<?php

namespace Drupal\synpromotion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Configure synpromotion settings for this site.
 */
class TestForm extends FormBase {

  const OFFER = [
    'order_item_percentage_off',
    'order_buy_x_get_y',
    'shipment_percentage_off',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct($promotionId = NULL) {
    $this->promotionId = 12;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "{$this->promotionId}||synpromotion_test";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
    $products = [];
    $variations = [];
    $categories = [];
    $offer = $promotion->getOffer();
    $configuration = $offer->getConfiguration();
    foreach ($configuration['conditions'] as $condition) {
      switch ($condition['plugin']) {
        case 'order_item_product':
          $products = $condition['configuration']['products'];
          break;

        case 'order_item_purchased_entity:commerce_product_variation':
          $variations = $condition['configuration']['entities'];
          break;

        case 'order_item_product_category':
          $categories = $condition['configuration']['terms'];
          break;
      }
    }
    $form_state->setValue('Promotion', $this->promotionId);
    $title = '(Включено)';
    $resultId = "saveResult_{$promotion->id()}";
    $startDate = '';
    if (!empty($promotion->start_date->getString())) {
      $startDate = substr($promotion->start_date->getString(), 0, 10);
      $startDate .= str_replace('T', ' ', substr($promotion->start_date->getString(), 10));
      $startDate = new DrupalDateTime($startDate);
    }
    $endDate = '';
    if (!empty($promotion->end_date->getString())) {
      $endDate = substr($promotion->end_date->getString(), 0, 10);
      $endDate .= str_replace('T', ' ', substr($promotion->end_date->getString(), 10));
      $endDate = new DrupalDateTime($endDate);
    }
    $form['detail_promotion'] = [
      '#type' => 'details',
      '#title' => "{$promotion->getName()} {$title}. ID {$promotion->id()}",
      'settings_container' => [
        '#type' => 'container',
        'status' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Status'),
          '#default_value' => $promotion->status->getString(),
        ],
        'startDate' => [
          '#type' => 'datetime',
          '#title' => $this->t('Start date'),
          '#default_value' => $startDate,
        ],
        'endDate' => [
          '#type' => 'datetime',
          '#title' => $this->t('End date'),
          '#default_value' => $endDate,
        ],
        'actions' => [
          '#type' => 'actions',
          'ajax_submit' => [
            '#type' => 'button',
            '#value' => $this->t('Save'),
            '#attributes' => ['class' => ['inline', 'btn-success']],
            '#ajax'   => [
              'callback' => '::saveSettings',
              'progress' => ['type' => 'throbber', 'message' => NULL],
            ],
          ],
          '#suffix' => '<div id="' . $resultId . '"></div>',
        ],
      ],
      'enable_products' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Products'),
        '#default_value' => !empty($products) ? 1 : 0,
      ],
      'products_container' => [
        '#type' => 'container',
        '#states' => [
          'visible' => [
            ':input[name="enable_products"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
        'auto_complete_products' => [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'commerce_product',
          '#ajax' => [
            'callback' => '::addProducts',
            'event' => 'autocompleteclose',
            'effect'   => 'none',
            'progress' => ['type' => 'throbber', 'message' => 'Добавляем позицию...'],
          ],
        ],
        'products_wrapper' => [
          '#type' => 'container',
          'list_products' => [
            '#type' => 'select',
            '#title' => $this->t('Products'),
            '#size' => 7,
            '#options' => $this->queryProducts($products),
            '#multiple' => TRUE,
            '#ajax' => [
              'callback' => '::deleteProducts',
              'event' => 'dblclick',
              'effect'   => 'none',
              'progress' => ['type' => 'throbber', 'message' => 'Удаляем позицию...'],
            ],
          ],
        ],
      ],
      'enable_categories' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Categories'),
        '#default_value' => !empty($categories) ? 1 : 0,
      ],
      'categories_container' => [
        '#type' => 'container',
        '#states' => [
          'visible' => [
            ':input[name="enable_categories"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
        'auto_complete_categories' => [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'taxonomy_term',
          '#selection_settings' => [
            'target_bundles' => ['catalog'],
          ],
          '#ajax' => [
            'callback' => '::addCategories',
            'event' => 'autocompleteclose',
            'effect'   => 'none',
            'progress' => ['type' => 'throbber', 'message' => 'Добавляем позицию...'],
          ],
        ],
        'categories_wrapper' => [
          '#type' => 'container',
          'list_categories' => [
            '#type' => 'select',
            '#title' => $this->t('Categories'),
            '#size' => 7,
            '#options' => $this->queryCatalog($categories),
            '#multiple' => TRUE,
            '#ajax' => [
              'callback' => '::deleteCategories',
              'event' => 'dblclick',
              'effect'   => 'none',
              'progress' => ['type' => 'throbber', 'message' => 'Удаляем позицию...'],
            ],
          ],
        ],
      ],
    ];
    switch ($promotion->getOffer()->getPluginId()) {
      case 'order_item_percentage_off':
        $form['detail_promotion']['settings_container']['percentage'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Percentage'),
          '#default_value' => $promotion->getOffer()->getConfiguration()['percentage'] * 100,
        ];
        break;
    }
    return $form;
  }

  /**
   * Implements queryProducts.
   */
  private static function queryProducts($configuration) {
    $options = [];
    $products = [];
    foreach ($configuration as $product) {
      $products[] = $product['product'];
    }
    if (!empty($products)) {
      $storage = \Drupal::entityTypeManager()->getStorage('commerce_product');
      $query = $storage->getQuery()
        ->condition('uuid', $products, 'IN')
        ->condition('status', 1)
        ->sort('title', 'ASC');
      $ids = $query->execute();
      if (!empty($ids)) {
        foreach ($storage->loadMultiple($ids) as $entity) {
          $options[$entity->uuid()] = $entity->getTitle();
        }
      }
    }
    else {
      $options['none'] = ' - Товары не выбраны - ';
    }
    return $options;
  }

  /**
   * Implements queryCatalog.
   */
  private static function queryCatalog($categories) {
    $options = [];
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $query = $storage->getQuery()
      ->condition('vid', 'catalog')
      ->condition('uuid', $categories, 'IN')
      ->condition('status', 1)
      ->sort('name', 'ASC');
    $ids = $query->execute();
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $entity) {
        $options[$entity->uuid()] = $entity->getName();
      }
    }
    return $options;
  }

  /**
   * Implements addProducts.
   */
  public static function addProducts(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $promotionId = strstr($values['form_id'], '||', TRUE);
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($promotionId);
    if (!empty($values['auto_complete_products'])) {
      $product = \Drupal::entityTypeManager()->getStorage('commerce_product')->load($values['auto_complete_products']);
      $offer = $promotion->getOffer();
      $conditions = $offer->getConditions();
      $conditionsNew = [];
      $configuration = [];
      foreach ($conditions as $condition) {
        if ($condition->getPluginId() == 'order_item_product') {
          $configuration = $condition->getConfiguration();
          $configuration['products'][] = ['product' => $product->uuid()];
          $condition->setConfiguration($configuration);
        }
        $conditionsNew[] = $condition;
      }
      if (empty($configuration)) {
        $plugin_manager = \Drupal::service('plugin.manager.commerce_condition');
        $configuration['products'][] = ['product' => $product->uuid()];
        $condition = $plugin_manager->createInstance('order_item_product', $configuration);
        $conditionsNew[] = $condition;
      }
      $offer->setConditions($conditionsNew);
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_container']['products_wrapper']['list_products']['#options'] = self::queryProducts($configuration['products']);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper']",
                        ($form['detail_promotion']['products_container']['products_wrapper'])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteProducts.
   */
  public static function deleteProducts(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // \Drupal::logger(__FUNCTION__ . __LINE__)->notice(
    //   '@j', ['@j' => json_encode($values ?? [])]
    // );
    $promotionId = strstr($values['form_id'], '||', TRUE);
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($promotionId);
    if (!empty($values['list_products']) && !array_key_exists('none', $values['list_products'])) {
      $productUuid = array_shift($values['list_products']);
      $offer = $promotion->getOffer();
      $conditions = $offer->getConditions();
      $conditionsNew = [];
      foreach ($conditions as $condition) {
        $saveCondition = TRUE;
        if ($condition->getPluginId() == 'order_item_product') {
          $configuration = $condition->getConfiguration();
          foreach ($configuration['products'] as $key => $value) {
            if ($value['product'] == $productUuid) {
              unset($configuration['products'][$key]);
            }
          }
          if (empty($configuration['products'])) {
            $saveCondition = FALSE;
          }
          $condition->setConfiguration($configuration);
        }
        if ($saveCondition) {
          $conditionsNew[] = $condition;
        }
      }
      $offer->setConditions($conditionsNew);
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_container']['products_wrapper']['list_products']['#options'] = self::queryProducts($configuration['products']);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper']",
                        ($form['detail_promotion']['products_container']['products_wrapper'])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements addCategories.
   */
  public static function addCategories(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $promotionId = strstr($values['form_id'], '||', TRUE);
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($promotionId);
    if (!empty($values['auto_complete_categories'])) {
      $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($values['auto_complete_categories']);
      $offer = $promotion->getOffer();
      $conditions = $offer->getConditions();
      $conditionsNew = [];
      $configuration = [];
      foreach ($conditions as $condition) {
        if ($condition->getPluginId() == 'order_item_product_category') {
          $configuration = $condition->getConfiguration();
          $configuration['terms'][] = $category->uuid();
          $condition->setConfiguration($configuration);
        }
        $conditionsNew[] = $condition;
      }
      if (empty($configuration)) {
        $plugin_manager = \Drupal::service('plugin.manager.commerce_condition');
        $configuration['terms'][] = $category->uuid();
        $condition = $plugin_manager->createInstance('order_item_product_category', $configuration);
        $conditionsNew[] = $condition;
      }
      $offer->setConditions($conditionsNew);
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_container']['categories_wrapper']['list_categories']['#options'] = self::queryCatalog($configuration['terms']);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper']",
                        ($form['detail_promotion']['categories_container']['categories_wrapper'])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteCategories.
   */
  public static function deleteCategories(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // \Drupal::logger(__FUNCTION__ . __LINE__)->notice(
    //   '@j', ['@j' => json_encode($values ?? [])]
    // );
    $promotionId = strstr($values['form_id'], '||', TRUE);
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($promotionId);
    if (!empty($values['list_categories']) && !array_key_exists('none', $values['list_categories'])) {
      $categoryUuid = array_shift($values['list_categories']);
      $offer = $promotion->getOffer();
      $conditions = $offer->getConditions();
      $conditionsNew = [];
      foreach ($conditions as $condition) {
        $saveCondition = TRUE;
        if ($condition->getPluginId() == 'order_item_product_category') {
          $configuration = $condition->getConfiguration();
          $configuration['terms'] = array_diff($configuration['terms'], [$categoryUuid]);
          if (empty($configuration['terms'])) {
            $saveCondition = FALSE;
          }
          $condition->setConfiguration($configuration);
        }
        if ($saveCondition) {
          $conditionsNew[] = $condition;
        }
        $conditionsNew[] = $condition;
      }
      $offer->setConditions($conditionsNew);
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_container']['categories_wrapper']['list_categories']['#options'] = self::queryCatalog($configuration['terms']);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper']",
                        ($form['detail_promotion']['categories_container']['categories_wrapper'])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements saveSettings.
   */
  public static function saveSettings(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $promotionId = strstr($values['form_id'], '||', TRUE);
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($promotionId);
    $offer = $promotion->getOffer();
    $resultId = "#saveResult_$promotionId";
    $startDate = NULL;
    if (!empty($values['startDate']['date'])) {
      $startDate = new DrupalDateTime("{$values['startDate']['date']} {$values['startDate']['time']}");
    }
    $endDate = NULL;
    if (!empty($values['endDate']['date'])) {
      $endDate = new DrupalDateTime("{$values['endDate']['date']} {$values['endDate']['time']}");
    }
    $response = new AjaxResponse();
    switch ($promotion->getOffer()->getPluginId()) {
      case 'order_item_percentage_off':
        $configuration = $offer->getConfiguration();
        $percentage = $values['percentage'] / 100;
        $configuration['percentage'] = "$percentage";
        $offer->setConfiguration($configuration);
        $promotion->set('status', $values['status']);
        $promotion->setStartDate($startDate);
        $promotion->setEndDate($endDate);
        $promotion->setOffer($offer)->save();
        break;
    }
    $response->addCommand(new HtmlCommand($resultId, t('Configuration saved')));
    return $response;
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
