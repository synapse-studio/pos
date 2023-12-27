<?php

namespace Drupal\syncommerce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\commerce_price\Price;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Url;

/**
 * Returns responses for syncommerce routes.
 */
class SyncommerceController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $data = [];
    return [
      '#markup' => "<h1>Редактирование товаров</h1>",
      '#theme' => 'syncommerce_product',
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function list(Request $request) {
    $params = Json::decode($request->getContent());
    $products = $this->getProducts($params['filters']);
    $catalog = $this->getCatalog();
    $attributes = $this->getAttributes();
    return new JsonResponse([
      'products' => $products,
      'catalog' => $catalog,
      'attributes' => $attributes,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getProducts($params) {
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_product');
    $range = ($params['page'] - 1) * $params['max'];
    $query = $storage->getQuery()
      ->condition('type', 'product')
      ->condition('status', $params['status'])
      ->sort('title', 'ASC')
      ->range($range, $params['max']);
    if (!empty($params['article'])) {
      $query->condition('field_article', "%{$params['article']}%", 'like');
    }
    if (!empty($params['name'])) {
      $query->condition('title', "%{$params['name']}%", 'like');
    }
    if (!empty($params['catalog'])) {
      $termIds = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->getQuery()
        ->condition('vid', 'catalog')
        ->condition('status', 1)
        ->condition('name', "%{$params['catalog']}%", 'like')
        ->execute();
      $query->condition('field_catalog', $termIds, 'IN');
    }
    $ids = $query->execute();
    $products = [];
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $id => $product) {
        $variations = $this->getVariations($product->getVariations());
        $options = ['absolute' => TRUE];
        $url = Url::fromRoute('entity.commerce_product.canonical', ['commerce_product' => $id], $options);
        $products[$id] = [
          'id' => $id,
          'title' => $product->getTitle(),
          'article' => $product->field_article->value ?? '',
          'status' => $product->status->value ? TRUE : FALSE,
          'variations' => $variations,
          'viewVariation' => FALSE,
          'catalog' => $product->field_catalog->entity->id(),
          'info' => '',
          'url' => $url->toString(),
        ];
      }
    }
    return $products;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariations($variations) {
    $productVariations = [];
    foreach ($variations as $variation) {
      $attribute_item = [];
      $attributes = $this->getAttributes();
      foreach ($attributes as $key => $attribute) {
        $attribute_item[$key] = [
          'id' => 0,
          'label' => $attribute['label'],
          'name' => '',
        ];
      }
      if (!empty($variation->getAttributeValues())) {
        foreach ($variation->getAttributeValues() as $value) {
          $attribute_item[$value->getAttribute()->getOriginalId()] = [
            'id' => $value->id(),
            'label' => $value->getAttribute()->label(),
            'name' => $value->getName(),
          ];
        }
      }
      $productVariations[$variation->id()] = [
        'id' => $variation->id(),
        'status' => $variation->status->value ? TRUE : FALSE,
        'old_price' => $variation->field_oldprice->value,
        'price_number' => $variation->getPrice()->getNumber(),
        'price_code' => $variation->getPrice()->getCurrencyCode(),
        'attributes' => $attribute_item,
        'stock' => $variation->field_stock->value ?? 0,
        'info' => '',
      ];
    }
    return $productVariations;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributes() {
    $conf = \Drupal::config('core.entity_view_display.commerce_product_variation.variation.default');
    $variation_definitions = $conf->getRawData()['content'];
    $storageAttribute = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute');
    $attribute_original = $storageAttribute
      ->getQuery()
      ->execute();
    $attributes = [];
    $weight = 100;
    if (!empty($attribute_original)) {
      foreach ($storageAttribute->loadMultiple($attribute_original) as $key => $attribute) {
        if (array_key_exists("attribute_{$key}", $variation_definitions)) {
          $weight = $variation_definitions["attribute_{$key}"]['weight'];
        }
        else {
          $weight = $weight++;
        }
        $storageAttributeValues = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
        $attributeValuesIds = $storageAttributeValues
          ->getQuery()
          ->condition('attribute', $attribute->getOriginalId())
          ->execute();
        $attributeValues = [];
        if (!empty($attributeValuesIds)) {
          foreach ($storageAttributeValues->loadMultiple($attributeValuesIds) as $id => $value) {
            $attributeValues[$id] = [
              'id' => $id,
              'name' => $value->getName(),
            ];
          }
        }
        $attributes[$key] = [
          'id' => $attribute->id(),
          'name' => $attribute->getOriginalId(),
          'label' => $attribute->label(),
          'weight' => $weight,
          'values' => $attributeValues,
        ];
      }
    }
    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getCatalog() {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $query = $storage->getQuery()
      ->condition('vid', 'catalog')
      ->condition('status', 1)
      ->sort('name', 'ASC');
      // ->range(0, 5);
    $ids = $query->execute();
    $catalog = [];
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $id => $entity) {
        $catalog[$id] = $entity->getName();
      }
    }
    return $catalog;
  }

  /**
   * {@inheritdoc}
   */
  public function updateProduct(Request $request) {
    $params = Json::decode($request->getContent());
    if (empty($params['id'])) {
      return new JsonResponse('Ошибка. Не известен товар.');
    }
    $product = \Drupal::entityTypeManager()->getStorage('commerce_product')->load($params['id']);
    $product->setTitle($params['title']);
    $product->set('field_article', $params['article']);
    $product->set('field_catalog', [
      'target_id' => $params['catalog'],
    ]);
    $product->set('status', $params['status']);
    $product->save();
    return new JsonResponse('Товар сохранен.');

  }

  /**
   * {@inheritdoc}
   */
  public function updateVariation(Request $request) {
    $params = Json::decode($request->getContent());
    if (empty($params['id'])) {
      return new JsonResponse('Ошибка. Не известен вариант.');
    }
    $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($params['id']);
    $currency_code = $variation->getPrice()->getCurrencyCode();
    $variation->setPrice(new Price($params['price_number'], $currency_code));
    $variation->set('field_oldprice', $params['old_price']);
    $variation->set('field_stock', $params['stock']);
    foreach ($params['attributes'] as $key => $value) {
      $variation->set("attribute_$key", [
        'target_id' => $value,
      ]);
    }
    $variation->set('status', $params['status']);
    $variation->save();
    return new JsonResponse('Вариант сохранен.');

  }

}
