/**
 * NExT Loop Visibility Threshold - Editor Script
 *
 * ビルドステップ不要。wp グローバルを直接使用。
 */
( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var el              = element.createElement;
	var Fragment        = element.Fragment;
	var __              = i18n.__;
	var InnerBlocks     = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps   = blockEditor.useBlockProps;
	var PanelBody       = components.PanelBody;
	var PanelRow        = components.PanelRow;
	var TextControl     = components.TextControl;

	/**
	 * エディター表示コンポーネント
	 */
	function Edit( props ) {
		var attributes   = props.attributes;
		var setAttributes = props.setAttributes;
		var threshold    = attributes.threshold || 0;

		var blockProps = useBlockProps( {
			className: 'nlvt-loop-visibility-threshold',
			style: {
				outline: '1px dashed #007cba',
				outlineOffset: '2px',
				padding: '8px',
				minHeight: '60px',
				position: 'relative',
			},
		} );

		var labelText = threshold > 0
			? __( '表示条件: 投稿数 ≥ ', 'next-loop-visibility-threshold' ) + threshold
			: __( '表示条件: サイト設定のデフォルト値を使用', 'next-loop-visibility-threshold' );

		return el(
			Fragment,
			null,

			/* サイドバー設定パネル */
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{
						title: __( '表示閾値設定', 'next-loop-visibility-threshold' ),
						initialOpen: true,
					},
					el(
						PanelRow,
						null,
						el( TextControl, {
							__nextHasNoMarginBottom: true,
							label: __( '最低投稿数', 'next-loop-visibility-threshold' ),
							help: threshold === 0
								? __( '0 の場合はサイト設定のデフォルト値を使用します', 'next-loop-visibility-threshold' )
								: __( 'この数以上の投稿がある場合にインナーブロックを表示します', 'next-loop-visibility-threshold' ),
							type: 'number',
							min: '0',
							value: threshold,
							onChange: function ( value ) {
								setAttributes( { threshold: parseInt( value, 10 ) || 0 } );
							},
						} )
					)
				)
			),

			/* ブロック本体 */
			el(
				'div',
				blockProps,

				/* 閾値インジケーター */
				el(
					'div',
					{
						style: {
							fontSize: '11px',
							lineHeight: '1.4',
							color: '#007cba',
							fontWeight: '600',
							marginBottom: '6px',
							pointerEvents: 'none',
						},
					},
					labelText
				),

				/* インナーブロック */
				el( InnerBlocks, null )
			)
		);
	}

	/**
	 * 保存関数（サーバーサイドレンダリングのためインナーブロックのみ保存）
	 */
	function Save() {
		return el( InnerBlocks.Content, null );
	}

	blocks.registerBlockType( 'nlvt/loop-visibility-threshold', {
		edit: Edit,
		save: Save,
	} );

} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n
);
