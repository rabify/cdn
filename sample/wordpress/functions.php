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
define('CDN_URL','https://rabify.example.com');  // 最後のスラッシュはつけないようにしてください
define('CDN_PATTERN', [150, 200, 400, 600, 800, 1000, 1200]); // 画像サイズのパターン

/**
 * imgタグのsizes属性（https://developer.mozilla.org/ja/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images）
 * 設定すると、より高速化に寄与します。 https://www.rabify.me/ でジェネレーターあります。
 */
define('SRC_SIZE', ''); // サムネイル以外のsizes
define('SRC_SIZE_THUMBNAIL', ''); // サムネイルのsizes

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
    $pattern = "/${preg_site_url}([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+\.[jpe?g|png|bmp])/i";
    $replace_content = preg_replace($pattern, CDN_URL."$1", $the_content);
    return $replace_content;
}
function rabify_cdn_srcset( $the_content, $pattern = [], $sizes = '' )
{
    if (count($pattern) === 0) {
        $pattern = CDN_PATTERN;
    }

    if (!$sizes) {
        $sizes = SRC_SIZE;
    }

    $preg_cdn_url = preg_replace('/(https?):\/\//', '$1:\/\/', CDN_URL);
    $preg_cdn_url = preg_replace('/\./', '\.', $preg_cdn_url);
    $reg = "/(<img.*?src\=)[\'\"](${preg_cdn_url}.*?[\.jpe?g|\.png|\.bmp])\??(v\=\w+)?&?(d\=\w+)?[\'\"](?!.*srcset.*>)/i";
    $srcset = "srcset=\"";

    foreach($pattern as $size) {
        $srcset .= "$2?$3&d=${size} ${size}w, ";
    }
    $srcset = rtrim($srcset, ', ') . "\" " . $sizes;
    global $content_width;
    if (!$content_width) {
        $content_width = 600;
    }

    $replace_content = preg_replace($reg, "$1\"$2?$3&$4&d=$content_width\" ${srcset} $5", $the_content);
    $replace_content = preg_replace('/(<img.*?src\=[\'\"].*?)(\?d\=\w+)(.*?)d\=\w+(.*?[\'\"])/', "$1$2$3$4", $replace_content );
    $replace_content = preg_replace('/([\.jpe?g|\.png|\.bmp])\?&/', '$1?', $replace_content );
    $preg_arrangement = ['/(<img.*?src\=.*?)&&(.*?>)/', '/(<img.*?src\=.*?)[&&?](\'|\")(.*?>)/'];
    $replace_content = preg_replace($preg_arrangement, ['$1&$2', '$1$2$3'], $replace_content );
    return $replace_content;
}
function rabify_cdn ( $text, $pattern = [] ) {
    $text = rabify_cdn_filter( $text );
    return rabify_cdn_srcset( $text, $pattern );
}

function rabify_cdn_srcset_thumbnail( $the_content ) {
    return rabify_cdn_srcset( $the_content, $pattern = [], SRC_SIZE_THUMBNAIL );
}

/**
 * the_content()で表示する画像を、rabify CDNの差し替えます
 */
add_filter( 'the_content', 'rabify_cdn_filter', 1 );
add_filter( 'the_content', 'rabify_cdn_srcset', 2 );
/**
 * the_excerpt()で表示する画像を、rabify CDNの差し替えます
 */
add_filter( 'the_excerpt', 'rabify_cdn_filter', 1 );
add_filter( 'the_excerpt', 'rabify_cdn_srcset', 2 );
/**
 * カスタムヘッダーの画像を、rabify CDNの差し替えます
 */
add_filter( 'custom-header', 'rabify_cdn_filter', 1 );
add_filter( 'custom-header', 'rabify_cdn_srcset', 2 );
/**
 * アイキャッチ画像で表示する画像を、rabify CDNの差し替えます
 */
add_filter( 'post_thumbnail_html', 'rabify_cdn_filter', 1 );
add_filter( 'post_thumbnail_html', 'rabify_cdn_srcset_thumbnail', 2 );
/**
 * デフォルトのsrcsetを有効化する場合、コメントアウトしてください。
 * WordPressのメディアで設定された画像サイズにしたがって、srcsetが設定されます。
 */
add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );