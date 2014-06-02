<?php
/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function igov_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL)  {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  // Create the form using Forms API: http://api.drupal.org/api/7

  /* -- Delete this line if you want to use this setting
  $form['igov_example'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('igov sample setting'),
    '#default_value' => theme_get_setting('igov_example'),
    '#description'   => t("This option doesn't do anything; it's just an example."),
  );
  // */

  // Remove some of the base theme's settings.
  /* -- Delete this line if you want to turn off this setting.
  unset($form['themedev']['zen_wireframes']); // We don't need to toggle wireframes on this site.
  // */

  // We are editing the $form in place, so we don't need to return anything.

  /**
   * igov GWT edit
   */

  $form['igov'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Government Website Template(GWT) settings'),
  );
  // TODO: add a settings for the transparency seal
  // default 0
  // 0 for left sidebar
  // 1 for right sidebar
  /*
  $form['igov']['igov_trans_seal'] = array(
    '#type' => 'select',
    '#title' => 'Transparency Seal',
    // TODO: add the law that states that transparency seal must always be shown to the site
    '#description' => 'Set the transparency seal sidebar location',
    '#default_value' => theme_get_setting('igov_trans_seal'),
    '#options'       => array(
      '0'   => t('Left'),
      '1' => t('Right'),
    ),
  );
  */
  /**
   * end igov GWT edit
   */
}

// how to save the igov_trans_seal settings
