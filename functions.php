<?php
// sort cors (*)
$host = $_SERVER['https'] === 'on' ? 'https://' : 'http://';
$host .= $_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: ' . $host);

// IMAGES
// add_theme_support('post-thumbnails');
// add_image_size('small', 300, 300);
// add_filter('image_size_names_choose', function ($sizes) {
//   return array_merge($sizes, array(
//     'small' => __('Small size')
//   ));
// });

// hide posts (not used)
add_action('admin_menu', function () {
  // remove_menu_page('edit.php');
});

// Page base
add_action('init', function () {
  global $wp_rewrite;
  // print_r($wp_rewrite);
  $wp_rewrite->page_structure = 'pages/%pagename%';
});

// acf things
// function my_acf_init () {
//   acf_update_setting('google_api_key', 'xxx'); // TODO: need a key?
// }
// add_action('acf/init', 'my_acf_init');

if (function_exists('acf_add_options_page')) {
  acf_add_options_page(array(
    'page_title' => __('Einstellungen'),
    'position' => 4.3,
    'autoload' => true
  ));
}

add_filter('acf/rest_api/post/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/projekt/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/kuenstler/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/person/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/glossar/get_fields', 'include_nested_acf_data');

function include_nested_acf_data ($data) {
  if (!empty($data)) {
    array_walk_recursive($data, 'get_fields_recursive');
  }
  return $data;
}

function get_fields_recursive ($item) {
  if (is_object($item)) {
    // $item->author_name = get_the_author_meta('display_name', $item->post_author);
    $item->permalink = get_the_permalink($item);

    // acf fields
    if ($fields = get_fields($item)) {
      $item->acf = $fields;
      array_walk_recursive($item->acf, 'get_fields_recursive');
    }
  } else if (is_array($item)) {
    $item['permalink'] = get_the_permalink($item['id']);

    // acf fields
    if ($fields = get_fields($item['id'])) {
      $item['acf'] = $fields;
      array_walk_recursive($item['acf'], 'get_fields_recursive');
    }
  }
}

// enable some comments
function default_comments_on ($data) {
  $pt = $data['post_type'];

  if ($pt == 'glossar' || $pt == 'markierung') {
    $data['comment_status'] = 'open';
  }

  return $data;
}
add_filter('wp_insert_post_data', 'default_comments_on');

add_filter('rest_allow_anonymous_comments', '__return_true');

require 'custom-post-types.php';
require 'custom-endpoints.php';
require 'custom-hooks.php';
