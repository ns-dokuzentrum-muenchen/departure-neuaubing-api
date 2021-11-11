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
    'video' => get_field('video', 'options'),
    'projekte' => get_field('projekte', 'options')
  );

  $response = new WP_REST_Response($data);
  $response->set_status(200);
  return $response;
}

function dn_search (WP_REST_Request $request) {
  $keyword = sanitize_text_field($request['s']);

  if ($keyword == null) {
    return new WP_Error('no_posts', 'Nothing found', array('status' => 404));
  }

  $args = array(
    's' => $keyword
  );
  $query = new WP_Query();
  $query->parse_query($args);
  relevanssi_do_query($query);

  if ($query->post_count) {
    foreach ($query->posts as $post) {
      $acf = get_fields($post->ID);

      $post->id = intval($post->ID);
      $post->categories = wp_get_post_categories($post->ID, array('fields' => 'all'));
      $post->tags = wp_get_post_tags($post->ID);
      $post->acf = $acf;
      $post->title_highlighted = relevanssi_highlight_terms(html_entity_decode($post->post_title), $keyword);
      $post->link = get_the_permalink($post->ID);
      $post->slug = $post->post_name;

      if ($acf) {
        $text = $acf['biographie'] ?? $acf['description']; // add more?
        $post->content_highlighted = relevanssi_highlight_terms($text, $keyword, true);
      }
    }
    $response = json_encode($query->posts);
    $response = json_decode($response, true);
    return new WP_REST_RESPONSE($response, 200);
  } else {
    return new WP_Error('no_posts', 'Nothing found', array('status' => 404));
  }
}

// function comment_nonce () {
//   $nonce = wp_create_nonce('hcaptcha_comment_form_nonce'); // ??
//   $data = array( 'nonce' => $nonce );
//   $response = new WP_REST_Response($data);
//   $response->set_status(200);
//   return $response;
// }

function passwordless_login (WP_REST_Request $request) {
  $account = ( isset( $request['user_email_username']) ) ? $account = sanitize_text_field( $request['user_email_username'] ) : false;
  $nonce = ( isset( $_POST['nonce']) ) ? $nonce = sanitize_key( $_POST['nonce'] ) : false;
  $return_to = ( isset( $_POST['return_to']) ) ? $return_to = esc_url_raw( $_POST['return_to'] ) : false;

  $msg = '';
  $status = 200;

  if (!$account || !$nonce) {
    $msg = __('Login with email or username', 'passwordless-login');
    $status = 400;
  } else {
    if (function_exists('wpa_send_link')) {
      $sent_link = wpa_send_link($account, $nonce, $return_to);
    } else {
      $msg = '404 Not found';
      $status = 404;
    }

    if ($account && !is_wp_error($sent_link)) {
      $msg = apply_filters('wpa_success_link_msg', __('Please check your email. You will soon receive an email with a login link.', 'passwordless-login'));
    } elseif (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      $msg = apply_filters('wpa_success_login_msg', __( 'You are currently logged in as %1$s. %2$s', 'passwordless-login' ));
      $msg .= $current_user->display_name;
    } else {
      if (is_wp_error($sent_link)) {
        $msg = apply_filters('wpa_error', $sent_link->get_error_message());
      } else {
        $msg = '404 Not found';
      }
      $status = 404;
    }
  }

  $response = new WP_REST_Response(array('msg' => $msg));
  $response->set_status($status);

  return $response;
}

function dn_register (WP_REST_Request $request) {
  $body = $request->get_json_params();
  $username = sanitize_text_field( $body['username'] );
  $email = sanitize_email( $body['email'] );
  $nonce = sanitize_key( $body['nonce'] );
  $return_to = esc_url_raw( $body['return_to'] );

  $response = new WP_REST_Response();

  if ($username && $email) {
    $pass = random_bytes(32);
    $user = wp_create_user($username, $pass, $email);

    if (is_wp_error($user)) {
      $response->set_data(array('msg' => 'Benutzer konnte mit diesen Angaben nicht registriert werden.'));
      $response->set_status(400);
      return $response;
    } else {
      // user has been created, send a login link
      $sent_link = wpa_send_link($username, $nonce, $return_to);

      if (!is_wp_error($sent_link)) {
        $msg = apply_filters('wpa_success_link_msg', __('Please check your email. You will soon receive an email with a login link.', 'passwordless-login'));

        $response->set_data(array('msg' => $msg));
        return $response;
      }
    }
  }

  $response->set_data(array('msg' => 'Fehler. Bitte überprüfen Sie das Formular.'));
  $response->set_status(400);
  return $response;
}

// geo
require 'vendor/autoload.php';
use GeoIp2\WebService\Client;
$geo_client = new Client(482594, '8FGlhsaF0TdRPf5Z', ['en'], ['host' => 'geolite.info']);

add_action('rest_api_init', function () {
  register_rest_route('dn/v1', '/settings', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_settings',
  ));
  register_rest_route('dn/v1', '/suche', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_search'
  ));

  register_rest_route('dn/v1', '/login', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'passwordless_login'
  ));
  register_rest_route('dn/v1', '/register', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'dn_register'
  ));

  register_rest_route('dn/v1', '/geo', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => function () {
      global $geo_client;

      $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['REMOTE_ADDR'];
      $proxy = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'];
      $check = $proxy ? $proxy : $ip;

      try {
        $record = $geo_client->city($check);
      } catch (Exception $e) {
        $record = null;
      }

      $response = new WP_REST_Response(array(
        'record' => $record
      ));
      return $response;
    }
  ));
});

add_filter('jwt_auth_whitelist', function ($endpoints) {
  array_push($endpoints, '/wp-json/dn/v1/*');
  return $endpoints;
});
