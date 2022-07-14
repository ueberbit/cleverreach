<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\DTO;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class Receiver
{
    /**
     * Receiver constructor.
     * @param string $email
     * @param array $attributes
     */
    public function __construct(
        public readonly string $email,
        public readonly int $activated,
        public readonly int $deactivated,
        public readonly int $registered,
        public readonly array $attributes,
        public readonly array $globalAttributes = []
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email'             => $this->email,
            'activated'         => $this->activated,
            'deactivated'       => $this->deactivated,
            'registered'        => $this->registered,
            'attributes'        => $this->attributes ?: null,
            'global_attributes' => $this->attributes ?: null,
            'source'            => 'TYPO3',
        ];
    }

    /**
     * @param array $data
     * @return Receiver
     */
    public static function make(array $data): Receiver
    {
        return new self(
            (string)$data['email'],
            (int)$data['activated'],
            (int)$data['deactivated'],
            (int)$data['registered'],
            (array)($data['attributes'] ?? []),
            (array)($data['global_attributes'] ?? []),
        );
    }

    public static function create(string $mail, array $attributes = []): Receiver
    {
        return new self(
            $mail,
            0,
            0,
            (int)($GLOBALS['EXEC_TIME'] ?? time()),
            $attributes,
            [],
        );
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->activated !== 0 && $this->deactivated === 0;
    }
}
