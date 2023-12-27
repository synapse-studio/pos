<?php

namespace Drupal\eme54_import\Plugin\CommerceImport;

use Drupal\commerce_import\Plugin\CommerceImport\XmlImportPlugin;
use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\Core\File\FileSystemInterface;

/**
 * Provides a 'Xml' Template.
 *
 * @CommerceImportAnnotation(
 *   id = "xml_eme54",
 *   title = @Translation("Xml Eme54"),
 * )
 */
class XmlImportEme54Plugin extends XmlImportPlugin {

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->file_system = \Drupal::service('file_system');
    $this->realpath = $this->getRealpath();
  }

  /**
   * {@inheritdoc}
   */
  private function getRealpath() :? string {
    $file = $this->getFile();
    if (!$file) {
      return NULL;
    }
    $file = $this->getFile();
    $uri = $file->getFileUri();
    return \Drupal::service('file_system')->realpath($uri);
  }

  /**
   * {@inheritdoc}
   */
  private function parseXml(string $element_name) : array {
    if (!$this->realpath) {
      return [];
    }
    $xml = new \XMLReader();
    $xml->open($this->realpath);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $element_name){;}

    // looping through elements
    while($xml->name == $element_name) {
      // loading element data into simpleXML object
      $element = new \SimpleXMLElement($xml->readOuterXML());
      $elements[] = json_decode(
        json_encode($element, JSON_FORCE_OBJECT), TRUE
      );
      // moving pointer
      $xml->next($element_name);
      // clearing current element
      unset($element);
    }

    $xml->close();
    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public function catalog() {
    $records = $this->parseXml('Раздел');
    if (empty($records)) {
      return [];
    }
    $catalog = [];
    $weight = 0;
    foreach ($records as $category) {
      $weight++;
      $id = $category['Код'] ?? 0;
      $name = $category['Название'] ?? 'no name';
      $parent_id = $category['КодРодителя'] ?? NULL;
      $item = [
        'id' => $id,
        'name' => $name,
        'parent' => $parent_id,
        'weight' => $weight,
      ];
      $catalog[$id] = $item;
    }
    $catalog = $this->sortCatalogChildrens([], $catalog);
    return $catalog;
  }

  /**
   * {@inheritdoc}
   */
  private function sortCatalogChildrens(array $catalog, array $childrens) : array {
    $iterations = 0;
    while (count($childrens) > 0 && $iterations < 10) {
      $iterations++;
      foreach ($childrens as $children) {
        $id = $children['id'];
        $parent_id = $children['parent'] ?? NULL;
        if (empty($parent_id) || !empty($catalog[$parent_id])) {
          $catalog[$id] = $children;
        }
      }
    }
    return $catalog;
  }

  /**
   * {@inheritdoc}
   */
  public function product() {
    $records = $this->parseXml('ДетальнаяЗапись');
    if (empty($records)) {
      return [];
    }
    $products = [];
    foreach ($records as $product) {
      if (empty($product['ID'])) {
        continue;
      }
      $id = $product['ID'];
      $products[$id] = [
        'id' => $id,
        'type' => 'product',
        'title' => $product['Наименование'],
        'body' => $product['ПодробноеОписание'],
        'catalog' => $product['Кодраздела'],
        'field_short' => $product['ДополнительноеОписаниеНоменклатуры'],
      ];
      $image_path = $this->getImagePath($product);
      if ($image_path) {
        $products[$id]['img'] = ['uri' => $image_path];
      }
      $images_paths = $this->getImagesPaths($product);
      if ($images_paths) {
        foreach ($images_paths as $image_path) {
          $products[$id]['gallery'][] = ['uri' => $image_path];
        }
      }
      $characteristics = $this->getCharacteristics($product);
      if ($characteristics) {
        foreach ($characteristics as $field => $value) {
          $products[$id][$field] = $value;
        }
      }
    }
    return $products;
  }

  /**
   * {@inheritdoc}
   */
  public function variation() : array {
    $records = $this->parseXml('ДетальнаяЗапись');
    if (empty($records)) {
      return [];
    }
    $variations = [];
    foreach ($records as $variation) {
      if (empty($variation['ID'])) {
        continue;
      }
      $id = $variation['ID'];
      $variations[$id] = [
        'id' => $id,
        'type' => 'variation',
        'title' => $variation['Наименование'],
        'sku' => $variation['Артикул'],
        'price' => $variation['Цена'],
        'list_price' => $variation['sale_price'] ?? NULL,
        'stock' => $variation['Доступноеколичество'],
        'product_id' => $id,
        'product_key' => $id,
      ];
    }
    return $variations;
  }


  /**
   * {@inheritdoc}
   */
  private function getImagePath(array $product) :? string {
    if (empty($product['Основноеизображение'])) {
      return NULL;
    }
    $parse_url = parse_url($product['Основноеизображение']);
    $pathinfo = pathinfo($parse_url['path']);
    $directory = sprintf('public://commerce-import/eme54-import%s', $pathinfo['dirname']);
    $this->file_system->prepareDirectory(
      $directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );
    return implode('/', [$directory, $pathinfo['basename']]);
  }

  /**
   * {@inheritdoc}
   */
  private function getImagesPaths(array $product) :? array {
    if (empty($product['Дополнительныеизображения'])) {
      return NULL;
    }
    $paths = [];
    $count = count($product['Дополнительныеизображения']['Изображение']);
    foreach ($product['Дополнительныеизображения']['Изображение'] as $image) {
      if ($count == 1) {
        $parse_url = parse_url($image);
      }
      else {
        $parse_url = parse_url($image['URL']);
      }
      $pathinfo = pathinfo($parse_url['path']);
      $directory = sprintf('public://commerce-import/eme54-import%s', $pathinfo['dirname']);
      $this->file_system->prepareDirectory(
        $directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
      );
      $paths[] = implode('/', [$directory, $pathinfo['basename']]);
    }
    return $paths;
  }

  /**
   * {@inheritdoc}
   */
  private function getCharacteristics(array $product) :? array {
    if (empty($product['Характеристики'])) {
      return NULL;
    }
    $trans = new PhpTransliteration();
    $count = count($product['Характеристики']['Характеристика']);
    $characteristics = [];
    foreach ($product['Характеристики']['Характеристика'] as $item) {
      $field = $trans->transliterate($item['Название'], '');
      $value = $item['Значение'];
      $characteristics[$field] = $value;
    }
    return $characteristics;
  }

  /**
   * {@inheritdoc}
   */
  public function image() {
    $records = $this->parseXml('ДетальнаяЗапись');
    if (empty($records)) {
      return [];
    }
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $images = [];
    foreach ($records as $product) {
      if (empty($product['Основноеизображение'])) {
        continue;
      }
      $image_path = $this->getImagePath($product);
      if (empty($image_path)) {
        continue;
      }
      if (!file_exists($image_path)) {
        $image_content = file_get_contents($product['Основноеизображение']);
        $image_path = $this->file_system->saveData(
          $image_content, $image_path, FileSystemInterface::EXISTS_REPLACE
        );
      }
      $id = $product['ID'];
      $images[$id] = [
        'id' => $id,
        'uid' => 1,
        'status' => TRUE,
        'langcode' => $lang,
        'uri' => $image_path,
        'filename' => $pathinfo['basename'],
      ];
    }
    return $images;
  }

  /**
   * {@inheritdoc}
   */
  public function paragraphs() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function term() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  private function getFile() {
    $file = FALSE;
    $files = $this->query();
    if (!empty($files)) {
      $file = array_shift($files);
    }
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  private function query() {
    $files = [];
    $storage = \Drupal::entityTypeManager()->getStorage('file');
    $query = $storage->getQuery()
      ->condition('status', 0)
      ->condition('uri', '%commerce-import/%', 'LIKE')
      ->sort('created', 'DESC')
      ->range(0, 1);
    $ids = $query->execute();
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $id => $entity) {
        $files[$id] = $entity;
      }
    }
    return $files;
  }

}
