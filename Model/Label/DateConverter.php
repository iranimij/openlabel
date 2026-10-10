<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Label;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Converts label dates between the shop timezone (what the merchant types and sees) and UTC (what is stored
 * and compared at resolve time, 06 · F27). The one place this conversion happens, used by the grid and the form.
 */
class DateConverter
{
    private const STORAGE_FORMAT = 'Y-m-d H:i:s';
    private const ISO_FORMATS = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d'];

    /**
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        private readonly TimezoneInterface $timezone,
        private readonly ResolverInterface $localeResolver
    ) {
    }

    /**
     * Shop-timezone input (ISO or the admin locale's short format) to a UTC storage string.
     *
     * @param string|null $value
     * @param bool $endOfDay a date without time means the end of that day (for "valid to")
     * @return string|null
     * @throws LocalizedException
     */
    public function toUtc(?string $value, bool $endOfDay = false): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $zone = new \DateTimeZone($this->timezone->getConfigTimezone());
        [$date, $hasTime] = $this->parse($value, $zone);
        if (!$hasTime) {
            $date = $endOfDay ? $date->setTime(23, 59, 59) : $date->setTime(0, 0);
        }

        return $date->setTimezone(new \DateTimeZone('UTC'))->format(self::STORAGE_FORMAT);
    }

    /**
     * UTC storage string to the shop timezone, same format.
     *
     * @param string|null $utc
     * @return string|null
     */
    public function toLocal(?string $utc): ?string
    {
        if ($utc === null || trim($utc) === '') {
            return null;
        }
        $date = new \DateTimeImmutable($utc, new \DateTimeZone('UTC'));

        return $date->setTimezone(new \DateTimeZone($this->timezone->getConfigTimezone()))
            ->format(self::STORAGE_FORMAT);
    }

    /**
     * @param string $value
     * @param \DateTimeZone $zone
     * @return array{\DateTimeImmutable, bool}
     * @throws LocalizedException
     */
    private function parse(string $value, \DateTimeZone $zone): array
    {
        foreach (self::ISO_FORMATS as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value, $zone);
            if ($date !== false && $date->format($format) === $value) {
                return [$date, $format !== 'Y-m-d'];
            }
        }
        $locale = $this->localeResolver->getLocale();
        foreach ([\IntlDateFormatter::SHORT, \IntlDateFormatter::NONE] as $timeType) {
            $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, $timeType, $zone);
            $formatter->setLenient(false);
            $position = 0;
            $timestamp = $formatter->parse($value, $position);
            if ($timestamp !== false && $position === strlen($value)) {
                $date = (new \DateTimeImmutable('@' . (int) $timestamp))->setTimezone($zone);

                return [$date, $timeType !== \IntlDateFormatter::NONE];
            }
        }

        throw new LocalizedException(
            __('Enter the date as YYYY-MM-DD (optionally with HH:MM) or in your admin locale format.')
        );
    }
}
