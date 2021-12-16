<?php
// sort cors (*)
// $host = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
// $host .= $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'];
// header('Access-Control-Allow-Origin: ' . $host);
$origins = ['http://localhost:8080', 'https://departure-neuaubing-stage.netlify.app'];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origins)) {
  header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
  header('Access-Control-Expose-Headers: X-WP-Nonce, X-Auth-Token, X-Login-Message');
}

// IMAGES
add_theme_support('post-thumbnails');
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

// prevent commenters setting name/email/auther etc
add_filter('rest_pre_dispatch', function ($response, $server, WP_REST_Request $request) {
  if ($request->get_route() == '/wp/v2/comments' && $request->get_method() === 'POST') {
    // TODO just throw an error?
    $request->offsetUnset('author');
    $request->offsetUnset('author_name');
    $request->offsetUnset('author_email');
  }
  return $response;
}, 0, 3);

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

add_filter('acf/rest_api/projekt/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/int-projekt/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/kuenstler/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/person/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/glossar/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/ort/get_fields', 'include_nested_acf_data');
add_filter('acf/rest_api/page/get_fields', 'include_nested_acf_data');

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

    if ($fields = get_fields($item)) {
      $item->acf = $fields;

      if ($item->post_type == 'int-projekt') return;
      array_walk_recursive($item->acf, 'get_fields_recursive');
    }
  } else if (is_array($item)) {
    $item['permalink'] = get_the_permalink($item['id']);

    if ($fields = get_fields($item['id'])) {
      $item['acf'] = $fields;
      if ($item['post_type'] == 'int-projekt') return;
      array_walk_recursive($item['acf'], 'get_fields_recursive');
    }
  }
}

// enable some comments
function default_comments_on ($data) {
  $pt = $data['post_type'];

  if ($pt == 'forum') {
    $data['comment_status'] = 'open';
  }

  return $data;
}
add_filter('wp_insert_post_data', 'default_comments_on');
// add_filter('rest_allow_anonymous_comments', '__return_true');

add_filter('user_has_cap', function ($all, $cap, $args, $user) {
  if (is_user_logged_in() && isset($cap[0]) && $cap[0] == 'edit_posts') {
    if ($_SERVER['REQUEST_URI'] == '/wp-json/wp/v2/markierungen') {
      if (isset($user->caps['subscriber']) && $user->caps['subscriber']) {
        $all['edit_posts'] = 1;
      }
    }
  }
  return $all;
}, 10, 4);

require 'custom-post-types.php';
require 'custom-endpoints.php';
require 'custom-hooks.php';
require 'custom-editor.php';

// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
  $filetype = wp_check_filetype( $filename, $mimes );

  return [
    'ext'             => $filetype['ext'],
    'type'            => $filetype['type'],
    'proper_filename' => $data['proper_filename']
  ];
}, 10, 4 );

function cc_mime_types( $mimes ){
  if (current_user_can('publish_posts')) {
    $mimes['svg'] = 'image/svg+xml';
  }
  return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action( 'admin_head', 'fix_svg' );
