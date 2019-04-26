<?php

namespace Drupal\islandora_form_fieldpanel\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allows for theming & processing fieldpanes.
 *
 * @FormElement("fieldpane")
 */
class FieldPane extends FormElement {

  /**
   * Constants.
   */
  const DELETEBUTTON = 'delete-fieldpane';
  const MOVEUPBUTTON = 'move-up-fieldpane';
  const MOVEDOWNBUTTON = 'move-down-fieldpane';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = [
      '#input' => TRUE,
      '#process' => ['islandora_form_fieldpanel_fieldpane_process'],
      '#user_data' => ['add' => TRUE, 'delete' => TRUE],
      '#theme_wrappers' => ['fieldpane'],
    ];

    return $info;
  }

  /**
   * FieldPane's theme hook.
   *
   * @param array $element
   *   The element.
   *
   * @return string
   *   The processed string.
   */
  public static function theme(array $element) {
    $children = isset($element['#children']) ? $element['#children'] : '';
    $description = isset($element['#description']) ? "<div class='description'>{$element['#description']}</div>" : '';
    return "<div id='{$element['#hash']}' class='islandora-form-fieldpanel-pane'>{$description}{$children}</div>";
  }

  /**
   * The default #process function for fieldpane.
   *
   * Adds elements that allow for adding/remove form elements.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $complete_form
   *   The completed form.
   *
   * @return array
   *   The processed array.
   */
  public static function process(array $element, FormStateInterface $form_state, array $complete_form = NULL) {
    $panel = get_form_element_parent($element, $complete_form);
    $children = Element::children($panel);
    // Defaults to TRUE.
    $delete = isset($element['#user_data']['delete']) ? $element['#user_data']['delete'] : TRUE;
    if (count($children) > 2 && $delete) {
      $delete_label = isset($element['#user_data']['delete_label']) ? $element['#user_data']['delete_label'] : t('Delete');
      $element[self::DELETEBUTTON] = self::createRemoveButton($element, $complete_form, $delete_label);
    }
    $weight = $element['#weight'] * 1000;
    $number_children = count($children) - 3;
    if ($weight > 0) {
      $element[self::MOVEUPBUTTON] = self::createMoveUpButton($element, $complete_form, t('Move Up'));
    }
    if ($weight < $number_children) {
      $element[self::MOVEDOWNBUTTON] = self::createMoveDownButton($element, $complete_form, t('Move Down'));
    }
    return $element;
  }

  /**
   * Creates a remove button.
   *
   * Allows the user to remove this fieldpane.
   *
   * @param array $element
   *   The element.
   * @param array $complete_form
   *   The completed form.
   * @param string $label
   *   The label.
   *
   * @return FormElement
   *   The processed element.
   */
  private static function createRemoveButton(array &$element, array & $complete_form, $label) {
    $tabs = get_form_element_parent($element, $complete_form);
    $button['#type'] = 'button';
    $button['#weight'] = 10;
    $button['#size'] = 30;
    $button['#id'] = $button['#name'] = $element['#hash'] . '-remove';
    $button['#value'] = $label;
    $button['#prefix'] = '<div class="ui-fieldpane-delete-button">';
    $button['#suffix'] = '</div>';
    $button['#ajax'] = [
      'callback' => 'xml_form_elements_ajax_callback',
      'params' => [
        'target' => $tabs['#hash'],
        'render' => $tabs['#hash'],
        'action' => 'delete',
        'child' => $element['#hash'],
      ],
      // The parents wrapper is set to the parents hash.
      'wrapper' => $tabs['#hash'],
      'method' => 'replaceWith',
      'effect' => 'fade',
    ];
    $button['#limit_validation_errors'] = [];
    return $button;
  }

  /**
   * Creates a move up button.
   *
   * Allows the user to move this element up by one.
   *
   * @param array $element
   *   The element.
   * @param array $complete_form
   *   The completed form.
   * @param string $label
   *   The label.
   *
   * @return FormElement
   *   The processed element.
   */
  private static function createMoveUpButton(array &$element, array & $complete_form, $label) {
    $button['#type'] = 'button';
    $button['#weight'] = 4;
    $button['#size'] = 30;
    $button['#id'] = $button['#name'] = $element['#hash'] . '-move_up';
    $button['#attributes']['data-position'] = $element['#weight'] * 1000;
    $button['#value'] = $label;
    $button['#prefix'] = '<div class="ui-fieldpane-move-up-button">';
    $button['#suffix'] = '</div>';
    $button['#limit_validation_errors'] = [];
    return $button;
  }

  /**
   * Creates a move up button.
   *
   * Allows the user to move this element down by one.
   *
   * @param array $element
   *   The element.
   * @param array $complete_form
   *   The completed form.
   * @param string $label
   *   The label.
   *
   * @return FormElement
   *   The processed element.
   */
  private static function createMoveDownButton(array &$element, array & $complete_form, $label) {
    $button['#type'] = 'button';
    $button['#weight'] = 5;
    $button['#size'] = 30;
    $button['#id'] = $button['#name'] = $element['#hash'] . '-move_down';
    $button['#attributes']['data-position'] = $element['#weight'] * 1000;
    $button['#value'] = $label;
    $button['#prefix'] = '<div class="ui-fieldpane-move-down-button">';
    $button['#suffix'] = '</div>';
    $button['#limit_validation_errors'] = [];
    return $button;
  }

}
