<?php

/**
 * @file
 * Allows forms to dynamically add new fieldpanes to a fieldpanel in a form.
 */

module_load_include('inc', 'php_lib', 'DrupalFormHelpers');

/**
 * Theme hook for a fieldpane.
 */
function theme_fieldpane($vars) {
  return FieldPane::theme($vars['element']);
}
