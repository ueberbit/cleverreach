<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Powermail\Validator;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Supseven\Cleverreach\Service\ApiService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OptinValidator
{
    public function __construct(private readonly ApiService $apiService)
    {
    }

    /**
     * Check if given number is higher than in configuration
     *
     * @param string $value
     * @param string $validationConfiguration
     * @return bool
     */
    public function validate120(mixed $value, mixed $validationConfiguration): bool
    {
        $value = trim((string)$value);

        if (!GeneralUtility::validEmail($value)) {
            return false;
        }

        return !$this->apiService->isReceiverOfGroupAndActive($value);
    }
}
