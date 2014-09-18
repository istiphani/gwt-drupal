<?php
// add template js and css for the inputs
drupal_add_js(drupal_get_path('theme', 'gwt_drupal') .'/js/spectrum/spectrum.js');
drupal_add_css(drupal_get_path('theme', 'gwt_drupal') .'/js/spectrum/spectrum.css');

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function gwt_drupal_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL)  {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  // Create the form using Forms API: http://api.drupal.org/api/7

  /* -- Delete this line if you want to use this setting
  $form['gwt_drupal_example'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('gwt_drupal sample setting'),
    '#default_value' => theme_get_setting('gwt_drupal_example'),
    '#description'   => t("This option doesn't do anything; it's just an example."),
  );
  // */

  // Remove some of the base theme's settings.
  /* -- Delete this line if you want to turn off this setting.
  unset($form['themedev']['zen_wireframes']); // We don't need to toggle wireframes on this site.
  // */

  $form['gwt_drupal'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Government Website Template(GWT) settings'),
  );

  $form['gwt_drupal']['gwt_drupal_masthead_bg_color'] = array(
    '#type' => 'textfield', 
    '#title' => t('Masthead Background Color:'), 
    '#default_value' => theme_get_setting('gwt_drupal_masthead_background_color'),
    '#size' => 10, 
    '#maxlength' => 30, 
    '#description' => t('Select the background color of the Masthead region. Select "X" to disable the color and use the default background color.'), 
    '#field_prefix' => '<div class="colorpicker-container">',
    '#field_suffix' => '</div>',
  );

  $form['gwt_drupal']['gwt_drupal_masthead_font_color'] = array(
    '#type' => 'textfield', 
    '#title' => t('Masthead Font Color:'), 
    '#default_value' => theme_get_setting('gwt_drupal_masthead_font_color'),
    '#size' => 10, 
    '#maxlength' => 30, 
    '#description' => t('Select the font color of the Masthead region. Select "X" to disable the color and use the default background color.'), 
    '#field_prefix' => '<div class="colorpicker-container">',
    '#field_suffix' => '</div>',
  );

  $form['gwt_drupal']['gwt_drupal_masthead_bg_image'] = array(
    '#type' => 'managed_file',
    '#title' => t('Masthead background image:'),
    '#default_value' => theme_get_setting('gwt_drupal_masthead_bg_image'),
    '#upload_location' => 'public://theme/',
    '#upload_validators' => array(
      'file_validate_extensions' => array('gif png jpg jpeg'),
    ),
    '#description' => t('Background of the masthead, allowed extensions: jpg, jpeg, png, gif<br/><strong>Note:</strong> The masthead background is different from the Logo.'),
  );

  $form['gwt_drupal']['gwt_drupal_banner_bg_color'] = array(
    '#type' => 'textfield', 
    '#title' => t('Banner Background Color:'), 
    '#default_value' => theme_get_setting('gwt_drupal_banner_background_color'),
    '#size' => 10, 
    '#maxlength' => 30, 
    '#description' => t('Select the background color of the Masthead region. Select "X" to disable the color and use the default background color.'), 
    '#field_prefix' => '<div class="colorpicker-container">',
    '#field_suffix' => '</div>',
  );

  $banner_font = theme_get_setting('gwt_drupal_banner_font_color');
  $form['gwt_drupal']['gwt_drupal_banner_font_color'] = array(
    '#type' => 'textfield', 
    '#title' => t('Banner Font Color:'), 
    '#default_value' => $banner_font ? $banner_font: '#666666',
    '#size' => 10, 
    '#maxlength' => 30, 
    '#description' => t('Select the font color of the Masthead region. Select "X" to disable the color and use the default background color.'), 
    '#field_prefix' => '<div class="colorpicker-container">',
    '#field_suffix' => '</div>',
  );

  $form['gwt_drupal']['gwt_drupal_banner_bg_image'] = array(
    '#type' => 'managed_file',
    '#title' => t('Banner background image'),
    '#default_value' => theme_get_setting('gwt_drupal_banner_bg_image'),
    //'#element_validate' => array('_gwt_drupal_banner_background_image_validate'),
    '#description' => t('Upload a file, allowed extensions: jpg, jpeg, png, gif<br/>Background of the banner.'),
    '#upload_location' => 'public://theme/',
    '#upload_validators' => array(
      'file_validate_extensions' => array('gif png jpg jpeg'),
    ),
  );

  $form['gwt_drupal']['form_script'] = array(
    '#markup' => '<script type="text/javascript">
    jQuery(document).ready(function($){
      $(\'.colorpicker-container input[type="text"]\').spectrum({
          showInput: true,
          allowEmpty:true,
          preferredFormat: "hex",
          clickoutFiresChange: true,
          showButtons: false
      });
    });
    </script>'
  );

/*
  $form['#validate'][] = 'gwt_drupal_settings_validate';
  $form['#submit'][] = 'gwt_drupal_settings_submit';
*/
  // We are editing the $form in place, so we don't need to return anything.
}

/**
 * helper function
 * implementation of hook_validate()
 *
function _gwt_drupal_masthead_background_image_validate($element, &$form_state) {
  drupal_set_message('<pre>'.print_r($element, 1).'</pre>');
  if (!is_numeric($element['#value'])) {
    // form_error($element, t('Please enter a number.'));
  }
}
*/
