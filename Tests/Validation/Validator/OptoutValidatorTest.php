<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Validation\Validator;

use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Service\ConfigurationService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;
use Supseven\Cleverreach\Validation\Validator\OptoutValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptoutValidatorTest extends LocalBaseTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @dataProvider validateDataProvider
     * @param RegistrationRequest $receiver
     * @param int $expectedErrorCode
     */
    public function testValidate(?RegistrationRequest $receiver, int $expectedErrorCode): void
    {
        $api = $this->createMock(ApiService::class);
        $api->expects(self::any())->method('isReceiverOfGroup')->willReturn(true);

        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [0 => ['uid' => 1]];

        $config = $this->createStub(ConfigurationService::class);
        $config->method('isTestEmail')->willReturn(false);

        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);
        GeneralUtility::setSingletonInstance(ApiService::class, $api);
        GeneralUtility::setSingletonInstance(ConfigurationManager::class, $this->getConfigurationManager());

        $subject = new OptoutValidator();
        $result = $subject->validate($receiver);
        $errors = $result->getFlattenedErrors();
        $error = current(current($errors));

        self::assertSame($expectedErrorCode, $error->getCode());
    }

    public function validateDataProvider(): array
    {
        $noEmail = new RegistrationRequest('', true, 1);
        $invalidEmail = new RegistrationRequest('abc', true, 1);
        $invalidGroup = new RegistrationRequest('abc@domain.tld', true, 3);

        return [
            'No model'      => [null, 20001],
            'Missing Email' => [$noEmail, 20002],
            'Invalid Email' => [$invalidEmail, 20002],
            'Invalid group' => [$invalidGroup, 20004],
        ];
    }

    public function testValidateCorrect(): void
    {
        $api = $this->createMock(ApiService::class);
        $api->expects(self::any())->method('isReceiverOfGroup')->willReturn(true);

        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [0 => ['uid' => 1]];

        $receiver = new RegistrationRequest('abc@domain.tld', true, 1);

        $config = $this->createStub(ConfigurationService::class);
        $config->method('isTestEmail')->willReturn(false);

        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);
        GeneralUtility::setSingletonInstance(ApiService::class, $api);
        GeneralUtility::setSingletonInstance(ConfigurationManager::class, $this->getConfigurationManager());

        $subject = new OptoutValidator();
        $result = $subject->validate($receiver);

        self::assertSame([], $result->getFlattenedErrors());
    }

    public function testValidateUnregistered(): void
    {
        $api = $this->createMock(ApiService::class);
        $api->expects(self::any())->method('isReceiverOfGroup')->willReturn(false);

        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [0 => ['uid' => 1]];

        $receiver = new RegistrationRequest('abc@domain.tld', true, 1);

        $config = $this->createStub(ConfigurationService::class);
        $config->method('isTestEmail')->willReturn(false);

        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);
        GeneralUtility::setSingletonInstance(ApiService::class, $api);
        GeneralUtility::setSingletonInstance(ConfigurationManager::class, $this->getConfigurationManager());

        $subject = new OptoutValidator();
        $result = $subject->validate($receiver);
        $errors = $result->getFlattenedErrors();
        $error = current(current($errors));

        self::assertSame(20005, $error->getCode());
    }

    private function getConfigurationManager(): ConfigurationManager
    {
        $conf = $this->createMock(ConfigurationManager::class);
        $conf->expects(self::any())->method('getConfiguration')->with(
            self::equalTo(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS),
            self::equalTo('CleverreachSubscription')
        )->willReturn([
            'newsletter' => [
                '1' => [
                    '1' => [
                        'label'  => 'FirstNewsletter',
                        'formId' => '2',
                    ],
                ],
            ],
        ]);

        return $conf;
    }
}
