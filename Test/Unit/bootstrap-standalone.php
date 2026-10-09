<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

/*
 * Unit-test bootstrap for the coverage job, where only the module and its Composer dependencies are installed
 * (no Magento application, so no app/functions.php): autoload plus the translation helper the code relies on.
 */
require __DIR__ . '/../../vendor/autoload.php';

if (!function_exists('__')) {
    /**
     * @param mixed ...$argc
     * @return \Magento\Framework\Phrase
     */
    function __(...$argc): \Magento\Framework\Phrase
    {
        $text = array_shift($argc);
        if (!empty($argc) && is_array($argc[0])) {
            $argc = $argc[0];
        }

        return new \Magento\Framework\Phrase((string) $text, $argc);
    }
}
