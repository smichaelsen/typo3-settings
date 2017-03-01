<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['settings'] = function($parameters = null, $templateService = null){
    $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Smichaelsen\Settings\Service\ConfigurationService::class);
    $configurationService->injectTypoScriptConstants($parameters, $templateService);
};
