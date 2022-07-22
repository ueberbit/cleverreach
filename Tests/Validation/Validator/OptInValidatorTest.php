<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Validation\Validator;

use Supseven\Cleverreach\DTO\Receiver;
use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Service\ConfigurationService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;
use Supseven\Cleverreach\Validation\Validator\OptinValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test the opt in validator
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptInValidatorTest extends LocalBaseTestCase
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
        $api->expects(self::any())->method('getReceiverOfGroup')->willReturn(null);

        GeneralUtility::setSingletonInstance(ApiService::class, $api);

        $this->addConfigurationService();

        $subject = new OptinValidator();
        $result = $subject->validate($receiver);
        $errors = $result->getFlattenedErrors();
        $error = current(current($errors));

        self::assertSame($expectedErrorCode, $error->getCode());
    }

    public function validateDataProvider(): array
    {
        $noEmail = new RegistrationRequest('', true, 1);
        $invalidEmail = new RegistrationRequest('abc', true, 1);
        $notAgreed = new RegistrationRequest('abc@domain.tld', false, 1);
        $invalidGroup = new RegistrationRequest('abc@domain.tld', true, 3);

        return [
            'No model'      => [null, 10001],
            'Missing Email' => [$noEmail, 10002],
            'Invalid Email' => [$invalidEmail, 10002],
            'Not agreed'    => [$notAgreed, 10003],
            'Invalid group' => [$invalidGroup, 10004],
        ];
    }

    public function testValidateCorrect(): void
    {
        $api = $this->createMock(ApiService::class);
        $api->expects(self::any())->method('getReceiverOfGroup')->willReturn(null);

        $this->addConfigurationService();

        $receiver = new RegistrationRequest('abc@domain.tld', true, 1);

        GeneralUtility::setSingletonInstance(ApiService::class, $api);

        $subject = new OptinValidator();
        $result = $subject->validate($receiver);

        self::assertSame([], $result->getFlattenedErrors());
    }

    public function testValidateRegistered(): void
    {
        $apiResult = new Receiver('', 1234, 0, 12345, []);

        $this->addConfigurationService();

        $api = $this->createMock(ApiService::class);
        $api->expects(self::any())->method('getReceiverOfGroup')->willReturn($apiResult);

        $receiver = new RegistrationRequest('abc@domain.tld', true, 1);

        GeneralUtility::setSingletonInstance(ApiService::class, $api);

        $subject = new OptinValidator();
        $result = $subject->validate($receiver);
        $errors = $result->getFlattenedErrors();
        $error = current(current($errors));

        self::assertSame(10005, $error->getCode());
    }

    private function addConfigurationService(): void
    {
        $config = $this->createStub(ConfigurationService::class);
        $config->method('isTestEmail')->willReturn(false);
        $config->method('getCurrentNewsletters')->willReturn([
            1 => [
                'label'  => 'FirstNewsletter',
                'formId' => '2',
            ],
        ]);

        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);
    }
}
