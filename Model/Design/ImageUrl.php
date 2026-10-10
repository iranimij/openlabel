<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Design;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Public URL of a design image stored under pub/media/openlabel/.
 */
class ImageUrl
{
    public const MEDIA_DIR = 'openlabel';

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param string|null $imagePath path relative to pub/media/openlabel, e.g. designs/sale.svg
     * @return string|null
     */
    public function get(?string $imagePath): ?string
    {
        if ($imagePath === null || $imagePath === '') {
            return null;
        }
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::MEDIA_DIR . '/' . ltrim($imagePath, '/');
    }
}
