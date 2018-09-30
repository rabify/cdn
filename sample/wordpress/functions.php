<?php

/**
 * ----------------------------------------------------------------------
 *
 *  rabify CDN
 *
 *  既存の画像URLを、rabify CDNに差し替えます。
 *  利用するためには、まずCDN_URLをrabify CDNから提供されたURLに置き換えてください。
 *  なお、CDN_SIZEは縮小サイズの横幅のパターンを指定ください。
 *
 *  Theme内で直接画像を差し替える時は rabify_cdn('<img src~') もご利用いただけます。
 *
 * ----------------------------------------------------------------------
 */

define('CDN_URL','https://rabify.example.com');
define('CDN_SIZE', [100, 200, 300, 400]);
define('SRC_SIZE', 'sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px"');
define('REPLACE_METHOD', ['get_the_excerpt', 'the_excerpt', 'the_content', 'post_thumbnail_html']);

function is_localhost($site_url) {
    if(strpos($site_url,'localhost') !== false) {
        return true;
    }

    if(strpos($site_url,'[::1]') !== false) {
        return true;
    }

    if(preg_match('/^127(?:\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/', $site_url)){
        return true;
    }

    return false;
}

function rabify_cdn_filter( $the_content ) {
    if(is_localhost(site_url())){
        return $the_content;
    }

    $preg_site_url = preg_replace(['/(https?):\/\//', '/\./'], ['$1:\/\/', '\.'], site_url());
    $pattern = "/${preg_site_url}(.*?[\.jpe?g|\.png|\.bmp])/i";
    $replace_content = preg_replace($pattern, CDN_URL."$1", $the_content);

    return $replace_content;
}

function rabify_cdn_srcset( $the_content, $sizes = [] )
{
    $preg_cdn_url = preg_replace('/(https?):\/\//', '$1:\/\/', CDN_URL);
    $preg_cdn_url = preg_replace('/\./', '\.', $preg_cdn_url);
    $pattern = "/(<img.*?src\=)[\'|\"](${preg_cdn_url}.*?[\.jpe?g|\.png|\.bmp])\??(v\=\w+)?&?(d\=\w+)?[\'|\"](?!.*srcset.*>)/i";

    $srcset = "srcset=\"";

    if(count($sizes) === 0) {
        $sizes = CDN_SIZE;
    }

    foreach($sizes as $size) {
        $srcset .= "$2?$3&d=${size} ${size}w, ";
    }
    $srcset = rtrim($srcset, ', ') . "\" " . SRC_SIZE;
    global $content_width;
    $replace_content = preg_replace($pattern, "$1\"$2?$3&$4&d=$content_width\" ${srcset} $5", $the_content);

    $replace_content = preg_replace('/(<img.*?src\=[\'|\"].*?)(\?d\=\w+)(.*?)d\=\w+(.*?[\'|\"])/', "$1$2$3$4", $replace_content );
    $replace_content = preg_replace('/([\.jpe?g|\.png|\.bmp])\?&/', '$1?', $replace_content );


    $preg_arrangement = ['/(<img.*?src\=.*?)&&(.*?>)/', '/(<img.*?src\=.*?)[&&?](\'|\")(.*?>)/'];
    $replace_content = preg_replace($preg_arrangement, ['$1&$2', '$1$2$3'], $replace_content );


    return $replace_content;
}

function rabify_cdn ( $text, $sizes = [] ) {
    $text = rabify_cdn_filter( $text );
    return rabify_cdn_srcset( $text, $sizes );
}

for($i = 0; count(REPLACE_METHOD) > $i; ++$i) {
    add_filter( REPLACE_METHOD[$i], 'rabify_cdn_filter', $i);
    add_filter( REPLACE_METHOD[$i], 'rabify_cdn_srcset', $i + 1);
}

// デフォルトのsrcsetを有効化する場合、コメントアウトしてください。
// WordPressのメディアで設定された画像サイズにしたがって、srcsetが設定されます。
add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );