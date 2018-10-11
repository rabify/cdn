<?php
/**
 * @package Rabify_CDN
 * @version 0.1
 */
/*
Plugin Name: Rabify CDN
Plugin URI: https://github.com/rabify/cdn
Description: Replace img tag attributes to rabify cdn url
Author: rdlabo
Version: 0.1
Text Domain: rabify-cdn
*/
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
	if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
    if(is_localhost(site_url())){
        return $the_content;
    }
	$cdn_url = get_option('rabify_domain');
	if ( ! $cdn_url ) return $the_content;
    $preg_site_url = preg_replace(['/(https?):\/\//', '/\./'], ['$1:\/\/', '\.'], site_url());
    $pattern = "/${preg_site_url}([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+\.)(jpe?g|png|bmp)/i";
    $replace_content = preg_replace($pattern, $cdn_url."$1$2", $the_content);
    return $replace_content;
}
function rabify_cdn_srcset( $the_content, $pattern = [], $sizes = '' )
{
	if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
	$cdn_url = get_option('rabify_domain');
	if ( ! $cdn_url ) return $the_content;
    if (count($pattern) === 0) {
        $pattern = CDN_PATTERN;
    }
    if (!$sizes) {
        $sizes = SRC_SIZE;
    }
    $preg_cdn_url = preg_replace('/(https?):\/\//', '$1:\/\/', $cdn_url);
    $preg_cdn_url = preg_replace('/\./', '\.', $preg_cdn_url);
    $reg = "/(<img.*?src\=)[\'\"](${preg_cdn_url}[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+$,%#]+\.)(jpe?g|png|bmp)\??(v\=\w+)?&?(d\=\w+)?[\'\"](?!.*srcset.*>)/i";
    $srcset = "srcset=\"";
    foreach($pattern as $size) {
        $srcset .= "$2$3?$4&d=${size} ${size}w, ";
    }
    $srcset = rtrim($srcset, ', ') . "\" " . $sizes;
    global $content_width;
    if (!$content_width) {
        $content_width = 600;
    }
    $replace_content = preg_replace($reg, "$1\"$2$3?$4&$5&d=$content_width\" ${srcset} $5", $the_content);
    $replace_content = preg_replace('/(<img.*?src\=[\'\"].*?)(\?d\=\w+)(.*?)d\=\w+(.*?[\'\"])/', "$1$2$3$4", $replace_content );
    $replace_content = preg_replace('/([\.jpe?g|\.png|\.bmp])\?&/', '$1?', $replace_content );
    $preg_arrangement = ['/(<img.*?src\=.*?)&&(.*?>)/', '/(<img.*?src\=.*?)[&&?](\'|\")(.*?>)/'];
    $replace_content = preg_replace($preg_arrangement, ['$1&$2', '$1$2$3'], $replace_content );
    return $replace_content;
}
function rabify_cdn ( $text, $pattern = [] ) {
	if ( ! get_option( 'rabify_is_enabled' ) ) return $text;
    $text = rabify_cdn_filter( $text );
    return rabify_cdn_srcset( $text, $pattern );
}
function rabify_cdn_srcset_thumbnail( $the_content ) {
	if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
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

// ------------------------------------------------------------------
 // admin_init の中で設定のセクションとフィールドを追加
 // ------------------------------------------------------------------
 //
 
 function eg_settings_api_init() {
 	// reading 設定ページへフィールドを追加する準備として
 	// セクションを追加
 	add_settings_section(
		'eg_setting_section',
		'Rabify CDN',
		'eg_setting_section_callback_function',
		'media'
	);
 	
 	// その新しいセクションの中に
 	// 新しい設定の名前と関数を指定しながらフィールドを追加
 	add_settings_field(
		'rabify_is_enabled',
		'Enable Rabify CDN',
		'eg_setting_callback_function',
		'media',
		'eg_setting_section'
	);
	 add_settings_field(
		'rabify_domain',
		'Rabify CDN URL',
		'eg_setting_callback_function1',
		'media',
		'eg_setting_section'
	);
 	
 	// 新しい設定が $_POST で扱われ、コールバック関数が <input> を
 	// echo できるように、新しい設定を登録
 	register_setting( 'media', 'rabify_is_enabled' );
 	register_setting( 'media', 'rabify_domain' );
 } // eg_settings_api_init()
 
 add_action( 'admin_init', 'eg_settings_api_init' );
 
  
 // ------------------------------------------------------------------
 // セクションのコールバック関数
 // ------------------------------------------------------------------
 //
 // 新規セクションを追加するために必要となる関数。
 // セクションのはじめに実行される。
 //
 
 function eg_setting_section_callback_function() {
 	echo '<p>設定セクションを説明する文章</p>';
 }
 
 // ------------------------------------------------------------------
 // 設定の例のためのコールバック関数
 // ------------------------------------------------------------------

 function eg_setting_callback_function1() {
 	echo '<input name="rabify_domain" id="rabify_domain" type="text" value="'. get_option( 'rabify_domain' ). '" class="code"  placeholder="https://example.rabify.me" /> *最後のスラッシュはつけないようにしてください';
 }
 function eg_setting_callback_function() {
 	echo '<label><input name="rabify_is_enabled" id="rabify_is_enabled" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'rabify_is_enabled' ), false ) . ' /> CDNを有効化する</label>';
 }
