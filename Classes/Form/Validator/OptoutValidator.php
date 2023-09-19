<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Form\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator for email addresses
 *
 * @api
 */
class OptoutValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'groupId' => [0, 'Group ID', 'integer'],
    ];

    public function __construct(
        private readonly ApiService $apiService,
        private readonly ConfigurationService $configurationService
    ) {
    }

    /**
     * Checks if the given value is already in the list
     *
     * @param mixed $value The value that should be validated
     * @api
     */
    public function isValid($value): void
    {
        $groupId = (int)($this->options['groupId'] ?? '') ?: $this->configurationService->getGroupId();

        if (empty($groupId)) {
            $this->addError($this->translateErrorMessage('validator.noGroupId', 'cleverreach'), 1534719428);

            return;
        }

        if (!MathUtility::canBeInterpretedAsInteger($value) && !GeneralUtility::validEmail($value)) {
            $this->addError($this->translateErrorMessage('validator.noReceiverID', 'cleverreach'), 1534719429);

            return;
        }

        if ($this->configurationService->isTestEmail((string)$value)) {
            return;
        }

        if (!$this->apiService->isReceiverOfGroupAndActive($value, $groupId)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.notInList',
                    'cleverreach'
                ),
                1534719523
            );
        }
    }
}
