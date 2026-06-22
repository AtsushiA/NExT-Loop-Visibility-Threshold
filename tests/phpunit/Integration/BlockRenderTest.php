<?php
/**
 * ブロックレンダリングの Integration テスト。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

namespace NLVT\Tests\Integration;

use NLVT_Block;
use WP_Block;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

/**
 * @covers \NLVT_Block
 */
class BlockRenderTest extends WP_UnitTestCase {

	/**
	 * ブロックが登録されている。
	 */
	public function test_block_is_registered() {
		$this->assertTrue(
			WP_Block_Type_Registry::get_instance()->is_registered( 'nlvt/loop-visibility-threshold' )
		);
	}

	/**
	 * 投稿数が閾値以上なら content を表示する。
	 */
	public function test_renders_content_when_threshold_met() {
		self::factory()->post->create_many( 3 );

		$output = $this->render_with_threshold( 3 );
		$this->assertSame( 'INNER', $output );
	}

	/**
	 * 投稿数が閾値未満なら何も表示しない。
	 */
	public function test_hides_content_when_below_threshold() {
		self::factory()->post->create_many( 3 );

		$output = $this->render_with_threshold( 5 );
		$this->assertSame( '', $output );
	}

	/**
	 * Query コンテキストが無ければ何も表示しない。
	 */
	public function test_hides_content_without_query_context() {
		self::factory()->post->create_many( 3 );

		$registry = WP_Block_Type_Registry::get_instance();
		$parsed   = array(
			'blockName'    => 'nlvt/loop-visibility-threshold',
			'attrs'        => array( 'threshold' => 1 ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$block    = new WP_Block( $parsed, array(), $registry );
		$nlvt     = new NLVT_Block();

		$this->assertSame( '', $nlvt->render_block( array( 'threshold' => 1 ), 'INNER', $block ) );
	}

	/**
	 * 指定した閾値でブロックをレンダリングするヘルパー。
	 *
	 * @param int $threshold 閾値。
	 * @return string 出力。
	 */
	private function render_with_threshold( $threshold ) {
		$registry = WP_Block_Type_Registry::get_instance();
		$parsed   = array(
			'blockName'    => 'nlvt/loop-visibility-threshold',
			'attrs'        => array( 'threshold' => $threshold ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$context  = array(
			'query' => array(
				'perPage'  => 10,
				'postType' => 'post',
			),
		);
		$block    = new WP_Block( $parsed, $context, $registry );
		$nlvt     = new NLVT_Block();

		return $nlvt->render_block( array( 'threshold' => $threshold ), 'INNER', $block );
	}
}
