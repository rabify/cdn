<?php
/**
 * php sample/wordpress/tests.php
 */

function add_filter($function, $method, $options = null) {
    return null;
}

function site_url() {
    return 'https://technical-creator.com';
}

$content_width = 600;

require_once __DIR__ . '/functions.php';

$html_string = '
<p>さらに皆さんお忘れかも知れないですが、InVision本体のリニューアルの話もあったんですよ！<br />
<div class="linkcard"><div class="lkc-internal-wrap"><a class="lkc-link no_icon" href="https://technical-creator.com/invision-v7/"><div class="lkc-card"><div class="lkc-info"><span class="lkc-domain"><img class="lkc-favicon" src="https://www.google.com/s2/favicons?domain=technical-creator.com" alt="" width=16 height=16 />&nbsp;TECHNICAL CREATOR</span></div><div class="lkc-content"><span class="lkc-thumbnail"><img class="lkc-thumbnail-img" src="https://technical-creator.com/wp-content/uploads/2017/12/1_Ah8FQPwtpsv9PrS9Yg1uDA-120x120.png" alt="" /></span><div class="lkc-title"><span class="lkc-title-text">その名は「V7」、InVisionが生まれ変わって超高速化！さらに新機能「スペース」の...</span></div><div class="lkc-url"><cite>https://technical-creator.com/invision-v7/</cite></div><div class="lkc-excerpt">10月に新製品「InVision Studio」と「InVision DSM」、そして大型の資金調達を発表したInVisionですが、まだまだ隠し玉を持っていたようです…！2018年、InVisionが大きなバージョンアップを行い、高速化＆新機能を伴ってリリースされます。新バージョンのコードネー...</div></div><div class="clear"></div></div></a></div></div></p>
<p>あと買収したBrand.aiのリニューアルはどうなってるんですか！<br />
<div class="linkcard"><div class="lkc-internal-wrap"><a class="lkc-link no_icon" href="https://technical-creator.com/invision-design-system-manager/"><div class="lkc-card"><div class="lkc-info"><span class="lkc-domain"><img class="lkc-favicon" src="https://www.google.com/s2/favicons?domain=technical-creator.com" alt="" width=16 height=16 />&nbsp;TECHNICAL CREATOR</span></div><div class="lkc-content"><span class="lkc-thumbnail"><img class="lkc-thumbnail-img" src="https://technical-creator.com/wp-content/uploads/2017/10/Image-1-120x120.png" alt="" /></span><div class="lkc-title"><span class="lkc-title-text">InVisionがBrand.aiを買収、デザイン管理ツール「InVision Design System Manager...</span></div><div class="lkc-url"><cite>https://technical-creator.com/invision-design-system-manager/</cite></div><div class="lkc-excerpt">寝ようとしていたらまたInVision先輩が…。今度はなんですか？え？買収？また買収したの？InVisionがBrand.aiというデザイン管理ツールを買収したことを発表しました。さらに、Brand.aiを元にInVision Design System Manger（DSM）を開発中であることを発表。今年の12...</div></div><div class="clear"></div></div></a></div></div></p>
<p>1億ドルの資金調達から間もなく1年ですが、ぼちぼちたくさんのユーザーの期待に答えて欲しいところ。来月はAdobe MAXですし、そろそろぶちかましてくれるんですかね？楽しみです。</p>
<ul class="social_button">
';

$replace = rabify_cdn( $html_string );

$expect = [
    "href=\"https://technical-creator.com/invision-v7/\"",
    "https://rabify.example.com/wp-content/uploads/2017/12/1_Ah8FQPwtpsv9PrS9Yg1uDA-120x120.png?d=150 150w",
    "href=\"https://technical-creator.com/invision-design-system-manager/\">",
    "さらに皆さんお忘れかも知れないですが",
    "そろそろぶちかましてくれるんですかね？楽しみです。"
];


for($i = 0; $i < count($expect); $i++) {
    $cdn = rabify_cdn( $html_string );
    if(strpos($cdn,$expect[$i]) !== false){
        echo "[success] 配列${i}は期待通りに実行されました。 expect: ${expect[$i]}\n" ;
    } else {
        throw new Exception("[fail] 配列${i}は期待通りの値をとりませんでした。\nexpect: ${expect[$i]}\n${cdn}\n");
    }
}

?>