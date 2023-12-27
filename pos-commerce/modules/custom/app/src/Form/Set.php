<?php

namespace Drupal\app\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\synhelper\Controller\AjaxResult;

/**
 * Class EntityButton.
 */
class Set extends FormBase {

  /**
   * Ajax wrapper.
   *
   * @var string
   */
  private $wrapper = 'app-form-wrapper';

  /**
   * AJAX ajaxPrev.
   */
  public function ajaxSubmit(array &$form, $form_state) {
    $otvet = "ajaxSubmit:\n";
    $node = $form_state->extra;
    $title = $node->title->value;
    if (is_numeric($node->id())) {
      $otvet .= "Title = $title\n";
    }
    else {
      $otvet .= "ERR\n";
    }
    return AjaxResult::ajax($this->wrapper, $otvet);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $wrap = $this->wrapper;
    $form_state->extra = $extra;
    $form_state->setCached(FALSE);
    $form["id"] = [
      '#type' => 'hidden',
      '#value' => $extra->id(),
    ];
    $form["signer"] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#attributes' => ['class' => ['inline']],
      '#ajax'   => [
        'callback' => '::ajaxSubmit',
        'effect'   => 'fade',
        'progress' => ['type' => 'throbber', 'message' => NULL],
      ],
      '#prefix' => "id:" . $extra->id(),
      '#suffix' => "<div id='$wrap'></div>",
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'app-form';
  }

}
