<?php
/*
	Plugin Name: rabify CDN
	Plugin URI: https://www.rabify.me/cdn
	Description: 画像をCDNからホスティングするためのプラグインです。有効にしたあと、設定 => メディアから詳細を設定ください。
	Version: 0.1
	Author: Relation Design Labo, General Inc.Association
	Author URI: https://www.rdlabo.jp/
	License: GPL2
	Text Domain: rabify-cdn
*/
function rabify_cdn_is_localhost($site_url) {
    if(strpos($site_url,'localhost') !== false) {
        return true;
    }
    if(strpos($site_url,'[::1]') !== false) {
        return true;
    }
    if(preg_match('/^127(?:\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/', $site_url)){
        return true;
    }
    if(strpos($_SERVER["REQUEST_URI"], 'wp-admin') !== false) {
        return true;
    }

    return false;
}
function rabify_cdn_filter( $the_content ) {
    if ( ! get_option( 'rabify_is_enabled' ) ) {
        return $the_content;
    }
    $cdn_url = get_option('rabify_domain');
    if ( ! $cdn_url ) {
        return $the_content;
    }
    if(rabify_cdn_is_localhost(site_url())){
        return $the_content;
    }
    $cdn_url = rtrim($cdn_url, "/");
    $preg_site_url = preg_replace(['/(https?):\/\//', '/\./'], ['$1:\/\/', '\.'], site_url());
    $pattern = "/${preg_site_url}([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+\.)(jpe?g|png|bmp)/i";
    $replace_content = preg_replace($pattern, $cdn_url."$1$2", $the_content);
    return $replace_content;
}
function rabify_cdn_srcset( $the_content, $pattern = [], $sizes = '' )
{
    if ( ! get_option( 'rabify_is_enabled' ) ) {
        return $the_content;
    }
    $cdn_url = get_option('rabify_domain');
    if ( ! $cdn_url ) {
        return $the_content;
    }

    if (count($pattern) === 0) {
        $rabify_pattern = get_option('rabify_pattern');
        if (!$rabify_pattern) {
            return $the_content;
        }

        $pattern = array_map('trim', explode(',', $rabify_pattern));
    }

    $preg_cdn_url = preg_replace('/(https?):\/\//', '$1:\/\/', $cdn_url);
    $preg_cdn_url = preg_replace('/\./', '\.', $preg_cdn_url);

    // content_width
    global $content_width;
    if (!$content_width) {
        $content_width = 600;
    }
    $reg = "/(<img.*?src\=)[\'\"](${preg_cdn_url}.*?)(jpe?g|png|bmp)(?!.*d\=\w+)[\'\"]/i";
    $replace_content = preg_replace($reg, "$1\"$2$3&d=$content_width\"", $the_content);

    // srcset
    $reg = "/(<img.*?src\=)[\'\"](${preg_cdn_url}.*?)(jpe?g|png|bmp|gif)\??(v\=\w+)?&?(d\=\w+)?[\'\"](?!.*srcset.*>)/i";
    $srcset = "srcset=\"";

    foreach($pattern as $size) {
        $srcset .= "$2$3?$4&d=${size} ${size}w, ";
    }
    $srcset = rtrim($srcset, ', ') . "\" ";
    $replace_content = preg_replace($reg, "$1\"$2$3?$4&$5\" ${srcset}", $replace_content);

    $reg = "/(<img.*?src\=)[\'\"](${preg_cdn_url}.*?)[\'\"](?=.*srcset)(?=.*width)(?=.*height)(?!.*sizes)/i";
    $replace_content = preg_replace($reg, "$1\"$2\"$3$4$5$6 $sizes", $replace_content);

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
function rabify_cdn_srcset_content( $the_content ) {
    if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
    return rabify_cdn_srcset( $the_content, $pattern = [], get_option( 'rabify_size_sizes_content' ) );
}
function rabify_cdn_srcset_excerpt( $the_content ) {
    if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
    return rabify_cdn_srcset( $the_content, $pattern = [], get_option( 'rabify_size_sizes_excerpt' ) );
}
function rabify_cdn_srcset_header( $the_content ) {
    if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
    return rabify_cdn_srcset( $the_content, $pattern = [], get_option( 'rabify_size_sizes_header' ) );
}
function rabify_cdn_srcset_thumbnail( $the_content ) {
    if ( ! get_option( 'rabify_is_enabled' ) ) return $the_content;
    return rabify_cdn_srcset( $the_content, $pattern = [], get_option( 'rabify_size_sizes_thumbnail' ) );
}

add_filter( 'the_content', 'rabify_cdn_filter', 1 );
add_filter( 'the_content', 'rabify_cdn_srcset_content', 2 );

add_filter( 'the_excerpt', 'rabify_cdn_filter', 1 );
add_filter( 'the_excerpt', 'rabify_cdn_srcset_excerpt', 2 );

add_filter( 'custom-header', 'rabify_cdn_filter', 1 );
add_filter( 'custom-header', 'rabify_cdn_srcset_header', 2 );

add_filter( 'post_thumbnail_html', 'rabify_cdn_filter', 1 );
add_filter( 'post_thumbnail_html', 'rabify_cdn_srcset_thumbnail', 2 );

add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );


function rabify_cdn_call_back($buffer) {
    return rabify_cdn($buffer, []);
}
function rabify_cdn_buf_start() {
    if ( ! get_option( 'rabify_force' ) ) {
        return null;
    }
    ob_start("rabify_cdn_call_back");
}
function rabify_cdn_buf_end() {
    if ( ! get_option( 'rabify_force' ) ) {
        return null;
    }
    ob_end_flush();
}

add_action('after_setup_theme', 'rabify_cdn_buf_start');
add_action('shutdown', 'rabify_cdn_buf_end');

// ------------------------------------------------------------------
// admin_init の中で設定のセクションとフィールドを追加
// ------------------------------------------------------------------
//

function eg_settings_api_init() {
    add_settings_section(
        'eg_setting_section',
        'rabify CDN',
        'eg_setting_section_callback_function',
        'media'
    );
    add_settings_field(
        'rabify_is_enabled',
        'Enable',
        'eg_setting_callback_enabled',
        'media',
        'eg_setting_section'
    );
    add_settings_field(
        'rabify_domain',
        'rabify CDN URL',
        'eg_setting_callback_domain',
        'media',
        'eg_setting_section'
    );
    add_settings_field(
        'rabify_pattern',
        '画像サイズ',
        'eg_setting_callback_pattern',
        'media',
        'eg_setting_section'
    );
    add_settings_field(
        'rabify_sizes',
        'img sizes（任意入力）',
        'eg_setting_callback_sizes',
        'media',
        'eg_setting_section'
    );
    add_settings_field(
        'rabify_force',
        '強制適用',
        'eg_setting_callback_force',
        'media',
        'eg_setting_section'
    );

    register_setting( 'media', 'rabify_is_enabled' );
    register_setting( 'media', 'rabify_domain' );
    register_setting( 'media', 'rabify_pattern' );
    register_setting( 'media', 'rabify_size_sizes_content' );
    register_setting( 'media', 'rabify_size_sizes_excerpt' );
    register_setting( 'media', 'rabify_size_sizes_header' );
    register_setting( 'media', 'rabify_size_sizes_thumbnail' );
    register_setting( 'media', 'rabify_force' );
}

add_action( 'admin_init', 'eg_settings_api_init' );


function eg_setting_section_callback_function() {
    echo '<p>WordPressで利用する画像のドメインをCDNに差し替えます。ローカル環境では動作しませんのでご注意ください。</p>';
}

function eg_setting_callback_enabled() {
    echo '<label><input name="rabify_is_enabled" id="rabify_is_enabled" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'rabify_is_enabled' ), false ) . ' /> rabify CDNを有効化する</label>';
}

function eg_setting_callback_domain() {
    echo '<input name="rabify_domain" id="rabify_domain" type="url" value="'. get_option( 'rabify_domain' ). '" class="regular-text code"  placeholder="https://example.rabify.me" />';
}

function eg_setting_callback_pattern() {
    echo '<input name="rabify_pattern" id="rabify_pattern" type="text" pattern="[0-9, ].+" value="'. get_option( 'rabify_pattern' ). '" class="regular-text code"  placeholder="150, 200, 400, 600, 800, 1000, 1200" />';
}

function eg_setting_callback_sizes() {
    echo '<fieldset>
    <label for="rabify_size_sizes_content">the_content</label>
    <input name="rabify_size_sizes_content" id="rabify_size_sizes_content" type="text" pattern="sizes=.*" value=\''. get_option( 'rabify_size_sizes_content' ). '\' class="code"  placeholder="sizes=&ldquo;&ldquo;" /><br />
    <label for="rabify_size_sizes_excerpt">the_excerpt</label>
    <input name="rabify_size_sizes_excerpt" id="rabify_size_sizes_excerpt" type="text" pattern="sizes=.*" value=\''. get_option( 'rabify_size_sizes_excerpt' ). '\' class="code"  placeholder="sizes=&ldquo;&ldquo;" /><br />
    <label for="rabify_size_sizes_header">custom-header</label>
    <input name="rabify_size_sizes_header" id="rabify_size_sizes_header" type="text" pattern="sizes=.*" value=\''. get_option( 'rabify_size_sizes_header' ). '\' class="code"  placeholder="sizes=&ldquo;&ldquo;" /><br />
    <label for="rabify_size_sizes_thumbnail">post_thumbnail_html</label>
    <input name="rabify_size_sizes_thumbnail" id="rabify_size_sizes_thumbnail" type="text" pattern="sizes=.*" value=\''. get_option( 'rabify_size_sizes_thumbnail' ). '\' class="code"  placeholder="sizes=&ldquo;&ldquo;" /><br />
    </fieldset>';
}

function eg_setting_callback_force() {
    echo '<label><input name="rabify_force" id="rabify_force" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'rabify_force' ), false ) . ' /> rabify CDNをサイト全体に適用</label>';
}