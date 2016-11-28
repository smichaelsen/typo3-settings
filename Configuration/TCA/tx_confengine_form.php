<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'Configuration',
        'hideTable' => true,
        'label' => '',
        'security' => [
            'ignoreRootLevelRestriction' => true,
        ],
    ],
    'types' => [
        '0' => ['showitem' => ''],
    ],
    'columns' => [],
];
