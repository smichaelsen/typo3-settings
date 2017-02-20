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

All configured values are available as TypoScript constants in `plugin.tx_settings`.

You can also access tha values via PHP:

    $configurationService = GeneralUtility::makeInstance(\Smichaelsen\Settings\Service\ConfigurationService::class);
    $allConfiguration = $configurationService->getAllConfiguration();

## Known issues

Right now javascript is not loaded correclty which intimidates a lot of form functionality like tabs, rich text editing, file uploads etc.
