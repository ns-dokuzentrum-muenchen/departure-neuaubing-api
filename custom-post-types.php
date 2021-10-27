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
        'slug' => 'projekte'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-layout',
      'menu_position' => 4
    )
  );
  register_post_type('int-projekt',
    array(
      'rest_base' => 'int-projekte',
      'labels' => array(
        'name' => __('Projekte (Version)'),
        'singular_name' => __('Projekt'),
        'add_new_item' => __('Neues Projekt')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => false,
      'rewrite' => array(
        'slug' => 'int-projekte'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions'),
      // 'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-translation',
      'menu_position' => 4
    )
  );

  register_post_type('kuenstler',
    array(
      'rest_base' => 'kuenstlerinnen',
      'labels' => array(
        'name' => __('Künstler*innen')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'slug' => 'kuenstlerinnen'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-art',
      'menu_position' => 4
    )
  );

  // register_post_type('person',
  //   array(
  //     'rest_base' => 'personen',
  //     'labels' => array(
  //       'name' => __('Personen'),
  //       'singular_name' => __('Person'),
  //       'add_new_item' => __('Neue Person')
  //     ),
  //     'show_in_rest' => true,
  //     'public' => true,
  //     'has_archive' => true,
  //     'rewrite' => array(
  //       'slug' => 'personen'
  //     ),
  //     'supports' => array('title', 'editor', 'author', 'revisions'),
  //     'menu_icon' => 'dashicons-admin-users',
  //     'taxonomies' => array('post_tag'),
  //     'menu_position' => 4
  //   )
  // );
  register_post_type('begriff',
    array(
      'rest_base' => 'begriffe',
      'labels' => array(
        'name' => __('Begriffe'),
        'singular_name' => __('Begriff'),
        'add_new_item' => __('Neuer Begriff')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'slug' => 'begriffe'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('category', 'post_tag'),
      'menu_icon' => 'dashicons-media-text',
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
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('category', 'post_tag'),
      'menu_icon' => 'dashicons-media-text',
      'menu_position' => 4
    )
  );

  register_post_type('markierung',
    array(
      'labels' => array(
        'name' => __('Kartenmarkierung'),
        'singular_name' => __('Markierung'),
        'add_new_item' => __('Neue Markeriung')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'slug' => 'markierungen'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('category', 'post_tag'),
      'menu_icon' => 'dashicons-location',
      'menu_position' => 4
    )
  );
}

add_action( 'init', 'create_post_types' );
