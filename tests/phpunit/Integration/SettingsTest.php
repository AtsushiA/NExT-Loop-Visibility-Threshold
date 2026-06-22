<?php
/**
 * 設定の Integration テスト。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

namespace NLVT\Tests\Integration;

use NLVT_Settings;
use WP_UnitTestCase;

/**
 * @covers \NLVT_Settings
 */
class SettingsTest extends WP_UnitTestCase {

	/**
	 * オプション未設定時のデフォルト閾値は 1。
	 */
	public function test_default_threshold_is_one_by_default() {
		delete_option( NLVT_Settings::OPTION_NAME );

		$this->assertSame( 1, NLVT_Settings::get_default_threshold() );
	}

	/**
	 * 保存済みオプション値が反映される。
	 */
	public function test_default_threshold_reflects_saved_option() {
		update_option( NLVT_Settings::OPTION_NAME, array( 'default_threshold' => 4 ) );

		$this->assertSame( 4, NLVT_Settings::get_default_threshold() );
	}

	/**
	 * sanitize_settings は最小値 1 を強制する。
	 */
	public function test_sanitize_enforces_minimum_via_register_setting() {
		$settings = new NLVT_Settings();
		$result   = $settings->sanitize_settings( array( 'default_threshold' => 0 ) );

		$this->assertSame( 1, $result['default_threshold'] );
	}
}
