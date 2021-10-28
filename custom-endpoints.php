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

      $text = $acf['biographie'] ?? $acf['description']; // add more?

      $post->content_highlighted = relevanssi_highlight_terms($text, $keyword, true);
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

add_action('rest_api_init', function () {
  register_rest_route('dn/v1', '/settings', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_settings',
  ));
  register_rest_route('dn/v1', '/suche', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_search'
  ));

  register_rest_route('dn/v1', '/comment-nonce', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'comment_nonce'
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
