<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied!!!');
}

if (TYPO3_MODE == 'BE') {
	TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Smichaelsen.confengine',
		'user',
		'Confengine',
		'',
		[
			'Form' => 'show',
		],
		[
			'access' => 'user,group',
			'icon' => 'EXT:lfeditor/ext_icon.png',
			'labels' => 'LLL:EXT:confengine/Resources/Private/Language/locallang_mod.xml',
		]
	);
}
