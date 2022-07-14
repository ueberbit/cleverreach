<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\DTO;

/**
 * Data of a cleverreach supscription
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class Subscriber
{
    /**
     * Supscriber constructor.
     * @param string $email
     * @param int $groupId
     * @param int $formId
     */
    public function __construct(
        public readonly string $email,
        public readonly int $groupId,
        public readonly int $formId
    ) {
    }
}
