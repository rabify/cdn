<?php
/**
 * php sample/wordpress/tests.php
 */

function add_filter($function, $method, $options = null) {
    return null;
}

function site_url() {
    return 'https://msak-note.com';
}

$content_width = 600;

require_once __DIR__ . '/functions.php';

$img_tags = [
    "<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg\" />",
    "<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1\" />",
    "<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=200\" />",
    "<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=200\" />",
    "<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618-640x360.jpg\" alt=\"\" width=\"640\" height=\"360\" />"
];

$comp = [];
foreach($img_tags as $tag) {
    $comp[] = rabify_cdn( $tag );
}

print_r($comp);