<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied!!!');
}

if (TYPO3_MODE == 'BE') {
	TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Smichaelsen.settings',
		'user',
		'Settings',
		'',
		[
			'Form' => 'show, save',
		],
		[
			'access' => 'user,group',
			'icon' => 'EXT:settings/Resources/Public/settings.svg',
			'labels' => 'LLL:EXT:settings/Resources/Private/Language/locallang_mod.xml',
		]
	);

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod.web_list.deniedNewTables := addToList(tx_settings_form)');
}
