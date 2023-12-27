<?php

namespace Drupal\syncommerce\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * ProductsEditForm.
 */
class ProductsEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'syncommerce_products';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Filters'),
      '#open' => TRUE,
      'filters_container' => [
        '#type' => 'container',
        'auto_complete_products' => [
          '#type' => 'entity_autocomplete',
          '#title' => $this->t('Products'),
          '#default_value' => NULL,
          '#target_type' => 'commerce_product',
          '#ajax' => [
            'callback' => '::addProducts',
            'event' => 'autocompleteclose',
            'effect'   => 'none',
            'progress' => ['type' => 'throbber', 'message' => 'Обновляем фильтр...'],
          ],
        ],
        'status' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Status'),
          '#default_value' => 1,
        ],
      ],
    ];
    $catalog = $this->getCatalog();
    $headerProducts = [
      'view' => NULL,
      'vendor' => 'Артикул',
      'name' => 'Наименование',
      'catalog' => 'Каталог',
      'status' => 'Опубликован',
      'action' => '',
    ];
    $form["products_container"] = [
      '#type' => 'table',
      '#header' => $headerProducts,
      '#sticky' => TRUE,
      '#attributes' => ['class' => ['table--products']],
      // '#rows' => $optionsProducts,
      // '#empty' => $this->t('No shapes found'),
    ];
    foreach ($this->getProducts($form, $form_state) as $id => $product) {
      $resultId = "saveResult_$id";
      $form["products_container"][$id]['view'] = [
        '#type' => 'checkbox',
        '#default_value' => 0,
      ];
      $form["products_container"][$id]['vendor'] = [
        '#type' => 'textfield',
        '#default_value' => $product->field_vendor->value ?? '',
      ];
      $form["products_container"][$id]['name'] = [
        '#type' => 'textfield',
        '#default_value' => $product->getTitle(),
      ];
      $form["products_container"][$id]['catalog'] = [
        '#type' => 'select',
        '#options' => $catalog,
      ];
      $form["products_container"][$id]['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $product->status->getString(),
      ];
      $form["products_container"][$id]['action'] = [
        '#type' => 'actions',
        'ajax_submit' => [
          '#type' => 'button',
          '#value' => $this->t('Save'),
          '#attributes' => ['class' => ['inline', 'btn--small']],
          '#ajax'   => [
            'callback' => '::saveSettings',
            'progress' => ['type' => 'throbber', 'message' => NULL],
          ],
        ],
        '#suffix' => '<div id="' . $resultId . '"></div>',
      ];

      // $form["detail_product_$id"] = [
      //   '#type' => 'details',
      //   '#title' => $product->getTitle(),
      //   'settings_container' => [
      //     '#type' => 'container',
      //     'name' => [
      //       '#type' => 'textfield',
      //       '#title' => $this->t('Product name'),
      //       '#default_value' => $product->getTitle(),
      //     ],
      //     'vendor' => [
      //       '#type' => 'textfield',
      //       '#title' => $this->t('Vendor code'),
      //       '#default_value' => $product->field_vendor->value ?? '',
      //     ],
      //     // "catalog_$id" => [
      //     //   '#type' => 'select',
      //     //   '#title' => $this->t('Catalog'),
      //     //   // '#size' => 7,
      //     //   '#options' => $catalog,
      //     //   // '#multiple' => TRUE,
      //     //   // '#ajax' => [
      //     //   //   'callback' => $buy ? '::deleteProductsBuy' : '::deleteProductsGet',
      //     //   //   'event' => 'dblclick',
      //     //   //   'effect'   => 'none',
      //     //   //   'progress' => ['type' => 'throbber', 'message' => 'Удаляем позицию...'],
      //     //   // ],
      //     // ],
      //     // 'catalog' => [
      //     //   '#type' => 'textfield',
      //     //   '#title' => $this->t('Catalog'),
      //     //   '#default_value' => $product->field_catalog->entity->getName() ?? '',
      //     // ],
      //     'status' => [
      //       '#type' => 'checkbox',
      //       '#title' => $this->t('Status'),
      //       '#default_value' => $product->status->getString(),
      //     ],
      //     'actions' => [
      //       '#type' => 'actions',
      //       'ajax_submit' => [
      //         '#type' => 'button',
      //         '#value' => $this->t('Save'),
      //         '#attributes' => ['class' => ['inline', 'btn-success']],
      //         '#ajax'   => [
      //           'callback' => '::saveSettings',
      //           'progress' => ['type' => 'throbber', 'message' => NULL],
      //         ],
      //       ],
      //       '#suffix' => '<div id="' . $resultId . '"></div>',
      //     ],
      //   ],
      // ];
      $variations = $product->getVariations();
    }
    $form['pager'] = [
      '#type' => 'pager',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getProducts(array &$form, FormStateInterface $form_state) {
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_product');
    $query = $storage->getQuery()
      ->condition('type', 'product')
      // ->condition('status', 1)
      ->sort('title', 'ASC')
      ->range(0, 5);
    $ids = $query->execute();
    $products = [];
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $id => $product) {
        $products[$id] = $product;
      }
    }
    return $products;
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
