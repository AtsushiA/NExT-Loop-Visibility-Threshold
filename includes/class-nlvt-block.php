<?php
/**
 * ブロック登録・レンダリングクラス
 *
 * @package NExT_Loop_Visibility_Threshold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NLVT_Block
 *
 * Gutenberg ブロックの登録とサーバーサイドレンダリングを担当します。
 */
class NLVT_Block {

	/**
	 * 初期化
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * ブロックを登録
	 *
	 * block.json を読み込み、サーバーサイドレンダリングのコールバックを設定します。
	 */
	public function register_block() {
		register_block_type(
			NLVT_PLUGIN_DIR . 'block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * ブロックのサーバーサイドレンダリング
	 *
	 * Query Loop の投稿総数が閾値以上の場合にインナーブロックを出力します。
	 *
	 * @param array    $attributes ブロック属性。
	 * @param string   $content    レンダリング済みインナーブロック HTML。
	 * @param WP_Block $block      ブロックインスタンス（コンテキスト含む）。
	 * @return string 出力 HTML。
	 */
	public function render_block( $attributes, $content, $block ) {
		// 閾値を決定（ブロック個別設定 → サイトデフォルト）.
		$threshold = isset( $attributes['threshold'] ) && $attributes['threshold'] > 0
			? (int) $attributes['threshold']
			: NLVT_Settings::get_default_threshold();

		// Query Loop のコンテキストを確認.
		$query_context = isset( $block->context['query'] ) ? $block->context['query'] : null;

		if ( null === $query_context ) {
			// Query Loop 外では何も表示しない.
			return '';
		}

		// 投稿総数を取得.
		$total_posts = $this->count_query_posts( $block, $query_context );

		// 閾値と比較して表示・非表示を決定.
		if ( $total_posts >= $threshold ) {
			return $content;
		}

		return '';
	}

	/**
	 * Query Loop の投稿総数をカウント
	 *
	 * WordPress コア関数 build_query_vars_from_query_block() が利用可能な場合はそれを使用し、
	 * 利用できない場合はコンテキストから独自にクエリを構築します。
	 *
	 * @param WP_Block $block         ブロックインスタンス。
	 * @param array    $query_context Query コンテキスト配列。
	 * @return int 投稿総数。
	 */
	private function count_query_posts( $block, $query_context ) {
		global $wp_query;

		// inherit=true の場合はメインクエリの投稿数を使用.
		$inherit = ! empty( $query_context['inherit'] );
		if ( $inherit ) {
			return (int) $wp_query->found_posts;
		}

		// WordPress 5.8+ のコア関数を優先使用.
		if ( function_exists( 'build_query_vars_from_query_block' ) ) {
			$query_args = build_query_vars_from_query_block( $block, 1 );
		} else {
			$query_args = $this->build_query_args_from_context( $query_context );
		}

		// ページネーションを無効にして全投稿数を取得.
		$query_args['posts_per_page']         = -1;
		$query_args['fields']                 = 'ids';
		$query_args['no_found_rows']          = false;
		$query_args['update_post_meta_cache'] = false;
		$query_args['update_post_term_cache'] = false;
		$query_args['paged']                  = 1;

		$count_query = new WP_Query( $query_args );

		return (int) $count_query->found_posts;
	}

	/**
	 * コンテキストから WP_Query 引数を構築（フォールバック用）
	 *
	 * build_query_vars_from_query_block() が利用できない古い WordPress 向けのフォールバックです。
	 *
	 * @param array $query_context Query コンテキスト配列。
	 * @return array WP_Query 引数。
	 */
	private function build_query_args_from_context( $query_context ) {
		// post_type: 登録済みの投稿タイプのみ許可.
		$raw_post_type = isset( $query_context['postType'] ) ? $query_context['postType'] : 'post';
		$post_type     = post_type_exists( $raw_post_type ) ? $raw_post_type : 'post';

		// order: 'ASC' または 'DESC' のみ許可.
		$raw_order = isset( $query_context['order'] ) ? strtoupper( $query_context['order'] ) : 'DESC';
		$order     = in_array( $raw_order, array( 'ASC', 'DESC' ), true ) ? $raw_order : 'DESC';

		// orderby: WP_Query が許可する値のホワイトリスト.
		$allowed_orderby = array(
			'none',
			'ID',
			'author',
			'title',
			'name',
			'type',
			'date',
			'modified',
			'parent',
			'rand',
			'comment_count',
			'relevance',
			'menu_order',
			'meta_value',
			'meta_value_num',
			'post__in',
		);
		$raw_orderby     = isset( $query_context['orderBy'] ) ? $query_context['orderBy'] : 'date';
		$orderby         = in_array( $raw_orderby, $allowed_orderby, true ) ? $raw_orderby : 'date';

		$args = array(
			'post_type'   => $post_type,
			'post_status' => 'publish',
			'order'       => $order,
			'orderby'     => $orderby,
		);

		// 著者フィルター.
		if ( ! empty( $query_context['author'] ) ) {
			$args['author__in'] = array_map( 'intval', (array) $query_context['author'] );
		}

		// 検索キーワード.
		if ( ! empty( $query_context['search'] ) ) {
			$args['s'] = sanitize_text_field( $query_context['search'] );
		}

		// 除外投稿.
		if ( ! empty( $query_context['exclude'] ) ) {
			$args['post__not_in'] = array_map( 'intval', (array) $query_context['exclude'] );
		}

		// スティッキー投稿.
		if ( isset( $query_context['sticky'] ) ) {
			$sticky_posts = get_option( 'sticky_posts', array() );
			if ( 'only' === $query_context['sticky'] ) {
				$args['post__in']            = $sticky_posts;
				$args['ignore_sticky_posts'] = 1;
			} elseif ( 'exclude' === $query_context['sticky'] ) {
				$args['post__not_in']        = array_merge(
					isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(),
					$sticky_posts
				);
				$args['ignore_sticky_posts'] = 1;
			}
		}

		// タクソノミークエリ.
		if ( ! empty( $query_context['taxQuery'] ) && is_array( $query_context['taxQuery'] ) ) {
			$tax_query = array();
			foreach ( $query_context['taxQuery'] as $taxonomy => $terms ) {
				if ( ! empty( $terms ) ) {
					$tax_query[] = array(
						'taxonomy' => sanitize_key( $taxonomy ),
						'field'    => 'term_id',
						'terms'    => array_map( 'intval', (array) $terms ),
					);
				}
			}
			if ( ! empty( $tax_query ) ) {
				$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Query Loop の絞り込みに必要。
			}
		}

		// 親投稿フィルター（ページ階層用）.
		if ( ! empty( $query_context['parents'] ) ) {
			$args['post_parent__in'] = array_map( 'intval', (array) $query_context['parents'] );
		}

		return $args;
	}
}
