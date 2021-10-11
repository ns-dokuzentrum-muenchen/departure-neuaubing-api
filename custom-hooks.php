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
