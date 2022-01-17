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
    'about' => get_field('about', 'options'),
    'intro' => get_field('intro', 'options'),
    'seitenleiste' => get_field('seitenleiste', 'options'),
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

function dn_all_places () {
  $markers = get_posts(array(
    'post_type' => 'markierung',
    'numberposts' => -1,
    'meta_key' => 'id',
    'order_by' => 'meta_value',
    'order' => 'ASC'
  ));

  $res = [];

  foreach ($markers as $marker) {
    $acf = get_fields($marker);
    $tmp = array(
      'title' => $marker->post_title,
      'post_id' => $marker->ID,
      'id' => $acf['id'],
      'place_id' => $acf['place_id'],
      'konzentrationslager' => $acf['konzentrationslager'],
      'kriegsgefangenenlager' => $acf['kriegsgefangenenlager'],
      'zivilarbeiterlager' => $acf['zivilarbeiterlager'],
      'sonstigelager' => $acf['sonstigelager'],
      'num_people_cat_id' => $acf['num_people_cat_id'],
      'location' => $acf['location'],
      'description' => $acf['description'],
      'source' => $acf['source'],
      'from_artist' => (bool)$acf['foto_id'],
      'slug' => $marker->post_name
    );
    array_push($res, $tmp);
  }

  $response = new WP_REST_Response($res);
  $response->set_status(200);
  return $response;
}

function dn_search_places (WP_REST_Request $request) {
  $keyword = sanitize_text_field($request['s']);

  if ($keyword == null) {
    return new WP_Error('no_posts', 'Nothing found', array('status' => 404));
  }

  $args = array(
    's' => $keyword,
    'post_type' => 'markierung'
  );
  $query = new WP_Query();
  $query->parse_query($args);
  relevanssi_do_query($query);

  if ($query->post_count) {
    $res = [];

    // TODO: highlight terms
    foreach ($query->posts as $marker) {
      $acf = get_fields($marker);
      $tmp = array(
        'title' => $marker->post_title,
        'post_id' => $marker->ID,
        'id' => $acf['id'],
        'place_id' => $acf['place_id'],
        'konzentrationslager' => $acf['konzentrationslager'],
        'kriegsgefangenenlager' => $acf['kriegsgefangenenlager'],
        'zivilarbeiterlager' => $acf['zivilarbeiterlager'],
        'sonstigelager' => $acf['sonstigelager'],
        'num_people_cat_id' => $acf['num_people_cat_id'],
        'location' => $acf['location'],
        'description' => $acf['description'],
        'source' => $acf['source'],
        'from_artist' => (bool)$acf['foto_id'],
        'slug' => $marker->post_name
      );
      array_push($res, $tmp);
    }

    $response = new WP_REST_Response($res);
    $response->set_status(200);
    return $response;
    // foreach ($query->posts as $post) {
    //   $acf = get_fields($post->ID);

    //   $post->id = intval($post->ID);
    //   $post->categories = wp_get_post_categories($post->ID, array('fields' => 'all'));
    //   $post->tags = wp_get_post_tags($post->ID);
    //   $post->acf = $acf;
    //   $post->title_highlighted = relevanssi_highlight_terms(html_entity_decode($post->post_title), $keyword);
    //   $post->link = get_the_permalink($post->ID);
    //   $post->slug = $post->post_name;

    //   if ($acf) {
    //     $text = $acf['biographie'] ?? $acf['description']; // add more?
    //     $post->content_highlighted = relevanssi_highlight_terms($text, $keyword, true);
    //   }
    // }
    // $response = json_encode($query->posts);
    // $response = json_decode($response, true);
    // return new WP_REST_RESPONSE($response, 200);
  } else {
    return new WP_Error('no_posts', 'Nothing found', array('status' => 404));
  }
}

function dn_user_posts () {
  $args = array(
    'post_type' => array('begriff', 'upload'),
    'posts_per_page' => -1,
    'post__in' => array_unique(
      wp_list_pluck(
        get_comments(
          array(
            'user_id' => get_current_user_id()
          )
        ),
        'comment_post_ID'
      )
    )
  );

  $res = new WP_Query($args);

  $response = new WP_REST_Response($res->posts);
  $response->set_status(200);
  return $response;
}

// geo
require 'vendor/autoload.php';
use GeoIp2\WebService\Client;
$geo_client = new Client(482594, '8FGlhsaF0TdRPf5Z', ['de'], ['host' => 'geolite.info']);

add_action('rest_api_init', function () {
  register_rest_route('dn/v1', '/settings', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_settings'
  ));
  register_rest_route('dn/v1', '/suche', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_search'
  ));

  register_rest_route('dn/v1', '/places', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_all_places'
  ));
  register_rest_route('dn/v1', '/place-search', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_search_places'
  ));

  register_rest_route('dn/v1', '/login', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'passwordless_login'
  ));
  register_rest_route('dn/v1', '/register', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'dn_register'
  ));
  register_rest_route('dn/v1', '/konto', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'dn_user_posts'
  ));

  register_rest_route('dn/v1', '/comments', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => function () {
      $response = new WP_REST_Response();

      $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);
      if (!isset($post_id)) {
        $response->set_status(404);
        return $response;
      }
      $args = array(
        'post_id' => $post_id,
        'status' => 'approve',
        'type' => 'comment',
        'orderby' => array(
          'comment_parent' => 'asc',
          'comment_date' => 'asc'
        )
      );
      $q = new WP_Comment_Query();

      $comments = $q->query($args);
      $map = [];

      // format comments, add them to Parent -> Child map
      foreach ($comments as &$comment) {
        $comment = comment_data($comment);
        $parent = $comment['parent'];
        $map[$parent] = $map[$parent] ?? [];
        array_unshift($map[$parent], $comment['id']);
      }

      function children ($id, $comments, $map) {
        $i = array_search($id, array_column($comments, 'id'));
        $comment = ($i !== false ? $comments[$i] : null);
        if (!isset($map[$id])) return $comment;

        $comment['children'] = array_map(function ($id) use ($comments, $map) {
          return children($id, $comments, $map);
        }, $map[$id]);
        return $comment;
      };

      // build recursive nested structure
      $nested = array_map(function ($c) use ($comments, $map) {
        if (!isset($map[$c['id']])) return $c;
        $c['children'] = array_map(function ($id) use ($comments, $map) {
          return children($id, $comments, $map);
        }, $map[$c['id']]);
        return $c;
      }, array_filter($comments, function ($c) {
        return $c['parent'] == 0;
      }));

      $response->set_data($nested);
      $response->set_status(200);
      return $response;
    }
  ));

  function comment_data ($c) {
    $author_id = (int) $c->user_id;
    $comment_id = (int) $c->comment_ID;
    $comment = array(
      'id' => $comment_id,
      'post' => (int) $c->comment_post_ID,
      'parent' => (int) $c->comment_parent,
      'author' => $author_id,
      'author_name' => $c->comment_author,
      'author_display_name' => get_the_author_meta('display_name', $author_id),
      'date' => get_comment_date('c', $comment_id),
      'content' => apply_filters('the_content', $c->comment_content),
      'link' => get_comment_link($comment_id),
      'author_avatar_url' => get_avatar_url($author_id),
      'status' => 'approved',
      'type' => 'comment',
      'children' => []
    );

    return $comment;
  }

  // export interface Comment {
  //   id: number
  //   post: number
  //   parent: number
  //   author: number
  //   author_name: string
  //   author_display_name: string
  //   author_url: string
  //   date: string
  //   content: RenderedString
  //   link: string
  //   status: string
  //   type: string
  //   author_avatar_urls: { 24: string, 48: string, 96: string }
  // }

  function getDistanceFromLatLonInKm($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Radius of the earth in km
    $dLat = deg2rad($lat2 - $lat1);  // deg2rad below
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
      cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
      sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $d = $R * $c; // Distance in km
    return $d;
  }

  function distance($lat1, $lon1, $lat2, $lon2) {
    $p = 0.017453292519943295;    // Math.PI / 180
    $a = 0.5 - cos(($lat2 - $lat1) * $p)/2 +
            cos($lat1 * $p) * cos($lat2 * $p) *
            (1 - cos(($lon2 - $lon1) * $p))/2;

    return 12742 * asin(sqrt($a)); // 2 * R; R = 6371 km
  }

  register_rest_route('dn/v1', '/geo', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => function () {
      global $geo_client;

      $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
      $proxy = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
      $check = $proxy ? $proxy : $ip;
      // $check = '209.58.188.173';
      $city = null;
      $country = null;
      $lat = 0;
      $lng = 0;
      $err = false;

      $response = new WP_REST_Response();

      try {
        $loc = $geo_client->city($check);
        $city = $loc->city->names;
        $country = $loc->country->names;
        // $city = $loc;
        $lat = $loc->location->latitude;
        $lng = $loc->location->longitude;
      } catch (Exception $e) {
        $loc = null;
        $err = $e->getMessage();
        $city = ['de' => 'Berlin'];
        $lat = 52.4564;
        $lng = 13.3425;
      } catch (Throwable $e) {
        $loc = null;
        $err = $e->getMessage();
        $city = ['de' => 'Berlin'];
        $lat = 52.4564;
        $lng = 13.3425;
      }

      $distance = distance($lat, $lng, 48.150699, 11.427918);

      $record = array(
        'city' => $city,
        'country' => $country,
        'distance' => $distance,
        'full' => $loc
      );

      if ($err) {
        $record['msg'] = $err;
      }

      $response->set_data($record);
      $response->set_status(200);
      return $response;
    }
  ));

  register_rest_field(['forum', 'begriff'], 'comment_count', array(
    'get_callback' => function ($object) {
      return get_comments_number($object['id']);
    }
  ));
  register_rest_field(['forum', 'begriff'], 'author_name', array(
    'get_callback' => function ($object) {
      $name = get_the_author_meta('display_name', $object['author']);
      return $name;
    }
  ));
  register_rest_field('comment', 'author_display_name', array(
    'get_callback' => function ($object) {
      $name = get_the_author_meta('display_name', $object['author']);
      return $name;
      // $first = get_the_author_meta('first_name', $object['author']);
      // $last = get_the_author_meta('last_name', $object['author']);
      // $names = [];

      // if ($first) {
      //   array_push($names, $first);
      // }
      // if ($last) {
      //   array_push($names, $last);
      // }
      // if (!count($names)) {
      //   array_push($names, $object['author_name']);
      // }

      // return implode(' ', $names);
    }
  ));
});

add_filter('jwt_auth_whitelist', function ($endpoints) {
  array_push($endpoints, '/wp-json/dn/v1/*');
  return $endpoints;
});

// function filter_rest_res ($response, $post, $request) {
//   $acf = $response->data['acf'];
//   if ($acf && $acf['content']) {
//     preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', json_encode($acf['content'], JSON_UNESCAPED_SLASHES), $matches);
//     // process the links
//     print_r($matches);
//   }
//   return $response;
// }
// add_filter('rest_prepare_projekt', 'filter_rest_res', 10, 3);
