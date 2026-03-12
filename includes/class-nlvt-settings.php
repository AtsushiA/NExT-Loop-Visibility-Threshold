<?php
/**
 * 設定ページクラス
 *
 * @package NExT_Loop_Visibility_Threshold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NLVT_Settings
 *
 * 管理画面の設定ページを管理します。
 */
class NLVT_Settings {

	/**
	 * オプション名
	 */
	const OPTION_NAME = 'nlvt_settings';

	/**
	 * 初期化
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * 設定メニューを追加
	 */
	public function add_menu_page() {
		add_options_page(
			__( 'Loop Visibility Threshold', 'next-loop-visibility-threshold' ),
			__( 'Loop Visibility Threshold', 'next-loop-visibility-threshold' ),
			'manage_options',
			'nlvt-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * 設定フィールドを登録
	 */
	public function register_settings() {
		register_setting(
			'nlvt_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array( 'default_threshold' => 1 ),
			)
		);

		add_settings_section(
			'nlvt_main_section',
			__( '基本設定', 'next-loop-visibility-threshold' ),
			array( $this, 'render_section_description' ),
			'nlvt-settings'
		);

		add_settings_field(
			'nlvt_default_threshold',
			__( 'デフォルト閾値', 'next-loop-visibility-threshold' ),
			array( $this, 'render_threshold_field' ),
			'nlvt-settings',
			'nlvt_main_section'
		);
	}

	/**
	 * セクション説明
	 */
	public function render_section_description() {
		echo '<p>' . esc_html__( 'Loop Visibility Threshold ブロックのデフォルト設定を管理します。', 'next-loop-visibility-threshold' ) . '</p>';
	}

	/**
	 * 設定値のサニタイズ
	 *
	 * @param array $input 入力値。
	 * @return array サニタイズ済みの値。
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['default_threshold'] = isset( $input['default_threshold'] )
			? max( 1, (int) $input['default_threshold'] )
			: 1;

		return $sanitized;
	}

	/**
	 * 閾値フィールドを描画
	 */
	public function render_threshold_field() {
		$options   = get_option( self::OPTION_NAME, array( 'default_threshold' => 1 ) );
		$threshold = isset( $options['default_threshold'] ) ? (int) $options['default_threshold'] : 1;
		?>
		<input
			type="number"
			min="1"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_threshold]"
			value="<?php echo esc_attr( $threshold ); ?>"
			class="small-text"
		>
		<p class="description">
			<?php esc_html_e( '投稿数がこの値以上の場合にインナーブロックを表示します。ブロックごとに個別設定されていない場合（閾値=0）にこの値が使用されます。最小値は 1 です。', 'next-loop-visibility-threshold' ); ?>
		</p>
		<?php
	}

	/**
	 * 設定ページを描画
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// 保存完了メッセージ.
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'nlvt_messages',
				'nlvt_message',
				__( '設定を保存しました。', 'next-loop-visibility-threshold' ),
				'updated'
			);
		}

		settings_errors( 'nlvt_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p>
				<?php esc_html_e( 'Query Loop ブロック内で使用できる「Loop Visibility Threshold」ブロックの設定です。', 'next-loop-visibility-threshold' ); ?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'nlvt_settings_group' );
				do_settings_sections( 'nlvt-settings' );
				submit_button( __( '設定を保存', 'next-loop-visibility-threshold' ) );
				?>
			</form>

			<hr>
			<h2><?php esc_html_e( '使い方', 'next-loop-visibility-threshold' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'エディターで「Query Loop」ブロックを配置します。', 'next-loop-visibility-threshold' ); ?></li>
				<li><?php esc_html_e( 'Query Loop ブロック内（または Post Template 内）に「Loop Visibility Threshold」ブロックを追加します。', 'next-loop-visibility-threshold' ); ?></li>
				<li><?php esc_html_e( 'Loop Visibility Threshold ブロックのインナーブロックに表示したいコンテンツを追加します。', 'next-loop-visibility-threshold' ); ?></li>
				<li><?php esc_html_e( '必要に応じてブロックのサイドバーから個別の閾値を設定します（0 の場合はこのページのデフォルト値を使用）。', 'next-loop-visibility-threshold' ); ?></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * デフォルト閾値を取得（静的メソッド）
	 *
	 * @return int デフォルト閾値。
	 */
	public static function get_default_threshold() {
		$options = get_option( self::OPTION_NAME, array( 'default_threshold' => 1 ) );
		return (int) ( isset( $options['default_threshold'] ) ? $options['default_threshold'] : 1 );
	}
}
