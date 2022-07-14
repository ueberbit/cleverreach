<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Form\Validator;

use Supseven\Cleverreach\Form\Validator\OptinValidator;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptinValidatorTest extends LocalBaseTestCase
{
    public function testIsValid(): void
    {
        $email = 'somebody@domain.tld';
        $options = ['groupId' => '147'];
        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::any())->method('isReceiverOfGroupAndActive')->with(
            self::equalTo($email),
            self::equalTo((int)$options['groupId'])
        )->willReturn(false);

        $subject = new OptinValidator(
            $options,
            $apiService,
            $this->getConfiguration()
        );
        $result = $subject->validate($email);

        self::assertFalse($result->hasErrors());
    }

    public function testIsNotValidAlreadyRegistered(): void
    {
        $email = 'somebody@domain.tld';
        $options = ['groupId' => '147'];
        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::any())->method('isReceiverOfGroupAndActive')->with(
            self::equalTo($email),
            self::equalTo((int)$options['groupId'])
        )->willReturn(true);

        $subject = new class (
            $options,
            $apiService,
            $this->getConfiguration()
        ) extends OptinValidator {
            public function translateErrorMessage($translateKey, $extensionName, $arguments = []): string
            {
                return (string)$translateKey;
            }
        };
        $result = $subject->validate($email);

        self::assertTrue($result->hasErrors());
    }

    public function testIsNotValidNoEmail(): void
    {
        $email = 'Some Body';
        $options = ['groupId' => '147'];
        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::never())->method('isReceiverOfGroupAndActive');

        $subject = new class (
            $options,
            $apiService,
            $this->getConfiguration()
        ) extends OptinValidator {
            public function translateErrorMessage($translateKey, $extensionName, $arguments = []): string
            {
                return (string)$translateKey;
            }
        };
        $result = $subject->validate($email);

        self::assertTrue($result->hasErrors());
    }
}
