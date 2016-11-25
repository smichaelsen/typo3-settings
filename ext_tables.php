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
			'Form' => 'show, save',
		],
		[
			'access' => 'user,group',
			'icon' => 'EXT:confengine/ext_icon.png',
			'labels' => 'LLL:EXT:confengine/Resources/Private/Language/locallang_mod.xml',
		]
	);

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod.web_list.deniedNewTables := addToList(tx_confengine_form)');
}
