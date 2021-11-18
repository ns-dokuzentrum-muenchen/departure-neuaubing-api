<?php
//
function sync_connections ($ok, $post_id) {
  $connections = get_field('connections', $post_id, false);

  if ($connections) {
    foreach ($connections as $str) {
      array_push($ok, (int)$str);
    }
  }

  if (count($ok) > 0) {
    update_field('connections', array_unique($ok), $post_id);

    // $fp = fopen('/Users/nics/Sites/ns-doku/api/custom-log.txt', 'a');
    // fwrite($fp, date(DATE_ISO8601) . ' - ' . $post_id . ' updated ' . $done . "\n");
    // fwrite($fp, date(DATE_ISO8601) . ' - ' . json_encode(array_unique($ok)) . "\n");
    // fclose($fp);
  }

  return;
}

function dn_get_ids_from_links ($value) {
  preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $value, $matches);

  $ok = [];
  foreach ($matches[0] as $url) {
    if (preg_match('/ns-doku\.test|localhost\:8080|doku\.n-kort\.net/', $url)) {
      $id = url_to_postid($url);

      array_push($ok, $id);
    }
  }

  return $ok;
}

// on save event
function my_acf_save_post($post_id) {
  // get all acf fields
  $values = get_fields($post_id, false);

  // rich fields, outside of content[]
  $toParse = [];
  if (isset($values['description'])) {
    array_push($toParse, $values['description']);
  }

  // all content rows, as string
  foreach ($values['content'] as $block) {
    array_push($toParse, json_encode($block, JSON_UNESCAPED_SLASHES)); // so regex works
  }

  $linked_ids = [];

  // get ids from text
  foreach ($toParse as $val) {
    $linked_ids = array_merge($linked_ids, dn_get_ids_from_links($val, $post_id));
  }

  // do the save
  sync_connections($linked_ids, $post_id);
}
add_action('acf/save_post', 'my_acf_save_post'); // after save

// output modification (perhaps)
function acf_format_textarea ($value) {
  preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $value, $matches);

  // print_r($matches);

  foreach ($matches[0] as $url) {
    preg_match_all('/http:\/\/ns-doku\.test\/|http:\/\/localhost\:8080\/|https:\/\/doku\.n-kort\.net\//', $url, $hit);
    if ($hit) {
      $new_url = str_replace($hit[0][0], '/', $url);
      $value = str_replace($url, $new_url, $value);
    }
  }

  // echo $value . "\n";

  return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'acf_format_textarea', 10, 3);
