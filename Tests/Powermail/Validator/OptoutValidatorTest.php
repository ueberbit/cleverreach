<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Powermail\Validator;

use Supseven\Cleverreach\Powermail\Validator\OptoutValidator;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class OptoutValidatorTest extends LocalBaseTestCase
{
    public function testNoValidEmail(): void
    {
        $api = $this->createStub(ApiService::class);
        $subject = new OptoutValidator($api);

        self::assertFalse($subject->validate121('abcd', null));
    }

    public function testNotRegistered(): void
    {
        $mail = 'someone@domain.tld';
        $api = $this->createMock(ApiService::class);
        $api->expects(self::once())->method('isReceiverOfGroupAndActive')->with(self::equalTo($mail))->willReturn(false);

        $subject = new OptoutValidator($api);

        self::assertFalse($subject->validate121($mail, null));
    }

    public function testValid(): void
    {
        $mail = 'someone@domain.tld';
        $api = $this->createMock(ApiService::class);
        $api->expects(self::once())->method('isReceiverOfGroupAndActive')->with(self::equalTo($mail))->willReturn(true);

        $subject = new OptoutValidator($api);

        self::assertTrue($subject->validate121($mail, null));
    }
}
