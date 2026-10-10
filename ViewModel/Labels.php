<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\ViewModel;

use Iranimij\OpenLabel\Api\Data\ResolvedLabelInterface;
use Iranimij\OpenLabel\Api\LabelResolverInterface;
use Iranimij\Base\Model\Config\TypedReader;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Template-facing access to resolved labels, memoized per request: the listing calls getForProducts() once with
 * every product id of the page, the item templates then read from memory (02 · Architecture §4, §12).
 */
class Labels implements ArgumentInterface
{
    public const CONFIG_ENABLED = 'openlabel/general/enabled';

    /** @var array<int, ResolvedLabelInterface[]> */
    private array $resolved = [];

    /**
     * @param LabelResolverInterface $resolver
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param TypedReader $config
     */
    public function __construct(
        private readonly LabelResolverInterface $resolver,
        private readonly StoreManagerInterface $storeManager,
        private readonly HttpContext $httpContext,
        private readonly TypedReader $config
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->getBool(self::CONFIG_ENABLED);
    }

    /**
     * Resolve the labels of many products with one query; already resolved products cost nothing.
     *
     * @param int[] $productIds
     * @return array<int, ResolvedLabelInterface[]> product id => labels (only products that have labels)
     */
    public function getForProducts(array $productIds): array
    {
        if (!$this->isEnabled()) {
            return [];
        }
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        $missing = array_values(array_filter($productIds, fn (int $id): bool => !array_key_exists($id, $this->resolved)));
        if ($missing !== []) {
            $fresh = $this->resolver->getForProducts($missing, $this->storeId(), $this->customerGroupId());
            foreach ($missing as $id) {
                $this->resolved[$id] = $fresh[$id] ?? [];
            }
        }
        $result = [];
        foreach ($productIds as $id) {
            if ($this->resolved[$id] !== []) {
                $result[$id] = $this->resolved[$id];
            }
        }

        return $result;
    }

    /**
     * @param int $productId
     * @return ResolvedLabelInterface[]
     */
    public function getForProduct(int $productId): array
    {
        return $this->getForProducts([$productId])[$productId] ?? [];
    }

    /**
     * Labels of one product in one area, grouped by position in display order.
     *
     * @param int $productId
     * @param string $area listing|product
     * @return array<string, ResolvedLabelInterface[]> position => labels
     */
    public function getStacks(int $productId, string $area): array
    {
        $stacks = [];
        foreach ($this->getForProduct($productId) as $label) {
            if ($label->getPlacement()->getArea() === $area) {
                $stacks[$label->getPlacement()->getPosition()][] = $label;
            }
        }

        return $stacks;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId();
    }

    /**
     * @return int the visitor's group from the HTTP context (full page cache varies on it already)
     */
    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId();
    }

    /**
     * @return int
     */
    private function storeId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * @return int
     */
    private function customerGroupId(): int
    {
        return (int) $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
    }
}
