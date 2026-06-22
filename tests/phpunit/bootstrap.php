<?php
/**
 * PHPUnit bootstrap file for Integration tests.
 *
 * WordPress テスト環境をロードする。wp-env / CI 上で `--bootstrap` を指定して使う。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

// Composer autoloader.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// WordPress テストライブラリの読み込み.
require_once $_tests_dir . '/includes/functions.php';

/**
 * テスト対象プラグインを手動で読み込む.
 */
function _nlvt_manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/next-loop-visibility-threshold.php';
}
tests_add_filter( 'muplugins_loaded', '_nlvt_manually_load_plugin' );

// WordPress テスト環境を起動.
require $_tests_dir . '/includes/bootstrap.php';
