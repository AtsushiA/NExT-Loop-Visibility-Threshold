<?php
/**
 * PHPUnit bootstrap file for Unit tests.
 *
 * WordPress をロードせず、Brain\Monkey でモックする軽量 bootstrap。
 * phpunit.xml.dist のデフォルト bootstrap として使用する。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

$nlvt_plugin_root = dirname( __DIR__, 3 );

require_once $nlvt_plugin_root . '/vendor/autoload.php';

// class ファイル先頭の ABSPATH ガードを通すためのダミー定義.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $nlvt_plugin_root . '/' );
}

// テスト対象クラスを読み込む（PSR-4 ではないため手動 require）.
require_once $nlvt_plugin_root . '/includes/class-nlvt-settings.php';
require_once $nlvt_plugin_root . '/includes/class-nlvt-block.php';
