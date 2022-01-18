<?php
// post an update to the frontend, for rebuild
function update_frontend ($post_id) {
  // Autosave, do nothing
  if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) return;
  // AJAX? Not used here
  if (defined( 'DOING_AJAX' ) && DOING_AJAX) return;
  // Check user permissions
  if (!current_user_can('edit_post', $post_id)) return;
  // Return if it's a post revision
  if (false !== wp_is_post_revision($post_id)) return;

  $url = 'https://api.netlify.com/build_hooks/?????????'; // Netlify Build Hook
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([]));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // ping the thing
  $response = curl_exec($ch);
  curl_close($ch);
}
// add_action('save_post', 'update_frontend');

// custom urls
add_filter('get_avatar_url', function ($url, $thing) {
  $id = 0;
  if (gettype($thing) == 'object') {
    if (isset($thing->user_id)) {
      $id = (int) $thing->user_id;
    } else if (isset($thing->post_author)) {
      $id = (int) $thing->post_author;
    } else if (isset($thing->author)) {
      $id = (int) $thing->author;
    } else {
      $id = (int) $thing->ID;
    }
  } else {
    $id = (int) $thing;
  }

  // $x = 2180 + ($id % 20);
  // $y = 1420 + floor($id / 20);
  // return 'https://stamen-tiles-a.a.ssl.fastly.net/toner-background/12/' . $x . '/' . $y . '@2x.png';

  // higher zoom level
  $x = 17424 + ($id % 20);
  $y = 11365 + floor($id / 20);
  return 'https://stamen-tiles-b.a.ssl.fastly.net/toner-background/15/' . $x . '/' . $y . '@2x.png';
}, 10, 2);
