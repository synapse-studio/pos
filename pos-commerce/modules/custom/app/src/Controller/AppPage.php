<?php

namespace Drupal\app\Controller;

/**
 * @file
 * Contains \Drupal\app\Controller\AppPage.
 */

use Drupal\app\Utility\HelperTheme;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\File\FileSystemInterface;

/**
 * Controller AppPage.
 */
class AppPage extends ControllerBase {

  /**
   * Render Page.
   */
  public function page(Request $request) {
    $this->file_system = \Drupal::service('file_system');
    $pub = 'public://';
    $dir = 'https://www.pos-commerce.ru/sites/default/files/';
    $path = $pub . 'commerce-import/_acrit.exportpro_tiu_standart_utf8_small.csv';
    $this->path = $path;
    $rows = $this->image();
    dsm($rows);


    $text = $this->t('App example text');
    return [
      'text' => ['#markup' => "<p>{$text}</p>"],
      'table' => HelperTheme::renderTable(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function catalog() {
    $rows = $this->parse($this->path);
    $weight = 1;
    $catalog = [];
    foreach ($rows as $row) {
      $weight++;
      $id = $row['CATEGORYID'] ?? NULL;
      $name = $row['CATEGORYNAME'] ?? "noname-$id";
      $parent_id = $category['CATEGORYPARENTID'] ?? NULL;
      $catalog[$id] = [
        'id' => (int) $id,
        'name' => $name,
        'parent' => $parent_id,
        'weight' => $weight,
      ];
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
    $products = [];
    $rows = $this->parse($this->path);
    foreach ($rows as $row) {
      $id = $row['Id'];
      $products[$id] = [
        'id' => $id,
        'type' => 'product',
        'title' => $row['NAME'],
        'body' => $row['TEXT'] ?? '',
        'catalog' => $row['CATEGORYID'] ?? [],
        'field_short' => $row['DESCRIPTION'] ?? '',
      ];
      $image_path = $this->getImagePath($row);
      if ($image_path) {
        $products[$id]['img'] = ['uri' => $image_path];
      }
    }
    return $products;
  }

  /**
   * {@inheritdoc}
   */
  private function getImagePath(array $product) :? string {
    if (empty($product['PICTURE'])) {
      return NULL;
    }
    $pathinfo = $this->getPathInfo($product['PICTURE']);
    $directory = sprintf('public://commerce-import/csv-import%s', $pathinfo['dirname']);
    $this->file_system->prepareDirectory(
      $directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );
    return implode('/', [$directory, $pathinfo['basename']]);
  }

   /**
   * {@inheritdoc}
   */
  private function getPathInfo(string $url) :? array {
    if (empty($url)) {
      return NULL;
    }
    $parse_url = parse_url($url);
    $pathinfo = pathinfo($parse_url['path']);
    return $pathinfo ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function image() {
    $products = $this->product();
    if (empty($products)) {
      return [];
    }
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $images = [];
    foreach ($products as $product) {
      if (empty($product['img'])) {
        continue;
      }
      $image_path = $this->getImagePath($product);
      if (empty($image_path)) {
        continue;
      }
      $pathinfo = $this->getPathInfo($product['PICTURE']);
      if (!file_exists($image_path)) {
        $image_content = file_get_contents($product['img']['uri']);
        $image_path = $this->file_system->saveData(
          $image_content, $image_path, FileSystemInterface::EXISTS_REPLACE
        );
      }
      $id = $product['id'];
      $images[$id] = [
        'id' => $product['id'],
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
    $paragraphs = [];
    return $paragraphs;
  }

  /**
   * Parse Products.
   */
  public function parse($path) {
    $rows = [];
    if (file_exists($path)) {
      $i = 0;
      $handle = fopen($path, 'r');
      $keys = [];
      while ($line = fgetcsv($handle, 4096, ';')) {
        // Get keys from 1-st line.
        ++$i;
        if ($i == 1) {
          $keys = $line;
          continue;
        }
        if (!$line) {
          continue;
        }
        $row = [];
        foreach ($keys as $key => $name) {
          $row[$name] = $this->clearCell($line, $key);
        }
        $rows[$i] = $row;
      }
      fclose($handle);
    }
    return $rows;
  }

  /**
   * Run Batch.
   */
  private function clearCell($line, $k) {
    return isset($line[$k]) ? trim($line[$k]) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function variation() {
    $rows = $this->parse($this->path);
    if (empty($rows)) {
      return [];
    }
    $variations = [];
    foreach ($rows as $row) {
      if (empty($row['id'])) {
        continue;
      }
      $id = $row['ID'];
      $variations[$id] = [
        'id' => $id,
        'type' => 'variation',
        'title' => $row['NAME'],
        'sku' => $row['CML2_ARTICLE'],
        'price' => $row['PRICE'],
        'list_price' => $row['sale_price'] ?? NULL,
        'stock' => $row['QUANTITY'] ?? NULL,
        'product_id' => $id,
        'product_key' => $id,
      ];
    }
    return $variations;
  }

  /**
   * Client.
   */
  public function client($url, array $query = []) {
    $options = $this->options;
    $options['query'] = $query;
    try {
      $response = $this->client->get($url, $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        return $response->getBody()->getContents();
      }
      return [
        'code' => $code,
        'header' => $response->getHeaders(),
        'body' => $response->getBody()->getContents(),
      ];
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }
    return FALSE;
  }


  /**
   * Title.
   */
  public function pageTitle() {
    return $this->t('App');
  }

}
