<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Conf Engine',
    'description' => 'User friendly configuration module for editors',
    'category' => 'backend',
    'state' => 'stable',
    'author' => 'Sebastian Michaelsen',
    'author_email' => 'sebastian@app-zap.de',
    'author_company' => 'app-zap',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.2-7.99.99',
        ),
    ),
);
