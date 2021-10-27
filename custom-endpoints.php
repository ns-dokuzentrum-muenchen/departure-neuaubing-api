<?php
function dn_settings () {
  // define('HOME_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);

  // strip local origins, for router-link
  // $menu = get_field('main_menu', 'options');
  // foreach ($menu as &$item) {
  //   $item['link'] = str_replace(HOME_URL, '', $item['link']);
  // }

  // $f_menu = get_field('footer_menu', 'options');
  // foreach ($f_menu as &$item) {
  //   $item['link'] = str_replace(HOME_URL, '', $item['link']);
  // }

  $data = array(
    'cover' => get_field('image', 'options'),
    'video' => get_field('video', 'options')
  );

  $response = new WP_REST_Response($data);
  $response->set_status(200);
  return $response;
}

function comment_nonce () {
  $nonce = wp_create_nonce('hcaptcha_comment_form_nonce'); // ??
  $data = array( 'nonce' => $nonce );
  $response = new WP_REST_Response($data);
  $response->set_status(200);
  return $response;
}

add_action('rest_api_init', function () {
  register_rest_route('dn/v1', '/settings', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_settings',
  ));
  register_rest_route('dn/v1', '/comment-nonce', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'comment_nonce'
  ));
});
