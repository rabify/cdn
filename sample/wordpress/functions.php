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
define('REPLACE_METHOD', ['get_the_excerpt', 'the_excerpt', 'the_content']);

function rabify_cdn_filter( $the_content ) {
    $preg_site_url = preg_replace(['/(https?):\/\//', '/\./'], ['$1:\/\/', '\.'], site_url());
    $pattern = "/${preg_site_url}(.*?[\.jpe?g|\.png|\.bmp])/i";
    $replace_content = preg_replace($pattern, CDN_URL."$1", $the_content);

    return $replace_content;
}

function rabify_cdn_srcset( $the_content, $sizes = [] )
{
    $preg_cdn_url = preg_replace('/(https?):\/\//', '$1:\/\/', CDN_URL);
    $preg_cdn_url = preg_replace('/\./', '\.', $preg_cdn_url);
    $pattern = "/(<img.*?src\=)[\'|\"](${preg_cdn_url}.*?[\.jpe?g|\.png|\.bmp])\??(v\=\w+)?&?(d\=\w+)?[\'|\"](?!.*srcset.*)/i";

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

    // ソースコードの整理。IMGのタグから外れる可能性あり。
    $replace_content = preg_replace('/(<img.*?src\=.*?)(d\=\w+)(.*?)d\=\w+/', "$1$2$3", $replace_content );

    $preg_arrangement = ['/(<img.*?src\=.*?)[&&?](\'|\")/', '/(<img.*?src\=.*?)&&/', '/(<img.*?src\=.*?)\?&/'];
    $replace_content = preg_replace($preg_arrangement, ['$1$2', '$1&', '$1?'], $replace_content );


    return $replace_content;
}

function rabify_cdn ( $text, $sizes = [] ) {
    $text = rabify_cdn_filter( $text );
    return rabify_cdn_srcset( $text, $sizes );
}

// サイトアドレス（URL）内にある画像URLを、rabify CDNの差し替えます
add_filter( 'the_content', 'rabify_cdn_filter', 1 );

// srcsetの設定されていないimgタグにすべてsrcsetを追加します
add_filter( 'the_content', 'rabify_cdn_srcset', 2 );

// デフォルトのsrcsetを有効化する場合、コメントアウトしてください。
// WordPressのメディアで設定された画像サイズにしたがって、srcsetが設定されます。
add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );