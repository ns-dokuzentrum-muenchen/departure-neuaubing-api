<?php
// IMAGES
// add_theme_support('post-thumbnails');
// add_image_size('small', 300, 300);
// add_filter('image_size_names_choose', function ($sizes) {
//   return array_merge($sizes, array(
//     'small' => __('Small size')
//   ));
// });

// Page base
add_action('init', function () {
  global $wp_rewrite;
  // print_r($wp_rewrite);
  $wp_rewrite->page_structure = 'pages/%pagename%';
});

// acf things
if (function_exists('acf_add_options_page')) {
  acf_add_options_page(array(
    'page_title' => __('Site Settings'),
    'position' => 4.3,
    'autoload' => true
  ));
}

add_filter('acf/rest_api/post/get_fields', 'include_nested_acf_data');

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

require 'custom-endpoints.php';
require 'custom-hooks.php';
