<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Cleverreach',
    'Pi1',
    [
        \Supseven\Cleverreach\Controller\NewsletterController::class => 'optinForm, optinSubmit',
    ],
    [
        \Supseven\Cleverreach\Controller\NewsletterController::class => 'optinForm, optinSubmit',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Cleverreach',
    'Pi2',
    [
        \Supseven\Cleverreach\Controller\NewsletterController::class => 'optoutForm, optoutSubmit',
    ],
    [
        \Supseven\Cleverreach\Controller\NewsletterController::class => 'optoutForm, optoutSubmit',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('@import \'EXT:cleverreach/Configuration/TsConfig/NewElementWizard.tsconfig\';');
