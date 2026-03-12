<?php
/**
 * エディタースクリプトの依存関係定義
 *
 * block.json で "editorScript": "file:./assets/editor.js" を使用する場合、
 * WordPress は同名の .asset.php を参照して依存パッケージを解決します。
 * このファイルがないと wp-blocks 等がロードされる前にスクリプトが実行されます。
 */
return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-block-editor',
		'wp-components',
		'wp-element',
		'wp-i18n',
	),
	'version'      => '1.1.0',
);
