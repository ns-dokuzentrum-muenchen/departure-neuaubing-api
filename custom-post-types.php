<?php
function create_post_types () {
  register_post_type('projekt',
    array(
      'rest_base' => 'projekte',
      'labels' => array(
        'name' => __('Projekte'),
        'singular_name' => __('Projekt'),
        'add_new_item' => __('Neues Projekt')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'slug' => 'projekt'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'excerpt'),
      'menu_icon' => 'dashicons-layout',
      'menu_position' => 4
    )
  );

  register_post_type('glossar',
    array(
      'labels' => array(
        'name' => __('Glossar'),
        'singular_name' => __('Glossar'),
        'add_new_item' => __('Neuer Glossar-Begriff')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'slug' => 'glossar'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'excerpt'),
      'menu_icon' => 'dashicons-media-text',
      'menu_position' => 4
    )
  );

}

add_action( 'init', 'create_post_types' );
