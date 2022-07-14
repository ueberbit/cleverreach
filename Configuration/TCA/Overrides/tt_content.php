<?php

declare(strict_types=1);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Cleverreach',
    'Pi1',
    'LLL:EXT:cleverreach/Resources/Private/Language/locallang.xlf:plugin.cleverreach_pi1.title'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['cleverreach_pi1'] = 'pages, recursive';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Cleverreach',
    'Pi2',
    'LLL:EXT:cleverreach/Resources/Private/Language/locallang.xlf:plugin.cleverreach_pi2.title'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['cleverreach_pi2'] = 'pages, recursive';
