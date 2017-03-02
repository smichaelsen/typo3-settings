# settings - TYPO3 Extension

[![Build Status](https://travis-ci.org/smichaelsen/typo3-settings.svg?branch=master)](https://travis-ci.org/smichaelsen/typo3-settings)

Because you haven't enough places in TYPO3 to configure stuff, right?

## What it does

settings offers a new backend module in which extensions can offer configuration options. Why?
Ever since I was missing a spot where editors (non-admins) can do global configuration.
 
![Screenshot](Documentation/Images/Screenshot_Overview.png?raw=true "Screenshot")

## How to use

### Define fields:

Define your fields in TCA syntax and add it to the table `tx_settings_form`.
 
Example (in `Configuration/TCA/Overrides/tx_settings_form.php`):

    <?php
    if (!defined('TYPO3_MODE')) {
        die ('Access denied.');
    }
    
    $GLOBALS['TCA']['tx_settings_form']['columns'] = array_merge(
        $GLOBALS['TCA']['tx_settings_form']['columns'],
        [
            'tx_myext_myfield' => [
                'label' => 'My field',
                'config' => [
                    'type' => 'input',
                ],
            ],
        ]
    );
     
     
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_settings_form',
        'tx_myext_myfield'
    );
    
### Read configuration

#### 1. TypoScript ###

All configured values are available as TypoScript constants in `plugin.tx_settings`.

### 2. Fluid ViewHelper ###

    {namespace s=Smichaelsen\Settings\ViewHelpers}
    {s:getValue(name:'tx_myext_myfield')} or <s:getValue name="tx_myext_myfield"/>

### 3. PHP ###

You can also access the values via PHP:

    $configurationService = GeneralUtility::makeInstance(\Smichaelsen\Settings\Service\ConfigurationService::class);
    $allConfiguration = $configurationService->getAllConfiguration($rootPageUid);

## Known issues

Inline fields do not work yet, that includes FAL file upload fields.
