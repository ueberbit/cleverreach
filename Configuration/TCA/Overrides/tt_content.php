<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

(static function (): void {
    $plugins = ['Optin', 'Optout'];

    foreach ($plugins as $plugin) {
        $key = 'cleverreach_' . strtolower($plugin);

        ExtensionUtility::registerPlugin(
            'Cleverreach',
            $plugin,
            'LLL:EXT:cleverreach/Resources/Private/Language/shared.xlf:plugin.' . $key . '.title',
            'cleverreach',
            'Newsletter'
        );

        $GLOBALS['TCA']['tt_content']['types'][$key]['showitem'] = '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        ';
    }
})();
