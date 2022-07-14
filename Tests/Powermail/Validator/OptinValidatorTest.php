<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Powermail\Validator;

use Supseven\Cleverreach\Powermail\Validator\OptinValidator;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptinValidatorTest extends LocalBaseTestCase
{
    public function testNoValidEmail(): void
    {
        $api = $this->createStub(ApiService::class);
        $subject = new OptinValidator($api);

        self::assertFalse($subject->validate120('abcd', null));
    }

    public function testAlreadyRegistered(): void
    {
        $mail = 'someone@domain.tld';
        $api = $this->createMock(ApiService::class);
        $api->expects(self::once())->method('isReceiverOfGroupAndActive')->with(self::equalTo($mail))->willReturn(true);

        $subject = new OptinValidator($api);

        self::assertFalse($subject->validate120($mail, null));
    }

    public function testValid(): void
    {
        $mail = 'someone@domain.tld';
        $api = $this->createMock(ApiService::class);
        $api->expects(self::once())->method('isReceiverOfGroupAndActive')->with(self::equalTo($mail))->willReturn(false);

        $subject = new OptinValidator($api);

        self::assertTrue($subject->validate120($mail, null));
    }
}
