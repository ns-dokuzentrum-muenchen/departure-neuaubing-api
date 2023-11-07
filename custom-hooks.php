<?php
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
  // return 'https://stamen-tiles-b.a.ssl.fastly.net/toner-background/15/' . $x . '/' . $y . '@2x.png';
  return 'https://tiles.stadiamaps.com/tiles/stamen-toner/15/' . $x . '/' . $y . '@2x.png?idx=' . $thing;
}, 10, 2);
