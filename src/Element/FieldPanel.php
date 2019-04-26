<?php

namespace Drupal\islandora_form_fieldpanel\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allows for theming and processing fieldpanesls.
 *
 * @FormElement("fieldpanel")
 */
class FieldPanel extends FormElement {

  /**
   * Constants.
   */
  const ADDBUTTON = 'add-fieldpane';
  const MOVEFIELDSET = 'move-fieldpane';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = [
      '#input' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#process' => ['islandora_form_fieldpanel_fieldpanel_process'],
      '#theme_wrappers' => ['fieldpanel', 'form_element'],
    ];

    return $info;
  }

  /**
   * Loads the required resources for displaying the FieldPane element.
   *
   * @static var boolean $load
   *   Keeps us from loading the same files multiple times, while not required
   *   it just saves some time.
   */
  public static function addRequiredResources(FormStateInterface $form_state) {
    static $load = TRUE;
    if ($load) {
      // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_js(ISLANDORA_FORM_FIELDPANEL_PATH_JS . 'fieldpanel.js');

      // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_css(ISLANDORA_FORM_FIELDPANEL_PATH_CSS . 'fieldpanel.css');

      $load = FALSE;
    }
  }

  /**
   * FieldPanel's theme hook.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $complete_form
   *   The completed form.
   */
  public static function process(array $element, FormStateInterface $form_state, array $complete_form = NULL) {
    self::addRequiredResources($form_state);
    // Defaults to TRUE.
    $add = isset($element['#user_data']['add']) ? $element['#user_data']['add'] : TRUE;
    $children = Element::children($element);
    if ($add && !empty($children)) {
      $add_label = isset($element['#user_data']['add_label']) ? $element['#user_data']['add_label'] : t('Add');
      $element[self::ADDBUTTON] = self::createAddButton($element, $complete_form, $add_label);
    }
    if (count($children) > 1) {
      $element[self::MOVEFIELDSET] = self::createMoveFieldset($element, $complete_form, t('Move'));
    }
    $element['#prefix'] = "<div class='clear-block islandora-form-fieldpanel-container' id='{$element['#hash']}'>";
    $element['#suffix'] = '</div>';
    return $element;
  }

  /**
   * Creates a button that allows fieldpanes to duplicate.
   *
   * @param array $element
   *   The element.
   * @param array $complete_form
   *   The completed form.
   * @param string $label
   *   The label.
   *
   * @return FormElement
   *   The processed form element.
   */
  private static function createAddButton(array &$element, array &$complete_form, $label) {
    $children = Element::children($element);
    $child = $element[array_pop($children)];

    $add['#type'] = 'button';
    $add['#weight'] = 4;
    $add['#size'] = 30;
    $add['#id'] = $add['#name'] = $element['#hash'] . '-add';
    $add['#attributes'] = ['class' => ['fieldpanel-add']];
    $add['#value'] = $label;
    $add['#prefix'] = '<div class="ui-fieldpane-add-button">';
    $add['#suffix'] = '</div>';
    $add['#ajax'] = [
      'params' => [
        'target' => $element['#hash'],
        'render' => $element['#hash'],
        'action' => 'add',
        'child' => $child['#hash'],
      ],
      'callback' => 'xml_form_elements_ajax_callback',
      // The parents wrapper is set to the parents hash.
      'wrapper' => $element['#hash'],
      'method' => 'replaceWith',
      'effect' => 'fade',
    ];
    $add['#limit_validation_errors'] = [];
    return $add;
  }

  /**
   * Creates a fieldset to move FieldPane elements.
   *
   * @param array $element
   *   The element.
   * @param array $complete_form
   *   The completed form.
   * @param string $label
   *   The label.
   *
   * @return FormElement
   *   The processed form element.
   */
  private static function createMoveFieldset(array &$element, array &$complete_form, $label) {
    $children = Element::children($element);
    $options = [];
    $counter = 0;
    foreach ($children as $child) {
      if (is_numeric($child)) {
        $options[$child] = $counter;
        if ($counter == 0) {
          $options[$child] = 'Top';
        }
        $counter++;
      }
    }
    $child = $element[array_shift($children)];

    $move = [
      '#type' => 'fieldset',
      '#title' => $label,
      '#id' => $element['#hash'] . '-swap-fieldset',
      '#attributes' => ['class' => ['fieldpanel-swap-fieldset']],
      '#name' => $element['#hash'] . '-swap-fieldset',
      '#description' => t('Move element to position. All elements at that position (and after) will be moved one step down'),
    ];
    $move['move-element'] = [
      '#type' => 'select',
      '#title' => t('Element Number'),
      '#attributes' => ['class' => ['fieldpanel-swap-fieldset-move-element']],
      '#options' => $options,
      '#default_value' => '0',
    ];
    $move['move-position'] = [
      '#type' => 'select',
      '#attributes' => ['class' => ['fieldpanel-swap-fieldset-move-position']],
      '#title' => t('Position Number'),
      '#options' => $options,
      '#default_value' => '0',
    ];
    $move['move-op'] = [
      '#type' => 'button',
      '#id' => $element['#hash'] . '-move',
      '#name' => $element['#hash'] . '-move',
      '#value' => $label,
      '#limit_validation_errors' => [$element['#parents']],
      '#ajax' => [
        'params' => [
          'target' => $element['#hash'],
          'render' => $element['#hash'],
          'action' => 'move',
          'child' => $child['#hash'],
        ],
        'callback' => 'xml_form_elements_ajax_callback',
        'wrapper' => $element['#hash'],
        'method' => 'replaceWith',
        'effect' => 'fade',
      ],
    ];
    return $move;
  }

  /**
   * A function to filter children of a fieldpane.
   *
   * @param array $child
   *   The child element.
   */
  public static function filterChildren(array $child) {
    $ret = ($child['#type'] == 'fieldpane') ? TRUE : FALSE;
    return $ret;
  }

}
