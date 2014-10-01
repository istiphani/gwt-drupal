<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('gwt_drupal_rebuild_registry') && !defined('MAINTENANCE_MODE')) {
  // Rebuild .info data.
  system_rebuild_theme_data();
  // Rebuild theme registry.
  drupal_theme_rebuild();
}

/**
 * gwt_drupal edit
 */
/**
 * theme override of link main menu
 */
function gwt_drupal_links__system_main_menu($variables) {
  //$links = $variables['links'];

  //*
  $links = menu_tree_all_data('main-menu', null, 3);
  // $test = '<pre>'.print_r($links, 1).'</pre>';
  // return $test;
  // */


  // heading not needed in main menu
  $heading = $variables['heading'];
  global $language_url;
  $output = '';

  $output .= _gwt_drupal_link_render($links, 0, $variables);

  return $output;
}

/**
 * helper function
 * render the link as li element
 */
function _gwt_drupal_link_render($links, $levels_deep = 0, $variables = array()){
  if($levels_deep > 3){
    return;
  }

  $attributes = $variables['attributes'];

  if (count($links) > 0) {
    $output = '';

    // reset classes
    $attributes['class'] = array();
    $attributes['class'][] = 'level-'.$levels_deep;

    if($levels_deep == 0){
      $attributes['class'][] = 'left';
    }
    if($levels_deep > 0){
      $attributes['class'][] = 'dropdown';
    }
    $output .= '<ul' . drupal_attributes($attributes) . '>';

    $num_links = count($links);
    $i = 1;
    foreach ($links as $key => $link_full) {
      // check if the key is really a link by checking if the key is an int
      $keys = explode(' ', $key);
      $menu_id = $keys[count($keys)-1];
      if(!is_numeric($menu_id)){
        break;
      }

      $link = $link_full['link'];
      $class = array('menu-'.$menu_id);
      $has_sub_menu = false;
      if(isset($link_full['below']) && !empty($link_full['below'])){
        $has_sub_menu = true;
        $class[] = 'has-dropdown';
        $class[] = 'not-click';
      }

      // Add first, last and active classes to the list of links to help out themers.
      if ($i == 1) {
        $class[] = 'first';
      }
      if ($i == $num_links) {
        $class[] = 'last';
      }
      if (isset($link['href']) && ($link['href'] == $_GET['q'] || ($link['href'] == '<front>' && drupal_is_front_page())) && (empty($link['language']) || $link['language']->language == $language_url->language)) {
        $class[] = 'active';
      }
      // add custom divider for the links
      $output .= '<li class="divider"></li>';
      $output .= '<li' . drupal_attributes(array('class' => $class)) . '>';

      if (isset($link['href'])) {
        // Pass in $link as $options, they share the same keys.
        if($link['href'] == '<front>'){
          $output .= l($link['title'], '');
        }
        else{
          $output .= l($link['title'], $link['href'], $link);
        }
      }
      elseif (!empty($link['title'])) {
        // Some links are actually not links, but we wrap these in <span> for adding title and class attributes.
        if (empty($link['html'])) {
          $link['title'] = check_plain($link['title']);
        }
        $span_attributes = '';
        if (isset($link['attributes'])) {
          $span_attributes = drupal_attributes($link['attributes']);
        }
        $output .= '<span' . $span_attributes . '>' . $link['title'] . '</span>';
      }

      // recursion
      // check if there are submenu
      // support up to three levels
      if($has_sub_menu){
        $output .= _gwt_drupal_link_render($link_full['below'], $levels_deep+1, $variables);
      }
      $i++;
      $output .= "</li>\n";
    }

    // add custom divider for the links
    $output .= '<li class="divider"></li>';

    $output .= '</ul>';
  }

  return $output;
}

/**
 * end gwt_drupal edit
 */





/**
 * Return a themed breadcrumb trail.
 *
 * @param $variables
 *   - title: An optional string to be used as a navigational heading to give
 *     context for breadcrumb links to screen-reader users.
 *   - title_attributes_array: Array of HTML attributes for the title. It is
 *     flattened into a string within the theme function.
 *   - breadcrumb: An array containing the breadcrumb links.
 * @return
 *   A string containing the breadcrumb output.
 */
function gwt_drupal_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';

  // Determine if we are to display the breadcrumb.
  $show_breadcrumb = theme_get_setting('gwt_drupal_breadcrumb');
  if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

    // Optionally get rid of the homepage link.
    $show_breadcrumb_home = theme_get_setting('gwt_drupal_breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }

    // Return the breadcrumb with separators.
    if (!empty($breadcrumb)) {
      $breadcrumb_separator = filter_xss_admin(theme_get_setting('gwt_drupal_breadcrumb_separator'));
      $trailing_separator = $title = '';
      if (theme_get_setting('gwt_drupal_breadcrumb_title')) {
        $item = menu_get_item();
        if (!empty($item['tab_parent'])) {
          // If we are on a non-default tab, use the tab's title.
          $breadcrumb[] = check_plain($item['title']);
        }
        else {
          $breadcrumb[] = drupal_get_title();
        }
      }
      elseif (theme_get_setting('gwt_drupal_breadcrumb_trailing')) {
        $trailing_separator = $breadcrumb_separator;
      }

      // Provide a navigational heading to give context for breadcrumb links to
      // screen-reader users.
      if (empty($variables['title'])) {
        $variables['title'] = t('You are here');
      }
      // Unless overridden by a preprocess function, make the heading invisible.
      if (!isset($variables['title_attributes_array']['class'])) {
        $variables['title_attributes_array']['class'][] = 'element-invisible';
      }

      // Build the breadcrumb trail.
      $output = '<nav class="breadcrumb" role="navigation">';
      $output .= '<h2' . drupal_attributes($variables['title_attributes_array']) . '>' . $variables['title'] . '</h2>';
      $output .= '<ol><li>' . implode($breadcrumb_separator . '</li><li>', $breadcrumb) . $trailing_separator . '</li></ol>';
      $output .= '</nav>';
    }
  }

  return $output;
}

/**
 * Override or insert variables into the html template.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered. This is usually "html", but can
 *   also be "maintenance_page" since gwt_drupal_preprocess_maintenance_page() calls
 *   this function to have consistent variables.
 */
function gwt_drupal_preprocess_html(&$variables, $hook) {
  // Add variables and paths needed for HTML5 and responsive support.
  $variables['base_path'] = base_path();
  $variables['path_to_gwt_drupal'] = drupal_get_path('theme', 'gwt_drupal');
  // Get settings for HTML5 and responsive support. array_filter() removes
  // items from the array that have been disabled.
  $html5_respond_meta = array_filter((array) theme_get_setting('gwt_drupal_html5_respond_meta'));
  $variables['add_respond_js']          = in_array('respond', $html5_respond_meta);
  $variables['add_html5_shim']          = in_array('html5', $html5_respond_meta);
  $variables['default_mobile_metatags'] = in_array('meta', $html5_respond_meta);

  // Attributes for html element.
  $variables['html_attributes_array'] = array(
    'lang' => $variables['language']->language,
    'dir' => $variables['language']->dir,
  );

  // Send X-UA-Compatible HTTP header to force IE to use the most recent
  // rendering engine or use Chrome's frame rendering engine if available.
  // This also prevents the IE compatibility mode button to appear when using
  // conditional classes on the html tag.
  if (is_null(drupal_get_http_header('X-UA-Compatible'))) {
    drupal_add_http_header('X-UA-Compatible', 'IE=edge,chrome=1');
  }

  $variables['skip_link_anchor'] = theme_get_setting('gwt_drupal_skip_link_anchor');
  $variables['skip_link_text'] = theme_get_setting('gwt_drupal_skip_link_text');

  // Return early, so the maintenance page does not call any of the code below.
  if ($hook != 'html') {
    return;
  }

  // Serialize RDF Namespaces into an RDFa 1.1 prefix attribute.
  if ($variables['rdf_namespaces']) {
    $prefixes = array();
    foreach (explode("\n  ", ltrim($variables['rdf_namespaces'])) as $namespace) {
      // Remove xlmns: and ending quote and fix prefix formatting.
      $prefixes[] = str_replace('="', ': ', substr($namespace, 6, -1));
    }
    $variables['rdf_namespaces'] = ' prefix="' . implode(' ', $prefixes) . '"';
  }

  // Classes for body element. Allows advanced theming based on context
  // (home page, node of certain type, etc.)
  if (!$variables['is_front']) {
    // Add unique class for each page.
    $path = drupal_get_path_alias($_GET['q']);
    // Add unique class for each website section.
    list($section, ) = explode('/', $path, 2);
    $arg = explode('/', $_GET['q']);
    if ($arg[0] == 'node' && isset($arg[1])) {
      if ($arg[1] == 'add') {
        $section = 'node-add';
      }
      elseif (isset($arg[2]) && is_numeric($arg[1]) && ($arg[2] == 'edit' || $arg[2] == 'delete')) {
        $section = 'node-' . $arg[2];
      }
    }
    $variables['classes_array'][] = drupal_html_class('section-' . $section);
  }
  if (theme_get_setting('gwt_drupal_wireframes')) {
    $variables['classes_array'][] = 'with-wireframes'; // Optionally add the wireframes style.
  }
  // Store the menu item since it has some useful information.
  $variables['menu_item'] = menu_get_item();
  if ($variables['menu_item']) {
    switch ($variables['menu_item']['page_callback']) {
      case 'views_page':
        // Is this a Views page?
        $variables['classes_array'][] = 'page-views';
        break;
      case 'page_manager_blog':
      case 'page_manager_blog_user':
      case 'page_manager_contact_site':
      case 'page_manager_contact_user':
      case 'page_manager_node_add':
      case 'page_manager_node_edit':
      case 'page_manager_node_view_page':
      case 'page_manager_page_execute':
      case 'page_manager_poll':
      case 'page_manager_search_page':
      case 'page_manager_term_view_page':
      case 'page_manager_user_edit_page':
      case 'page_manager_user_view_page':
        // Is this a Panels page?
        $variables['classes_array'][] = 'page-panels';
        break;
    }
  }
}

/**
 * Override or insert variables into the html templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("html" in this case.)
 */
function gwt_drupal_process_html(&$variables, $hook) {
  // Flatten out html_attributes.
  $variables['html_attributes'] = drupal_attributes($variables['html_attributes_array']);
}

/**
 * Override or insert variables in the html_tag theme function.
 */
function gwt_drupal_process_html_tag(&$variables) {
  $tag = &$variables['element'];

  if ($tag['#tag'] == 'style' || $tag['#tag'] == 'script') {
    // Remove redundant type attribute and CDATA comments.
    unset($tag['#attributes']['type'], $tag['#value_prefix'], $tag['#value_suffix']);

    // Remove media="all" but leave others unaffected.
    if (isset($tag['#attributes']['media']) && $tag['#attributes']['media'] === 'all') {
      unset($tag['#attributes']['media']);
    }
  }
}

/**
 * Implement hook_html_head_alter().
 */
function gwt_drupal_html_head_alter(&$head) {
  // Simplify the meta tag for character encoding.
  if (isset($head['system_meta_content_type']['#attributes']['content'])) {
    $head['system_meta_content_type']['#attributes'] = array('charset' => str_replace('text/html; charset=', '', $head['system_meta_content_type']['#attributes']['content']));
  }
}

/**
 * Override or insert variables into the page template.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
function gwt_drupal_preprocess_page(&$variables, $hook) {
  // Find the title of the menu used by the secondary links.
  $secondary_links = variable_get('menu_secondary_links_source', 'user-menu');
  if ($secondary_links) {
    $menus = function_exists('menu_get_menus') ? menu_get_menus() : menu_list_system_menus();
    $variables['secondary_menu_heading'] = $menus[$secondary_links];
  }
  else {
    $variables['secondary_menu_heading'] = '';
  }

  $variables['content_class'] = '';
  $variables['sidebar_first_class'] = ' large-3 medium-3';
  $variables['sidebar_second_class'] = ' large-3 medium-3';
  // if both sidbars are present set the content css for 3 column layout
  if($variables['page']['sidebar_first'] && $variables['page']['sidebar_second']){
    $variables['content_class'] .= ' large-6 medium-6 large-push-3 medium-push-3';
    $variables['sidebar_first_class'] .= ' large-pull-6 medium-push-6';
    // $variables['sidebar_second_class'] .= '';
  }
  // if left sidebar only
  elseif($variables['page']['sidebar_first'] && !$variables['page']['sidebar_second']){
    $variables['content_class'] .= ' large-9 medium-9 large-push-3 medium-push-3';
    $variables['sidebar_first_class'] .= ' large-pull-9 medium-pull-9';
    //$variables['sidebar_second_class'] .= '';
  }
  // if right sidebar only
  elseif(!$variables['page']['sidebar_first'] && $variables['page']['sidebar_second']){
    $variables['content_class'] .= ' large-9 medium-9';
    //$variables['sidebar_first_class'] .= ' pull-9';
    //$variables['sidebar_second_class'] .= '';
  }
  // if no sidebars present
  else{
    $variables['content_class'] .= ' large-12';
  }

  // create a dynamic column on banner
  $variables['banner_class'] = ' large-12';
  $variables['banner_2_class'] = '';
  $variables['banner_3_class'] = '';
  $variables['banner_container_class'] = ' has-border';

  if($variables['page']['banner']){
    // if both banner are available
    if($variables['page']['banner_2'] && $variables['page']['banner_3']){
      $variables['banner_class'] = ' large-6';
      $variables['banner_2_class'] = ' large-3';
      $variables['banner_3_class'] = ' large-3';
    }
    elseif($variables['page']['banner_2'] && !$variables['page']['banner_3']){
      $variables['banner_class'] = ' large-8';
      $variables['banner_2_class'] = ' large-4';
      //$variables['banner_3_class'] = '';
    }
    elseif(!$variables['page']['banner_2'] && $variables['page']['banner_3']){
      $variables['banner_class'] = ' large-9';
      //$variables['banner_2_class'] = '';
      $variables['banner_3_class'] = ' large-3';
    }
    $variables['banner_class'] .= ' show-for-medium-up';
  }
  elseif(!$variables['page']['banner']){
    $variables['banner_class'] = '';
    $variables['banner_container_class'] = '';
    // if both banner are available
    if($variables['page']['banner_2'] && $variables['page']['banner_3']){
      $variables['banner_2_class'] = ' large-6';
      $variables['banner_3_class'] = ' large-6';
    }
    elseif($variables['page']['banner_2'] && !$variables['page']['banner_3']){
      $variables['banner_2_class'] = ' large-12';
      //$variables['banner_3_class'] = '';
    }
    elseif(!$variables['page']['banner_2'] && $variables['page']['banner_3']){
      //$variables['banner_2_class'] = '';
      $variables['banner_3_class'] = ' large-12';
    }
    $variables['banner_class'] .= ' show-for-medium-up';
  }

  // create a dynamic column on banner
  $variables['name_slogan_class'] = ' large-12';
  $variables['ear_content_class'] = '';
  $variables['ear_content_2_class'] = '';
  // if both banner are available
  if($variables['page']['ear_content'] && $variables['page']['ear_content_2']){
    $variables['name_slogan_class'] = ' large-6';
    $variables['ear_content_class'] = ' large-3';
    $variables['ear_content_2_class'] = ' large-3';
  }
  elseif($variables['page']['ear_content'] && !$variables['page']['ear_content_2']){
    $variables['name_slogan_class'] = ' large-9';
    $variables['ear_content_class'] = ' large-3';
    //$variables['ear_content_2_class'] = '';
  }
  elseif(!$variables['page']['ear_content'] && $variables['page']['ear_content_2']){
    $variables['name_slogan_class'] = ' large-9';
    //$variables['ear_content_class'] = '';
    $variables['ear_content_2_class'] = ' large-3';
  }

  // create a dynamic column on agency footer
  $variables['footer_class'] = ' large-12';
  $variables['footer_2_class'] = '';
  $variables['footer_3_class'] = '';
  $variables['footer_4_class'] = '';
  if($variables['page']['footer_2']){
    $variables['footer_class'] = ' large-6';
    $variables['footer_2_class'] = ' large-6';
    $variables['footer_3_class'] = ' large-3';
    $variables['footer_4_class'] = ' large-3';
    if($variables['page']['footer_3'] && $variables['page']['footer_4']){
      $variables['footer_class'] = ' large-3';
      $variables['footer_2_class'] = ' large-3';
    }
    elseif(!$variables['page']['footer_3'] && $variables['page']['footer_4']){
      $variables['footer_2_class'] = ' large-3';
      $variables['footer_3_class'] = '';
      $variables['footer_4_class'] = '';
      $variables['footer_4_class'] = ' large-3';
    }
    elseif($variables['page']['footer_3'] && !$variables['page']['footer_4']){
      $variables['footer_2_class'] = ' large-3';
      $variables['footer_3_class'] = ' large-3';
      $variables['footer_4_class'] = '';
    }
    elseif(!$variables['page']['footer_3'] && !$variables['page']['footer_4']){
      $variables['footer_2_class'] = ' large-3';
      $variables['footer_3_class'] = '';
      $variables['footer_4_class'] = '';
    }
  }
  else{
    $variables['footer_class'] = ' large-6';
    $variables['footer_2_class'] = '';
    $variables['footer_3_class'] = ' large-3';
    $variables['footer_4_class'] = ' large-3';
    if(!$variables['page']['footer_3'] && $variables['page']['footer_4']){
      $variables['footer_3_class'] = ' large-6';
      $variables['footer_4_class'] = '';
    }
    elseif($variables['page']['footer_3'] && !$variables['page']['footer_4']){
      $variables['footer_3_class'] = '';
      $variables['footer_4_class'] = ' large-6';
    }
    elseif(!$variables['page']['footer_3'] && !$variables['page']['footer_4']){
      $variables['footer_class'] = ' large-12';
      $variables['footer_3_class'] = '';
      $variables['footer_4_class'] = '';
    }
  }

  // load the color used from theme_settings
  $variables['gwt_drupal_masthead_styles'] = 'style=" ';
  if($gwt_drupal_masthead_bg = theme_get_setting('gwt_drupal_masthead_background_color')){
    $variables['gwt_drupal_masthead_styles'] .= 'background-color: '.$gwt_drupal_masthead_bg.'; ';
  }

  $masthead_fid = theme_get_setting('gwt_drupal_masthead_bg_image');
  if($masthead_fid){
    $file = file_load($masthead_fid);
    if(isset($file->uri)){
      $variables['gwt_drupal_masthead_styles'] .= 'background-image: url('.file_create_url($file->uri).'); ';
    }
  }

  if($gwt_drupal_masthead_font = theme_get_setting('gwt_drupal_masthead_font_color')){
    $variables['gwt_drupal_masthead_styles'] .= 'color: '.$gwt_drupal_masthead_font.'; ';
  }
  $variables['gwt_drupal_masthead_styles'] .= '" ';

  $variables['gwt_drupal_banner_styles'] = 'style=" ';
  if($gwt_drupal_banner_bg_color = theme_get_setting('gwt_drupal_banner_background_color')){
    $variables['gwt_drupal_banner_styles'] .= 'background-color: '.$gwt_drupal_banner_bg_color.'; ';
  }

  $banner_fid = theme_get_setting('gwt_drupal_banner_bg_image');
  if($banner_fid){
    $file = file_load($banner_fid);
    if(isset($file->uri)){
      $variables['gwt_drupal_banner_styles'] .= 'background-image: url('.file_create_url($file->uri).'); ';
    }
  }

  if($gwt_drupal_banner_font = theme_get_setting('gwt_drupal_banner_font_color')){
    $variables['gwt_drupal_banner_styles'] .= 'color: '.$gwt_drupal_banner_font.'; ';
  }
  $variables['gwt_drupal_banner_styles'] .= '" ';

  $accessibility = array();
  $gwt_drupal_acc_statement = theme_get_setting('gwt_drupal_acc_statement') ? theme_get_setting('gwt_drupal_acc_statement') : '';
  if($gwt_drupal_acc_statement){
    $accessibility['0'] = array(
      'url' => $gwt_drupal_acc_statement,
      'text' => t('Accessibility Statement'),
      );
  }
  $gwt_drupal_acc_home = theme_get_setting('gwt_drupal_acc_home') ? theme_get_setting('gwt_drupal_acc_home') : '';
  if($gwt_drupal_acc_home){
    $accessibility['1'] = array(
      'url' => $gwt_drupal_acc_home,
      'text' => t('Home'),
      );
  }
  $gwt_drupal_acc_faq = theme_get_setting('gwt_drupal_acc_faq') ? theme_get_setting('gwt_drupal_acc_faq') : '';
  if($gwt_drupal_acc_faq){
    $accessibility['5'] = array(
      'url' => $gwt_drupal_acc_maincontent,
      'text' => t('Faqs'),
      );
  }
  $gwt_drupal_acc_contact = theme_get_setting('gwt_drupal_acc_contact') ? theme_get_setting('gwt_drupal_acc_contact') : '';
  if($gwt_drupal_acc_contact){
    $accessibility['C'] = array(
      'url' => $gwt_drupal_acc_contact,
      'text' => t('Contact'),
      );
  }
  $gwt_drupal_acc_feedback = theme_get_setting('gwt_drupal_acc_feedback') ? theme_get_setting('gwt_drupal_acc_feedback') : '';
  if($gwt_drupal_acc_feedback){
    $accessibility['K'] = array(
      'url' => $gwt_drupal_acc_feedback,
      'text' => t('Feedbacks'),
      );
  }
  $gwt_drupal_acc_search = theme_get_setting('gwt_drupal_acc_search') ? theme_get_setting('gwt_drupal_acc_search') : 'search/node/';
  if($gwt_drupal_acc_search){
    $accessibility['S'] = array(
      'url' => $gwt_drupal_acc_search,
      'text' => t('Search'),
      );
  }

  $variables['accesibility'] = '<ul>';
  foreach($accessibility as $access_key => $data){
    $variables['accesibility'] .= '<li>';
    $variables['accesibility'] .= l(
      $data['text'],
      $data['url'],
      array(
        'attributes' => array(
          'accesskey' => $access_key,
          'class' => 'skips',
          ),
        )
      );
    $variables['accesibility'] .= '</li>';
  }
  $gwt_drupal_acc_maincontent = theme_get_setting('gwt_drupal_acc_maincontent') ? theme_get_setting('gwt_drupal_acc_maincontent') : '#main-content';
  if($gwt_drupal_acc_maincontent){
    $variables['accesibility'] .= '<li><a href="'.$gwt_drupal_acc_maincontent.'" accesskey="R">Main Content</a></li>';
  }
  $gwt_drupal_acc_sitemap = theme_get_setting('gwt_drupal_acc_sitemap') ? theme_get_setting('gwt_drupal_acc_sitemap') : '#footer';
  if($gwt_drupal_acc_sitemap){
    $variables['accesibility'] .= '<li><a href="'.$gwt_drupal_acc_sitemap.'" accesskey="M">Sitemap</a></li>';
  }
  $variables['accesibility'] .= '</ul>';

  // TODO: create an auto accesibility page that loads all the accesibility theme_settings.
}

/**
 * Override or insert variables into the maintenance page template.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("maintenance_page" in this case.)
 */
function gwt_drupal_preprocess_maintenance_page(&$variables, $hook) {
  gwt_drupal_preprocess_html($variables, $hook);
  // There's nothing maintenance-related in gwt_drupal_preprocess_page(). Yet.
  //gwt_drupal_preprocess_page($variables, $hook);
}

/**
 * Override or insert variables into the maintenance page template.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("maintenance_page" in this case.)
 */
function gwt_drupal_process_maintenance_page(&$variables, $hook) {
  gwt_drupal_process_html($variables, $hook);
  // Ensure default regions get a variable. Theme authors often forget to remove
  // a deleted region's variable in maintenance-page.tpl.
  foreach (array('header', 'navigation', 'highlighted', 'help', 'content', 'sidebar_first', 'sidebar_second', 'footer', 'bottom') as $region) {
    if (!isset($variables[$region])) {
      $variables[$region] = '';
    }
  }
}

/**
 * Override or insert variables into the node templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
function gwt_drupal_preprocess_node(&$variables, $hook) {
  // Add $unpublished variable.
  $variables['unpublished'] = (!$variables['status']) ? TRUE : FALSE;

  // Add pubdate to submitted variable.
  $variables['pubdate'] = '<time pubdate datetime="' . format_date($variables['node']->created, 'custom', 'c') . '">' . $variables['date'] . '</time>';
  if ($variables['display_submitted']) {
    $variables['submitted'] = t('Submitted by !username on !datetime', array('!username' => $variables['name'], '!datetime' => $variables['pubdate']));
  }

  // Add a class for the view mode.
  if (!$variables['teaser']) {
    $variables['classes_array'][] = 'view-mode-' . $variables['view_mode'];
  }

  // Add a class to show node is authored by current user.
  if ($variables['uid'] && $variables['uid'] == $GLOBALS['user']->uid) {
    $variables['classes_array'][] = 'node-by-viewer';
  }

  $variables['title_attributes_array']['class'][] = 'node__title';
  $variables['title_attributes_array']['class'][] = 'node-title';
}

/**
 * Override or insert variables into the comment templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
function gwt_drupal_preprocess_comment(&$variables, $hook) {
  // If comment subjects are disabled, don't display them.
  if (variable_get('comment_subject_field_' . $variables['node']->type, 1) == 0) {
    $variables['title'] = '';
  }

  // Add pubdate to submitted variable.
  $variables['pubdate'] = '<time pubdate datetime="' . format_date($variables['comment']->created, 'custom', 'c') . '">' . $variables['created'] . '</time>';
  $variables['submitted'] = t('!username replied on !datetime', array('!username' => $variables['author'], '!datetime' => $variables['pubdate']));

  // Zebra striping.
  if ($variables['id'] == 1) {
    $variables['classes_array'][] = 'first';
  }
  if ($variables['id'] == $variables['node']->comment_count) {
    $variables['classes_array'][] = 'last';
  }
  $variables['classes_array'][] = $variables['zebra'];

  $variables['title_attributes_array']['class'][] = 'comment__title';
  $variables['title_attributes_array']['class'][] = 'comment-title';
}

/**
 * Preprocess variables for region.tpl.php
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("region" in this case.)
 */
function gwt_drupal_preprocess_region(&$variables, $hook) {
  // Sidebar regions get some extra classes and a common template suggestion.
  if (strpos($variables['region'], 'sidebar_') === 0) {
    $variables['classes_array'][] = 'column';
    $variables['classes_array'][] = 'sidebar';
    // Allow a region-specific template to override gwt_drupal's region--sidebar.
    array_unshift($variables['theme_hook_suggestions'], 'region__sidebar');
  }
  // Use a template with no wrapper for the content region.
  elseif ($variables['region'] == 'content') {
    // Allow a region-specific template to override gwt_drupal's region--no-wrapper.
    array_unshift($variables['theme_hook_suggestions'], 'region__no_wrapper');
  }
  // Add a SMACSS-style class for header region.
  elseif ($variables['region'] == 'header') {
    array_unshift($variables['classes_array'], 'header__region');
  }
}

/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function gwt_drupal_preprocess_block(&$variables, $hook) {
  // Use a template with no wrapper for the page's main content.
  if ($variables['block_html_id'] == 'block-system-main') {
    $variables['theme_hook_suggestions'][] = 'block__no_wrapper';
  }

  // Classes describing the position of the block within the region.
  if ($variables['block_id'] == 1) {
    $variables['classes_array'][] = 'first';
  }
  // The last_in_region property is set in gwt_drupal_page_alter().
  if (isset($variables['block']->last_in_region)) {
    $variables['classes_array'][] = 'last';
  }
  $variables['classes_array'][] = $variables['block_zebra'];

  $variables['title_attributes_array']['class'][] = 'block__title';
  $variables['title_attributes_array']['class'][] = 'block-title';

  // Add Aria Roles via attributes.
  switch ($variables['block']->module) {
    case 'system':
      switch ($variables['block']->delta) {
        case 'main':
          // Note: the "main" role goes in the page.tpl, not here.
          break;
        case 'help':
        case 'powered-by':
          $variables['attributes_array']['role'] = 'complementary';
          break;
        default:
          // Any other "system" block is a menu block.
          $variables['attributes_array']['role'] = 'navigation';
          break;
      }
      break;
    case 'menu':
    case 'menu_block':
    case 'blog':
    case 'book':
    case 'comment':
    case 'forum':
    case 'shortcut':
    case 'statistics':
      $variables['attributes_array']['role'] = 'navigation';
      break;
    case 'search':
      $variables['attributes_array']['role'] = 'search';
      break;
    case 'help':
    case 'aggregator':
    case 'locale':
    case 'poll':
    case 'profile':
      $variables['attributes_array']['role'] = 'complementary';
      break;
    case 'node':
      switch ($variables['block']->delta) {
        case 'syndicate':
          $variables['attributes_array']['role'] = 'complementary';
          break;
        case 'recent':
          $variables['attributes_array']['role'] = 'navigation';
          break;
      }
      break;
    case 'user':
      switch ($variables['block']->delta) {
        case 'login':
          $variables['attributes_array']['role'] = 'form';
          break;
        case 'new':
        case 'online':
          $variables['attributes_array']['role'] = 'complementary';
          break;
      }
      break;
  }
}

/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function gwt_drupal_process_block(&$variables, $hook) {
  // Drupal 7 should use a $title variable instead of $block->subject.
  $variables['title'] = isset($variables['block']->subject) ? $variables['block']->subject : '';
}

/**
 * Implements hook_page_alter().
 *
 * Look for the last block in the region. This is impossible to determine from
 * within a preprocess_block function.
 *
 * @param $page
 *   Nested array of renderable elements that make up the page.
 */
function gwt_drupal_page_alter(&$page) {
  // Look in each visible region for blocks.
  foreach (system_region_list($GLOBALS['theme'], REGIONS_VISIBLE) as $region => $name) {
    if (!empty($page[$region])) {
      // Find the last block in the region.
      $blocks = array_reverse(element_children($page[$region]));
      while ($blocks && !isset($page[$region][$blocks[0]]['#block'])) {
        array_shift($blocks);
      }
      if ($blocks) {
        $page[$region][$blocks[0]]['#block']->last_in_region = TRUE;
      }
    }
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 *
 * Prevent user-facing field styling from screwing up node edit forms by
 * renaming the classes on the node edit form's field wrappers.
 */
function gwt_drupal_form_node_form_alter(&$form, &$form_state, $form_id) {
  // Remove if #1245218 is backported to D7 core.
  foreach (array_keys($form) as $item) {
    if (strpos($item, 'field_') === 0) {
      if (!empty($form[$item]['#attributes']['class'])) {
        foreach ($form[$item]['#attributes']['class'] as &$class) {
          // Core bug: the field-type-text-with-summary class is used as a JS hook.
          if ($class != 'field-type-text-with-summary' && strpos($class, 'field-type-') === 0 || strpos($class, 'field-name-') === 0) {
            // Make the class different from that used in theme_field().
            $class = 'form-' . $class;
          }
        }
      }
    }
  }
}

/**
 * Returns HTML for primary and secondary local tasks.
 *
 * @ingroup themeable
 */
function gwt_drupal_menu_local_tasks(&$variables) {
  $output = '';

  // Add theme hook suggestions for tab type.
  foreach (array('primary', 'secondary') as $type) {
    if (!empty($variables[$type])) {
      foreach (array_keys($variables[$type]) as $key) {
        if (isset($variables[$type][$key]['#theme']) && ($variables[$type][$key]['#theme'] == 'menu_local_task' || is_array($variables[$type][$key]['#theme']) && in_array('menu_local_task', $variables[$type][$key]['#theme']))) {
          $variables[$type][$key]['#theme'] = array('menu_local_task__' . $type, 'menu_local_task');
        }
      }
    }
  }

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="tabs-primary tabs primary">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="tabs-secondary tabs secondary">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Returns HTML for a single local task link.
 *
 * @ingroup themeable
 */
function gwt_drupal_menu_local_task($variables) {
  $type = $class = FALSE;

  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  // Check for tab type set in gwt_drupal_menu_local_tasks().
  if (is_array($variables['element']['#theme'])) {
    $type = in_array('menu_local_task__secondary', $variables['element']['#theme']) ? 'tabs-secondary' : 'tabs-primary';
  }

  // Add SMACSS-style class names.
  if ($type) {
    $link['localized_options']['attributes']['class'][] = $type . '__tab-link';
    $class = $type . '__tab';
  }

  if (!empty($variables['element']['#active'])) {
    // Add text to indicate active tab for non-visual users.
    $active = ' <span class="element-invisible">' . t('(active tab)') . '</span>';

    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));

    if (!$type) {
      $class = 'active';
    }
    else {
      $link['localized_options']['attributes']['class'][] = 'is-active';
      $class .= ' is-active';
    }
  }

  return '<li' . ($class ? ' class="' . $class . '"' : '') . '>' . l($link_text, $link['href'], $link['localized_options']) . "</li>\n";
}

/**
 * Implements hook_preprocess_menu_link().
 */
function gwt_drupal_preprocess_menu_link(&$variables, $hook) {
  foreach ($variables['element']['#attributes']['class'] as $key => $class) {
    switch ($class) {
      // Menu module classes.
      case 'expanded':
      case 'collapsed':
      case 'leaf':
      case 'active':
      // Menu block module classes.
      case 'active-trail':
        array_unshift($variables['element']['#attributes']['class'], 'is-' . $class);
        break;
      case 'has-children':
        array_unshift($variables['element']['#attributes']['class'], 'is-parent');
        break;
    }
  }
  array_unshift($variables['element']['#attributes']['class'], 'menu__item');
  if (empty($variables['element']['#localized_options']['attributes']['class'])) {
    $variables['element']['#localized_options']['attributes']['class'] = array();
  }
  else {
    foreach ($variables['element']['#localized_options']['attributes']['class'] as $key => $class) {
      switch ($class) {
        case 'active':
        case 'active-trail':
          array_unshift($variables['element']['#localized_options']['attributes']['class'], 'is-' . $class);
          break;
      }
    }
  }
  array_unshift($variables['element']['#localized_options']['attributes']['class'], 'menu__link');
}

/**
 * Returns HTML for status and/or error messages, grouped by type.
 */
function gwt_drupal_status_messages($variables) {
  $display = $variables['display'];
  $output = '';

  $status_heading = array(
    'status' => t('Status message'),
    'error' => t('Error message'),
    'warning' => t('Warning message'),
  );
  foreach (drupal_get_messages($display) as $type => $messages) {
    $output .= "<div class=\"messages--$type messages $type\">\n";
    if (!empty($status_heading[$type])) {
      $output .= '<h2 class="element-invisible">' . $status_heading[$type] . "</h2>\n";
    }
    if (count($messages) > 1) {
      $output .= " <ul class=\"messages__list\">\n";
      foreach ($messages as $message) {
        $output .= '  <li class=\"messages__item\">' . $message . "</li>\n";
      }
      $output .= " </ul>\n";
    }
    else {
      $output .= $messages[0];
    }
    $output .= "</div>\n";
  }
  return $output;
}

/**
 * Returns HTML for a marker for new or updated content.
 */
function gwt_drupal_mark($variables) {
  $type = $variables['type'];

  if ($type == MARK_NEW) {
    return ' <mark class="new">' . t('new') . '</mark>';
  }
  elseif ($type == MARK_UPDATED) {
    return ' <mark class="updated">' . t('updated') . '</mark>';
  }
}

/**
 * Alters the default Panels render callback so it removes the panel separator.
 */
function gwt_drupal_panels_default_style_render_region($variables) {
  return implode('', $variables['panes']);
}
