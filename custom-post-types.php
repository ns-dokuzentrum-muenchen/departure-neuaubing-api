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
        'with_front' => false,
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
        'with_front' => false,
        'slug' => 'int-projekte'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions'),
      // 'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-translation',
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
        'with_front' => false,
        'slug' => 'glossar'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-media-text',
      'menu_position' => 4
    )
  );
  register_post_type('ort',
    array(
      'rest_base' => 'orte',
      'labels' => array(
        'name' => __('Orte'),
        'singular_name' => __('Ort'),
        'add_new_item' => __('Neue Ort')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'with_front' => false,
        'slug' => 'orte'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-location',
      'menu_position' => 4
    )
  );
  register_post_type('person',
    array(
      'rest_base' => 'personen',
      'labels' => array(
        'name' => __('Personen'),
        'singular_name' => __('Person'),
        'add_new_item' => __('Neuer Person')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'with_front' => false,
        'slug' => 'personen'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-media-text',
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
        'with_front' => false,
        'slug' => 'kuenstlerinnen'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-art',
      'menu_position' => 4
    )
  );

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
        'with_front' => false,
        'slug' => 'begriffe'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-media-text',
      'menu_position' => 4
    )
  );

  register_post_type('markierung',
    array(
      'rest_base' => 'markierungen',
      'labels' => array(
        'name' => __('Kartenmarkierung'),
        'singular_name' => __('Markierung'),
        'add_new_item' => __('Neue Markeriung')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'with_front' => false,
        'slug' => 'markierungen'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-location',
      'menu_position' => 4
    )
  );
  register_post_type('upload',
    array(
      'rest_base' => 'uploads',
      'labels' => array(
        'name' => __('Uploads'),
        'singular_name' => __('Beitrag'),
        'add_new_item' => __('')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => false,
      'rewrite' => array(
        'with_front' => false,
        'slug' => 'uploads'
      ),
      'supports' => array('title', 'editor', 'author'),
      'menu_icon' => 'dashicons-cloud-upload',
      'menu_position' => 4
    )
  );

  register_post_type('forum',
    array(
      'rest_base' => 'forum',
      'labels' => array(
        'name' => __('Forum'),
        'singular_name' => __('Thema'),
        'add_new_item' => __('Neues Thema')
      ),
      'show_in_rest' => true,
      'public' => true,
      'has_archive' => true,
      'rewrite' => array(
        'with_front' => false,
        'slug' => 'forum'
      ),
      'supports' => array('title', 'editor', 'author', 'revisions', 'comments', 'thumbnail'),
      'taxonomies' => array('post_tag'),
      'menu_icon' => 'dashicons-format-chat',
      'menu_position' => 4
    )
  );
}

add_action( 'init', 'create_post_types' );
