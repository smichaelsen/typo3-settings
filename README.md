# confengine - TYPO3 Extension

Because you haven't enough places in TYPO3 to configure stuff, right?

## What it does

confengine offers a new backend module in which extensions can offer configuration options. Why?
Ever since I was missing a spot where editors (non-admins) can do global configuration.
 
![Screenshot](Documentation/Images/Screenshot_Overview.png?raw=true "Screenshot")

## How to use

### Define fields:

Define your fields in TCA syntax and add it to the table `tx_confengine_form`.
 
Example (in `Configuration/TCA/Overrides/tx_confengine_form.php`):

    <?php
    if (!defined('TYPO3_MODE')) {
        die ('Access denied.');
    }
    
    $GLOBALS['TCA']['tx_confengine_form']['columns'] = array_merge(
        $GLOBALS['TCA']['tx_confengine_form']['columns'],
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
        'tx_confengine_form',
        'tx_myext_myfield'
    );
    
### Read configuration

    $configurationService = GeneralUtility::makeInstance(\Smichaelsen\Confengine\Service\ConfigurationService::class);
    $allConfiguration = $configurationService->getAllConfiguration();

## Known issues

Right now javascript is not loaded correclty which intimidates a lot of form functionality like tabs, rich text editing, file uploads etc.
