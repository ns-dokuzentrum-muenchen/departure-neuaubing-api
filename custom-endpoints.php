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

function comment_nonce () {
  $nonce = wp_create_nonce('hcaptcha_comment_form_nonce'); // ??
  $data = array( 'nonce' => $nonce );
  $response = new WP_REST_Response($data);
  $response->set_status(200);
  return $response;
}

function passwordless_login (WP_REST_Request $request) {
  $account = ( isset( $request['user_email_username']) ) ? $account = sanitize_text_field( $request['user_email_username'] ) : false;
  $nonce = wp_create_nonce('wpa_passwordless_login_request');

  $msg = '';
  $status = 200;

  if (!$account || !$nonce) {
    $msg = __('Login with email or username', 'passwordless-login') . $account . $nonce;
    $status = 400;
  } else {
    if (function_exists('wpa_send_link')) {
      $sent_link = wpa_send_link($account, $nonce);
    } else {
      $msg = '404 Not found';
      $status = 404;
    }

    if ($account && !is_wp_error($sent_link)) {
      $msg = apply_filters('wpa_success_link_msg', __('Please check your email. You will soon receive an email with a login link.', 'passwordless-login'));
    } elseif (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      $msg = apply_filters('wpa_success_login_msg', sprintf(__( 'You are currently logged in as %1$s. %2$s', 'passwordless-login' )));
      $msg .= $current_user->display_name;
    } else {
      if (is_wp_error($sent_link)) {
        $msg = apply_filters('wpa_error', $sent_link->get_error_message());
        $status = 404;
      } else {
        $msg = '404 Not found';
        $status = 404;
      }
    }
  }

  $response = new WP_REST_Response(array('msg' => $msg));
  $response->set_status($status);
  return $response;
}
function handle_login (WP_REST_Request $request) {
  $error_token = ( isset( $request['wpa_error_token']) ) ? $error_token = sanitize_key( $request['wpa_error_token'] ) : false;

  if( $error_token ) {
    $msg = apply_filters( 'wpa_invalid_token_error', __('Your token has probably expired. Please try again.', 'passwordless-login') );
    $response = new WP_REST_Response(array('msg' => $msg));
  $response->set_status(200);
  return $response;
  } else {
    $current_user = wp_get_current_user();
    $msg = apply_filters('wpa_success_login_msg', sprintf(__( 'You are currently logged in as %1$s. %2$s', 'passwordless-login' )));
    $msg .= $current_user->display_name;

    $data = Auth::generate_token($current_user, true);

    // $response = new WP_REST_Response(array('msg' => $msg));
    $response = new WP_REST_Response($data);
    $response->set_status(200);
    return $response;
  }
}

add_action('rest_api_init', function () {
  register_rest_route('dn/v1', '/settings', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_settings',
  ));
  register_rest_route('dn/v1', '/suche', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_search'
  ));

  // register_rest_route('dn/v1', '/comment-nonce', array(
  //   'methods' => WP_REST_Server::READABLE,
  //   'callback' => 'comment_nonce'
  // ));

  register_rest_route('dn/v1', '/login', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'passwordless_login'
  ));
  register_rest_route('dn/v1', '/login', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'handle_login'
  ));

  register_rest_route('dn/v1', '/nonce', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => function () {
      // $response = new WP_REST_Response(wp_create_nonce('wp_rest'));
      $response = new WP_REST_Response(wp_get_current_user());
      // wp_verify_nonce()
      $response->set_status(200);
      return $response;
    }
  ));
  // register_rest_route('dn/v1', '/what', array(
  //   'methods' => WP_REST_Server::READABLE,
  //   'callback' => function () {
  //     $serv = $_SERVER;
  //     $response = new WP_REST_Response($serv);
  //     $response->set_status(200);
  //     return $response;
  //   }
  // ));
});


add_filter('jwt_auth_whitelist', function ($endpoints) {
  array_push($endpoints,'/wp-json/dn/v1/*');
  return $endpoints;
});
