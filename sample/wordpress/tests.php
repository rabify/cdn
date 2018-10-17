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
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg\" width/>[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1\" width />[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.gif\" width />[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=200\" width />[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=200\" width />[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=200\" width />[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618-640x360.jpg?v=1\" width srcset=\"https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=1000 1000w\" />[after]",
    "[before]<a class=\"lkc-link no_icon\" href=\"https://msak-note.com/wp-content/\">[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg\" width height/>[after]",
    "[before]<img src=\"https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.jpg\"/>[after]",
];

$expect = [
    "https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=600",
    "https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=200 200w",
    "https://msak-note.com/wp-content/uploads/2018/06/twitter_top0618.gif",
    "https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=400 400w",
    "https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=400 400w",
    "src=\"https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?v=1&d=200\"",
    "https://rabify.example.com/wp-content/uploads/2018/06/twitter_top0618.jpg?d=1000 1000w",
    "https://msak-note.com/wp-content/",
    "sizes=\"(max-width: 767px)",
    " 1200w\" />[",
];

$comp = [];

for($i = 0; $i < count($img_tags); $i++) {
    $cdn = rabify_cdn( $img_tags[$i] );
    if(strpos($cdn, $expect[$i]) !== false){
        echo "[success] 配列${i}は期待通りに実行されました。 expect: ${expect[$i]}\n" ;
    } else {
        throw new Exception("[fail] 配列${i}は期待通りの値をとりませんでした。\nexpect: ${expect[$i]}\n${cdn}\n");
    }
}

foreach(['localhost', '127.0.0.1'] as $host) {
    if(is_localhost($host)) {
        echo "[success] ローカルホストの判定に成功しました。 expect: ${host}\n" ;
    } else {
        throw new Exception("[fail] ローカルホストの判定に失敗しました\nexpect: ${host}\n");
    }
}
?>