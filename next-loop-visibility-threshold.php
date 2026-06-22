<?php
/**
 * Plugin Name: NExT Loop Visibility Threshold
 * Plugin URI:  https://github.com/
 * Description: Query Loop ブロック内で投稿数が指定の閾値以上の場合にインナーブロックを表示します。
 * Version:     1.1.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author:      NExT-Season
 * Author URI: https://next-season.net
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: next-loop-visibility-threshold
 * Domain Path: /languages
 *
 * @package NExT_Loop_Visibility_Threshold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NLVT_VERSION', '1.1.0' );
define( 'NLVT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NLVT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once NLVT_PLUGIN_DIR . 'includes/class-nlvt-settings.php';
require_once NLVT_PLUGIN_DIR . 'includes/class-nlvt-block.php';

/**
 * プラグインの初期化
 */
function nlvt_init() {
	$settings = new NLVT_Settings();
	$settings->init();

	$block = new NLVT_Block();
	$block->init();
}
add_action( 'plugins_loaded', 'nlvt_init' );
