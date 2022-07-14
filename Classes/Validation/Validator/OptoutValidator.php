<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Validation\Validator;

use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\Service\ApiService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validate data of a reciver DTO for opt out process
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptoutValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ApiService
     */
    protected $apiService;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param ApiService $apiService
     */
    public function injectApi(ApiService $apiService): void
    {
        $this->apiService = $apiService;
    }

    protected function isValid($value): void
    {
        if (!$value instanceof RegistrationRequest) {
            $this->addError('Receiver missing', 20001);

            return;
        }

        $email = filter_var($value->email, FILTER_SANITIZE_EMAIL);

        if (!$email || $email !== $value->email || !GeneralUtility::validEmail($email)) {
            $this->result->forProperty('email')->addError(new Error('Email invalid', 20002));
        }

        $newsletters = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'CleverreachSubscription'
        )['newsletter'] ?? [];

        $rootUid = $GLOBALS['TSFE']->rootLine[0]['uid'];

        if (!isset($newsletters[$rootUid])) {
            $this->result->forProperty('groupId')->addError(new Error('no group IDs configured', 1594195300));
        }

        if (empty($newsletters[$rootUid][$value->groupId]['formId'])) {
            $this->result->forProperty('groupId')->addError(new Error('unknown newsletter', 20004));
        }

        if (!$this->apiService->isReceiverOfGroup($value->email, $value->groupId)) {
            $this->addError('not subscribed', 20005);
        }
    }
}