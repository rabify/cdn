# cdn
「画像がなかなか表示されない」を改善。

## Sample

### WordPress 
__functions.php__
```PHP
$ php sample/wordpress/tests.php && php sample/wordpress/tests_linkcard.php
```

__plugin__
```PHP
$ php sample/wordpress/plugins/tests/tests.php 
```

## Release
__plugin__
```PHP
$ cd sample/wordpress
$ cp -r plugins/ rabicy_cdn/ && zip -r rabify_cdn.zip rabicy_cdn/ && rm -rf rabicy_cdn/
```