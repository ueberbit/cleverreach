<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Supseven\Cleverreach\Service\ConfigurationService;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class LocalBaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['EXEC_TIME'] = 123456789;
    }

    protected function getConfiguration(): ConfigurationService & Stub
    {
        $config = $this->createStub(ConfigurationService::class);
        $config->method('getRestUrl')->willReturn('https://api.cleverreach.com');
        $config->method('getClientId')->willReturn('123');
        $config->method('getLoginName')->willReturn('abc');
        $config->method('getPassword')->willReturn('def');
        $config->method('getGroupId')->willReturn(123);
        $config->method('getFormId')->willReturn(456);

        return $config;
    }
}
