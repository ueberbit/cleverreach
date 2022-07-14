<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\DTO;

/**
 * Receiver data sent by the form
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class RegistrationRequest
{
    /**
     * Receiver constructor.
     * @param string $email
     * @param bool $agreed
     * @param int $groupId
     */
    public function __construct(
        public readonly string $email = '',
        public readonly bool $agreed = false,
        public readonly int $groupId = 0
    ) {
    }
}
