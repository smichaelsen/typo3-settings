<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied!!!');
}

if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'user',
        'sitesettings',
        'after:setup',
        '',
        [
            'routeTarget' => \Smichaelsen\Settings\Controller\FormController::class . '::mainAction',
            'access' => 'group,user',
            'name' => 'user_sitesettings',
            'labels' => [
                'tabs_images' => [
                    'tab' => 'EXT:settings/Resources/Public/settings.svg',
                ],
                'll_ref' => 'LLL:EXT:settings/Resources/Private/Language/locallang_mod.xml',
            ],
        ]
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod.web_list.deniedNewTables := addToList(tx_settings_form)');
}
