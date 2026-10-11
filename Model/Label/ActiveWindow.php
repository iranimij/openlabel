<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Label;

use Magento\Framework\Phrase;

/**
 * State and plain-language summary of a label's schedule for the grid.
 */
class ActiveWindow
{
    public const ACTIVE = 'active';
    public const SCHEDULED = 'scheduled';
    public const EXPIRED = 'expired';
    public const DISABLED = 'disabled';

    /**
     * @param int $status
     * @param string|null $validFrom UTC
     * @param string|null $validTo UTC
     * @param \DateTimeImmutable $now
     * @return string one of the class constants
     */
    public function state(int $status, ?string $validFrom, ?string $validTo, \DateTimeImmutable $now): string
    {
        if ($status !== 1) {
            return self::DISABLED;
        }
        $utc = new \DateTimeZone('UTC');
        if ($validFrom !== null && new \DateTimeImmutable($validFrom, $utc) > $now) {
            return self::SCHEDULED;
        }
        if ($validTo !== null && new \DateTimeImmutable($validTo, $utc) < $now) {
            return self::EXPIRED;
        }

        return self::ACTIVE;
    }

    /**
     * @param string $state
     * @return Phrase
     */
    public function stateLabel(string $state): Phrase
    {
        return match ($state) {
            self::ACTIVE => __('Active now'),
            self::SCHEDULED => __('Scheduled'),
            self::EXPIRED => __('Ended'),
            default => __('Disabled'),
        };
    }

    /**
     * @param string|null $localFrom shop-timezone date time
     * @param string|null $localTo shop-timezone date time
     * @return Phrase
     */
    public function describe(?string $localFrom, ?string $localTo): Phrase
    {
        $from = $localFrom === null ? null : substr($localFrom, 0, 10);
        $to = $localTo === null ? null : substr($localTo, 0, 10);

        return match (true) {
            $from === null && $to === null => __('Always'),
            $to === null => __('From %1', $from),
            $from === null => __('Until %1', $to),
            default => __('%1 – %2', $from, $to),
        };
    }
}
