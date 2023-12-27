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
class SettingsForm extends FormBase {

  const OFFER = [
    'order_item_percentage_off',
    'order_buy_x_get_y',
    'shipment_percentage_off',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct($promotionId = NULL) {
    $this->promotionId = $promotionId;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "{$this->promotionId}||synpromotion_settings";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
    // $title = '(Включено)';
    $title = $promotion->status->getString() ? '(Включено)' : '(Выключено)';
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
    ];
    $offer = $promotion->getOffer();
    $configuration = $offer->getConfiguration();
    switch ($offer->getPluginId()) {
      case 'order_item_percentage_off':
        $form['detail_promotion']['settings_container']['percentage'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Percentage'),
          '#default_value' => $configuration['percentage'] * 100,
        ];
        $data = $this->getConditions($configuration['conditions']);
        $form['detail_promotion']['products_buy'] = $this->formConditionProducts(TRUE, $data['products']);
        $form['detail_promotion']['categories_buy'] = $this->formConditionCategories(TRUE, $data['categories']);
        break;

      case 'order_buy_x_get_y':
        $form['detail_promotion']['settings_container']['buy_quantity'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Client buy quantity'),
          '#default_value' => $configuration['buy_quantity'],
        ];
        $data = $this->getConditions($configuration['buy_conditions']);
        $form['detail_promotion']['products_buy'] = $this->formConditionProducts(TRUE, $data['products']);
        $form['detail_promotion']['categories_buy'] = $this->formConditionCategories(TRUE, $data['categories']);
        $form['detail_promotion']['settings_container']['get_quantity'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Client get quantity'),
          '#default_value' => $configuration['get_quantity'],
        ];
        $form['detail_promotion']['settings_container']['get_quantity'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Client get quantity'),
          '#default_value' => $configuration['get_quantity'],
        ];
        $data = $this->getConditions($configuration['get_conditions']);
        $form['detail_promotion']['products_get'] = $this->formConditionProducts(FALSE, $data['products']);
        $form['detail_promotion']['categories_get'] = $this->formConditionCategories(FALSE, $data['categories']);
        $form['detail_promotion']['settings_container']['discount_container'] = [
          '#type' => 'container',
          // '#title' => $this->t('Discount'),
          "discount_choice{$this->promotionId}" => [
            '#type' => 'radios',
            '#required' => TRUE,
            '#title' => $this->t('At a discounted value'),
            '#default_value' => $configuration['offer_type'],
            '#options' => [
              'percentage' => $this->t('Percentage'),
              'fixed_amount' => $this->t('Fixed amount'),
            ],
          ],
          'percentage' => [
            '#type' => 'textfield',
            '#title' => $this->t('Percentage'),
            '#default_value' => $configuration['offer_percentage'] * 100,
            '#states' => [
              'visible' => [
                ":input[name='discount_choice{$this->promotionId}']" => [
                  'value' => 'percentage',
                ],
              ],
            ],
          ],
          'fixed_amount' => [
            '#type' => 'textfield',
            '#title' => $this->t('Fixed amount'),
            '#default_value' => $configuration['fixed_amount']['number'] ?? 0,
            '#states' => [
              'visible' => [
                ":input[name='discount_choice{$this->promotionId}']" => [
                  'value' => 'fixed_amount',
                ],
              ],
            ],
          ],
        ];
        break;

      case 'shipment_percentage_off':
        $form['detail_promotion']['settings_container']['percentage'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Percentage'),
          '#default_value' => $configuration['percentage'] * 100,
        ];
        $conditions = $promotion->getConditions();
        $amount = $conditions[0]->getConfiguration()['amount']['number'];
        $form['detail_promotion']['settings_container']['conditions_container'] = [
          '#type' => 'container',
          'amount' => [
            '#type' => 'textfield',
            '#title' => $this->t('Условие. Итого по текущему заказу больше'),
            '#default_value' => $amount ?? 0,
          ],
        ];
        break;
    }
    // switch ($offer->getPluginId()) {
    //   case 'order_item_percentage_off':
    //     break;
    // }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function formConditionProducts($buy, $products) {
    $param = $buy ? 'buy' : 'get';
    $form["enable_products_$param{$this->promotionId}"] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Products') . ' ' . \Drupal::translation()->translate($param),
      '#default_value' => !empty($products) ? 1 : 0,
    ];
    $form['products_container'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ":input[name='enable_products_$param{$this->promotionId}']" => [
            'checked' => TRUE,
          ],
        ],
      ],
      "auto_complete_products_$param" => [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'commerce_product',
        '#ajax' => [
          'callback' => $buy ? '::addProductsBuy' : '::addProductsGet',
          'event' => 'autocompleteclose',
          'effect'   => 'none',
          'progress' => ['type' => 'throbber', 'message' => 'Добавляем позицию...'],
        ],
      ],
      "products_wrapper_$param{$this->promotionId}" => [
        '#type' => 'container',
        "list_products_$param" => [
          '#type' => 'select',
          // '#title' => $this->t('Products'),
          '#size' => 7,
          '#options' => $this->queryProducts($products),
          '#multiple' => TRUE,
          '#ajax' => [
            'callback' => $buy ? '::deleteProductsBuy' : '::deleteProductsGet',
            'event' => 'dblclick',
            'effect'   => 'none',
            'progress' => ['type' => 'throbber', 'message' => 'Удаляем позицию...'],
          ],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function formConditionCategories($buy, $categories) {
    $param = $buy ? 'buy' : 'get';
    $form["enable_categories_$param{$this->promotionId}"] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Categories') . ' ' . \Drupal::translation()->translate($param),
      '#default_value' => !empty($categories) ? 1 : 0,
    ];
    $form['categories_container'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ":input[name='enable_categories_$param{$this->promotionId}']" => [
            'checked' => TRUE,
          ],
        ],
      ],
      "auto_complete_categories_$param" => [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'taxonomy_term',
        '#selection_settings' => [
          'target_bundles' => ['catalog'],
        ],
        '#ajax' => [
          'callback' => $buy ? '::addCategoriesBuy' : '::addCategoriesGet',
          'event' => 'autocompleteclose',
          'effect'   => 'none',
          'progress' => ['type' => 'throbber', 'message' => 'Добавляем позицию...'],
        ],
      ],
      "categories_wrapper_$param{$this->promotionId}" => [
        '#type' => 'container',
        "list_categories_$param" => [
          '#type' => 'select',
          // '#title' => $this->t('Categories'),
          '#size' => 7,
          '#options' => $this->queryCatalog($categories),
          '#multiple' => TRUE,
          '#ajax' => [
            'callback' => $buy ? '::deleteCategoriesBuy' : '::deleteCategoriesGet',
            'event' => 'dblclick',
            'effect'   => 'none',
            'progress' => ['type' => 'throbber', 'message' => 'Удаляем позицию...'],
          ],
        ],
      ],
    ];
    return $form;
  }

  /**
   * Implements queryProducts.
   */
  private function queryProducts($configuration) {
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
  private function queryCatalog($categories) {
    $options = [];
    if (!empty($categories)) {
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
    }
    else {
      $options['none'] = ' - Категории не выбраны - ';
    }
    return $options;
  }

  /**
   * Implements addProductsBuy.
   */
  public function addProductsBuy(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['auto_complete_products_buy'])) {
      $product = \Drupal::entityTypeManager()->getStorage('commerce_product')->load($values["auto_complete_products_buy"]);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $products = $configuration['products'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          if (empty($configuration['buy_conditions'])) {
            $configuration['buy_conditions'][] = [
              'plugin' => 'order_item_product',
              'configuration' => [
                'products' => [
                  0 => [
                    'product' => $product->uuid(),
                  ],
                ],
              ],
            ];
            $products = [['product' => $product->uuid()]];
          }
          else {
            foreach ($configuration['buy_conditions'] as $key => $value) {
              if ($value['plugin'] == 'order_item_product') {
                $configuration['buy_conditions'][$key]['configuration']['products'][] = ['product' => $product->uuid()];
                $products = $configuration['buy_conditions'][$key]['configuration']['products'];
              }
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_buy']['products_container']["products_wrapper_buy{$this->promotionId}"]['list_products_buy']['#options'] = $this->queryProducts($products);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper-buy{$this->promotionId}']",
                        ($form['detail_promotion']['products_buy']['products_container']["products_wrapper_buy{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements addProducts.
   */
  public function addProductsGet(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['auto_complete_products_get'])) {
      $product = \Drupal::entityTypeManager()->getStorage('commerce_product')->load($values["auto_complete_products_get"]);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $products = $configuration['products'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          if (empty($configuration['get_conditions'])) {
            $configuration['get_conditions'][] = [
              'plugin' => 'order_item_product',
              'configuration' => [
                'products' => [
                  0 => [
                    'product' => $product->uuid(),
                  ],
                ],
              ],
            ];
            $products = [['product' => $product->uuid()]];
          }
          else {
            foreach ($configuration['get_conditions'] as $key => $value) {
              if ($value['plugin'] == 'order_item_product') {
                $configuration['get_conditions'][$key]['configuration']['products'][] = ['product' => $product->uuid()];
                $products = $configuration['get_conditions'][$key]['configuration']['products'];
              }
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_get']['products_container']["products_wrapper_get{$this->promotionId}"]['list_products_get']['#options'] = $this->queryProducts($products);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper-get{$this->promotionId}']",
                        ($form['detail_promotion']['products_get']['products_container']["products_wrapper_get{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteProductsBuy.
   */
  public function deleteProductsBuy(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['list_products_buy']) && !array_key_exists('none', $values['list_products_buy'])) {
      $productUuid = array_shift($values['list_products_buy']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $products = $configuration['products'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          foreach ($configuration['buy_conditions'] as $key_conf => $conf) {
            if ($conf['plugin'] == 'order_item_product') {
              foreach ($conf['configuration']['products'] as $key => $value) {
                if ($value['product'] == $productUuid) {
                  unset($configuration['buy_conditions'][$key_conf]['configuration']['products'][$key]);
                }
              }
              $products = $configuration['buy_conditions'][$key_conf]['configuration']['products'];
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_buy']['products_container']["products_wrapper_buy{$this->promotionId}"]['list_products_buy']['#options'] = $this->queryProducts($products);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper-buy{$this->promotionId}']",
                        ($form['detail_promotion']['products_buy']['products_container']["products_wrapper_buy{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteProductsGet.
   */
  public function deleteProductsGet(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['list_products_get']) && !array_key_exists('none', $values['list_products_get'])) {
      $productUuid = array_shift($values['list_products_get']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $products = $configuration['products'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          foreach ($configuration['get_conditions'] as $key_conf => $conf) {
            if ($conf['plugin'] == 'order_item_product') {
              foreach ($conf['configuration']['products'] as $key => $value) {
                if ($value['product'] == $productUuid) {
                  unset($configuration['get_conditions'][$key_conf]['configuration']['products'][$key]);
                }
              }
              $products = $configuration['get_conditions'][$key_conf]['configuration']['products'];
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['products_get']['products_container']["products_wrapper_get{$this->promotionId}"]['list_products_get']['#options'] = $this->queryProducts($products);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-products-wrapper-get{$this->promotionId}']",
                        ($form['detail_promotion']['products_get']['products_container']["products_wrapper_get{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements addCategories.
   */
  public function addCategoriesBuy(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['auto_complete_categories_buy'])) {
      $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($values['auto_complete_categories_buy']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $categories = $configuration['terms'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          if (empty($configuration['buy_conditions'])) {
            $configuration['buy_conditions'][] = [
              'plugin' => 'order_item_product_category',
              'configuration' => [
                'terms' => [
                  $category->uuid(),
                ],
              ],
            ];
            $categories = [$category->uuid()];
          }
          else {
            $saveConfiguration = FALSE;
            foreach ($configuration['buy_conditions'] as $key => $value) {
              if ($value['plugin'] == 'order_item_product_category') {
                $configuration['buy_conditions'][$key]['configuration']['terms'][] = $category->uuid();
                $categories = $configuration['buy_conditions'][$key]['configuration']['terms'];
                $saveConfiguration = TRUE;
              }
            }
            if (!$saveConfiguration) {
              $configuration['buy_conditions'][] = [
                'plugin' => 'order_item_product_category',
                'configuration' => [
                  'terms' => [
                    $category->uuid(),
                  ],
                ],
              ];
              $categories = [$category->uuid()];
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_buy']['categories_container']["categories_wrapper_buy{$this->promotionId}"]['list_categories_buy']['#options'] = $this->queryCatalog($categories);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper-buy{$this->promotionId}']",
                        ($form['detail_promotion']['categories_buy']['categories_container']["categories_wrapper_buy{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements addCategories.
   */
  public function addCategoriesGet(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['auto_complete_categories_get'])) {
      $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($values['auto_complete_categories_get']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $categories = $configuration['terms'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          if (empty($configuration['get_conditions'])) {
            $configuration['get_conditions'][] = [
              'plugin' => 'order_item_product_category',
              'configuration' => [
                'terms' => [
                  $category->uuid(),
                ],
              ],
            ];
            $categories = [$category->uuid()];
          }
          else {
            $saveConfiguration = FALSE;
            foreach ($configuration['get_conditions'] as $key => $value) {
              if ($value['plugin'] == 'order_item_product_category') {
                $configuration['get_conditions'][$key]['configuration']['terms'][] = $category->uuid();
                $categories = $configuration['get_conditions'][$key]['configuration']['terms'];
                $saveConfiguration = TRUE;
              }
            }
            if (!$saveConfiguration) {
              $configuration['get_conditions'][] = [
                'plugin' => 'order_item_product_category',
                'configuration' => [
                  'terms' => [
                    $category->uuid(),
                  ],
                ],
              ];
              $categories = [$category->uuid()];
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_get']['categories_container']["categories_wrapper_get{$this->promotionId}"]['list_categories_get']['#options'] = $this->queryCatalog($categories);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper-get{$this->promotionId}']",
                        ($form['detail_promotion']['categories_get']['categories_container']["categories_wrapper_get{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteCategoriesBuy.
   */
  public function deleteCategoriesBuy(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['list_categories_buy']) && !array_key_exists('none', $values['list_categories_buy'])) {
      $categoryUuid = array_shift($values['list_categories_buy']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $categories = $configuration['terms'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          foreach ($configuration['buy_conditions'] as $key_conf => $conf) {
            if ($conf['plugin'] == 'order_item_product_category') {
              foreach ($conf['configuration']['terms'] as $key => $value) {
                if ($value == $categoryUuid) {
                  unset($configuration['buy_conditions'][$key_conf]['configuration']['terms'][$key]);
                }
              }
              $categories = $configuration['buy_conditions'][$key_conf]['configuration']['terms'];
              if (empty($categories)) {
                unset($configuration['buy_conditions'][$key_conf]);
              }
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_buy']['categories_container']["categories_wrapper_buy{$this->promotionId}"]['list_categories_buy']['#options'] = $this->queryCatalog($categories);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper-buy{$this->promotionId}']",
                        ($form['detail_promotion']['categories_buy']['categories_container']["categories_wrapper_buy{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements deleteCategoriesGet.
   */
  public function deleteCategoriesGet(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['list_categories_get']) && !array_key_exists('none', $values['list_categories_get'])) {
      $categoryUuid = array_shift($values['list_categories_get']);
      $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
      $offer = $promotion->getOffer();
      switch ($offer->getPluginId()) {
        case 'order_item_percentage_off':
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
          $categories = $configuration['terms'];
          break;

        case 'order_buy_x_get_y':
          $configuration = $offer->getConfiguration();
          foreach ($configuration['get_conditions'] as $key_conf => $conf) {
            if ($conf['plugin'] == 'order_item_product_category') {
              foreach ($conf['configuration']['terms'] as $key => $value) {
                if ($value == $categoryUuid) {
                  unset($configuration['get_conditions'][$key_conf]['configuration']['terms'][$key]);
                }
              }
              $categories = $configuration['get_conditions'][$key_conf]['configuration']['terms'];
              if (empty($categories)) {
                unset($configuration['get_conditions'][$key_conf]);
              }
            }
          }
          $offer->setConfiguration($configuration);
          break;
      }
      $promotion->setOffer($offer)->save();
      $form['detail_promotion']['categories_get']['categories_container']["categories_wrapper_get{$this->promotionId}"]['list_categories_get']['#options'] = $this->queryCatalog($categories);
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new ReplaceCommand("[data-drupal-selector='edit-categories-wrapper-get{$this->promotionId}']",
                        ($form['detail_promotion']['categories_get']['categories_container']["categories_wrapper_get{$this->promotionId}"])));
    $form_state->setRebuild();
    return $response;
  }

  /**
   * Implements saveSettings.
   */
  public function saveSettings(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load($this->promotionId);
    $offer = $promotion->getOffer();
    $configuration = $offer->getConfiguration();
    $default_currency = \Drupal::entityTypeManager()
      ->getStorage('commerce_store')
      ->loadDefault()
      ->getDefaultCurrency()
      ->getCurrencyCode();
    $resultId = "#saveResult_{$this->promotionId}";
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
        $configuration['percentage'] = $values['percentage'] / 100;
        break;

      case 'order_buy_x_get_y':
        $configuration['buy_quantity'] = $values['buy_quantity'];
        $configuration['get_quantity'] = $values['get_quantity'];
        $configuration['offer_type'] = $values["discount_choice{$this->promotionId}"];
        $configuration['offer_percentage'] = $values['percentage'] / 100;
        $configuration['offer_amount'] = [
          'number' => $values['fixed_amount'],
          'currency_code' => $default_currency,
        ];
        break;

      case 'shipment_percentage_off':
        $configuration['percentage'] = $values['percentage'] / 100;
        $conditions = $promotion->getConditions();
        $conditionConfiguration = $conditions[0]->getConfiguration();
        $conditionConfiguration['amount']['number'] = $values['amount'];
        $conditions[0]->setConfiguration($conditionConfiguration);
        $promotion->setConditions($conditions);
        break;
    }
    $offer->setConfiguration($configuration);
    $promotion->set('status', $values['status']);
    $promotion->setStartDate($startDate);
    $promotion->setEndDate($endDate);
    $promotion->setOffer($offer)->save();
    $response->addCommand(new HtmlCommand($resultId, t('Configuration saved.')));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  private function getConditions($conditions) {
    $products = [];
    $variations = [];
    $categories = [];
    foreach ($conditions as $condition) {
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
    return [
      'products' => $products,
      'variations' => $variations,
      'categories' => $categories,
    ];
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
