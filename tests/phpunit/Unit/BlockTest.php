<?php
/**
 * NLVT_Block の Unit テスト。
 *
 * @package NExT_Loop_Visibility_Threshold
 */

namespace NLVT\Tests\Unit;

use Brain\Monkey\Functions;
use NLVT_Block;
use ReflectionMethod;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers \NLVT_Block
 */
class BlockTest extends TestCase {

	/**
	 * Query Loop コンテキスト外では何も表示しない。
	 */
	public function test_render_returns_empty_without_query_context() {
		$block          = (object) array( 'context' => array() );
		$nlvt           = new NLVT_Block();

		$this->assertSame( '', $nlvt->render_block( array( 'threshold' => 1 ), 'VISIBLE', $block ) );
	}

	/**
	 * inherit クエリで投稿数が閾値以上なら content を返す。
	 */
	public function test_render_shows_content_when_count_meets_threshold() {
		$GLOBALS['wp_query'] = (object) array( 'found_posts' => 5 );
		$block               = (object) array( 'context' => array( 'query' => array( 'inherit' => true ) ) );
		$nlvt                = new NLVT_Block();

		$this->assertSame( 'VISIBLE', $nlvt->render_block( array( 'threshold' => 3 ), 'VISIBLE', $block ) );
	}

	/**
	 * inherit クエリで投稿数が閾値未満なら空を返す。
	 */
	public function test_render_hides_content_when_count_below_threshold() {
		$GLOBALS['wp_query'] = (object) array( 'found_posts' => 5 );
		$block               = (object) array( 'context' => array( 'query' => array( 'inherit' => true ) ) );
		$nlvt                = new NLVT_Block();

		$this->assertSame( '', $nlvt->render_block( array( 'threshold' => 10 ), 'VISIBLE', $block ) );
	}

	/**
	 * 閾値ちょうどの場合は表示する（>= 比較）。
	 */
	public function test_render_shows_content_when_count_equals_threshold() {
		$GLOBALS['wp_query'] = (object) array( 'found_posts' => 3 );
		$block               = (object) array( 'context' => array( 'query' => array( 'inherit' => true ) ) );
		$nlvt                = new NLVT_Block();

		$this->assertSame( 'VISIBLE', $nlvt->render_block( array( 'threshold' => 3 ), 'VISIBLE', $block ) );
	}

	/**
	 * 不正な post_type はホワイトリストにより 'post' へフォールバックする（v1.1.0 セキュリティ強化）。
	 */
	public function test_build_query_args_rejects_unregistered_post_type() {
		Functions\when( 'post_type_exists' )->alias(
			static function ( $post_type ) {
				return in_array( $post_type, array( 'post', 'page' ), true );
			}
		);

		$args = $this->invoke_build_args( array( 'postType' => 'malicious_type' ) );
		$this->assertSame( 'post', $args['post_type'] );

		$args = $this->invoke_build_args( array( 'postType' => 'page' ) );
		$this->assertSame( 'page', $args['post_type'] );
	}

	/**
	 * order はホワイトリスト（ASC/DESC）で検証される。
	 */
	public function test_build_query_args_validates_order() {
		Functions\when( 'post_type_exists' )->justReturn( true );

		$this->assertSame( 'ASC', $this->invoke_build_args( array( 'order' => 'asc' ) )['order'] );
		$this->assertSame( 'DESC', $this->invoke_build_args( array( 'order' => 'sneaky' ) )['order'] );
	}

	/**
	 * orderby はホワイトリストで検証され、不正値は 'date' へフォールバックする。
	 */
	public function test_build_query_args_validates_orderby() {
		Functions\when( 'post_type_exists' )->justReturn( true );

		$this->assertSame( 'title', $this->invoke_build_args( array( 'orderBy' => 'title' ) )['orderby'] );
		$this->assertSame( 'date', $this->invoke_build_args( array( 'orderBy' => 'DROP TABLE' ) )['orderby'] );
	}

	/**
	 * private メソッド build_query_args_from_context をリフレクションで呼ぶヘルパー。
	 *
	 * @param array $context Query コンテキスト。
	 * @return array WP_Query 引数。
	 */
	private function invoke_build_args( array $context ) {
		$method = new ReflectionMethod( NLVT_Block::class, 'build_query_args_from_context' );
		$method->setAccessible( true );

		return $method->invoke( new NLVT_Block(), $context );
	}
}
