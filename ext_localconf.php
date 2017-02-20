<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['confengine'] = function(){
    $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Smichaelsen\Confengine\Service\ConfigurationService::class);
    $configurationService->injectTypoScriptConstants();
};
