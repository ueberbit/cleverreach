<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Validation\Validator;

use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\Service\ApiService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validate data of a reciver DTO for opt in process
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptinValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected ConfigurationManagerInterface $configurationManager;

    /**
     * @var ApiService
     */
    protected ApiService $apiService;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param ApiService $api
     */
    public function injectApi(ApiService $api): void
    {
        $this->apiService = $api;
    }

    protected function isValid($value): void
    {
        if (!$value instanceof RegistrationRequest) {
            $this->addError('Receiver missing', 10001);

            return;
        }

        $email = filter_var($value->email, FILTER_SANITIZE_EMAIL);

        if (!$email || $email !== $value->email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->result->forProperty('email')->addError(new Error('Email invalid', 10002));
        }

        if ($value->agreed !== true) {
            $this->result->forProperty('agreed')->addError(new Error('Not accepted', 10003));
        }

        $newsletters = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'CleverreachSubscription'
        )['newsletter'] ?? [];

        $rootUid = $GLOBALS['TSFE']->rootLine[0]['uid'];

        if (!isset($newsletters[$rootUid])) {
            $this->result->forProperty('groupId')->addError(new Error('no group ID', 1594194662));
        }

        if (empty($newsletters[$rootUid][$value->groupId]['formId'])) {
            $this->result->forProperty('groupId')->addError(new Error('unknown newsletter', 10004));
        }

        $receiver = $this->apiService->getReceiverOfGroup($value->email, $value->groupId);

        if ($receiver && $receiver->isActive()) {
            $this->addError('already registered', 10005);
        }
    }
}
