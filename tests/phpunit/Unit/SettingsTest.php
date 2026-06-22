<?php
/**
 * NLVT_Settings の Unit テスト。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

namespace NLVT\Tests\Unit;

use Brain\Monkey\Functions;
use NLVT_Settings;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers \NLVT_Settings
 */
class SettingsTest extends TestCase {

	/**
	 * 入力値が無い場合は最小値 1 にフォールバックする。
	 */
	public function test_sanitize_settings_defaults_to_one_when_missing() {
		$settings = new NLVT_Settings();
		$result   = $settings->sanitize_settings( array() );

		$this->assertSame( array( 'default_threshold' => 1 ), $result );
	}

	/**
	 * 1 未満の値は最小値 1 に丸められる。
	 */
	public function test_sanitize_settings_enforces_minimum() {
		$settings = new NLVT_Settings();

		$this->assertSame( 1, $settings->sanitize_settings( array( 'default_threshold' => 0 ) )['default_threshold'] );
		$this->assertSame( 1, $settings->sanitize_settings( array( 'default_threshold' => -5 ) )['default_threshold'] );
	}

	/**
	 * 数値以外の入力は整数にキャストされる。
	 */
	public function test_sanitize_settings_casts_to_int() {
		$settings = new NLVT_Settings();

		$this->assertSame( 7, $settings->sanitize_settings( array( 'default_threshold' => '7abc' ) )['default_threshold'] );
		$this->assertSame( 3, $settings->sanitize_settings( array( 'default_threshold' => 3.9 ) )['default_threshold'] );
	}

	/**
	 * get_default_threshold は保存済みオプション値を返す。
	 */
	public function test_get_default_threshold_returns_stored_value() {
		Functions\when( 'get_option' )->justReturn( array( 'default_threshold' => 5 ) );

		$this->assertSame( 5, NLVT_Settings::get_default_threshold() );
	}

	/**
	 * オプションが未設定なら 1 を返す。
	 */
	public function test_get_default_threshold_falls_back_to_one() {
		Functions\when( 'get_option' )->returnArg( 2 );

		$this->assertSame( 1, NLVT_Settings::get_default_threshold() );
	}
}
