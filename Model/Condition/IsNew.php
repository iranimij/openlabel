<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rule\Model\Condition\Context;

/**
 * Is new: inside the product's news_from/news_to window (default-scope values), or at most N days since created/updated.
 * All three attributes change with time, so labels using them are reindexed by the daily cron.
 */
class IsNew extends AbstractBuiltIn
{
    public const NEWS_DATES = 'news_dates';
    public const DAYS_SINCE_CREATED = 'days_since_created';
    public const DAYS_SINCE_UPDATED = 'days_since_updated';

    private const FROM_ALIAS = 'ol_news_from';
    private const TO_ALIAS = 'ol_news_to';

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param EavConfig $eavConfig
     * @param ProductResource $productResource
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly DateTime $dateTime,
        private readonly EavConfig $eavConfig,
        private readonly ProductResource $productResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function attributeOptions(): array
    {
        return [
            self::NEWS_DATES => __('Within "Set Product as New" dates'),
            self::DAYS_SINCE_CREATED => __('Days since created'),
            self::DAYS_SINCE_UPDATED => __('Days since updated'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function booleanAttributes(): array
    {
        return [self::NEWS_DATES];
    }

    /**
     * @inheritDoc
     */
    public function isDateRelative(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getTablesToJoin()
    {
        if ((string) $this->getAttribute() !== self::NEWS_DATES) {
            return [];
        }
        $linkField = $this->linkField();

        return [
            self::FROM_ALIAS => $this->datetimeJoin(self::FROM_ALIAS, 'news_from_date', $linkField),
            self::TO_ALIAS => $this->datetimeJoin(self::TO_ALIAS, 'news_to_date', $linkField),
        ];
    }

    /**
     * The core Sql Builder accepts expressions only as Zend_Db_Expr instances, hence the widened return type.
     *
     * @return \Zend_Db_Expr
     * @phpstan-ignore method.childReturnType, method.childReturnType (AbstractCondition and ConditionInterface both declare string)
     */
    public function getMappedSqlField()
    {
        $now = "'" . $this->dateTime->gmtDate() . "'";

        return match ((string) $this->getAttribute()) {
            self::DAYS_SINCE_CREATED => new \Zend_Db_Expr("DATEDIFF($now, e.created_at)"),
            self::DAYS_SINCE_UPDATED => new \Zend_Db_Expr("DATEDIFF($now, e.updated_at)"),
            default => new \Zend_Db_Expr(sprintf(
                'IF(%1$s.value IS NOT NULL AND %1$s.value <= %3$s AND (%2$s.value IS NULL OR %2$s.value >= %3$s), 1, 0)',
                self::FROM_ALIAS,
                self::TO_ALIAS,
                $now
            )),
        };
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        $now = $this->dateTime->gmtDate();
        switch ((string) $this->getAttribute()) {
            case self::DAYS_SINCE_CREATED:
                return $this->daysSince($product->getData('created_at'));
            case self::DAYS_SINCE_UPDATED:
                return $this->daysSince($product->getData('updated_at') ?? $product->getData('created_at'));
            default:
                $from = $product->getData('news_from_date');
                $to = $product->getData('news_to_date');
                if ($from === null || $from === '') {
                    return 0;
                }

                return ($from <= $now && ($to === null || $to === '' || $to >= $now)) ? 1 : 0;
        }
    }

    /**
     * @param mixed $date
     * @return int|null whole days between the date and now
     */
    private function daysSince(mixed $date): ?int
    {
        if ($date === null || $date === '') {
            return null;
        }
        $seconds = $this->dateTime->gmtTimestamp() - $this->dateTime->timestamp((string) $date);

        return (int) floor($seconds / 86400);
    }

    /**
     * @param string $alias
     * @param string $attributeCode
     * @param string $linkField
     * @return array<string, mixed>
     */
    private function datetimeJoin(string $alias, string $attributeCode, string $linkField): array
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
        $attributeId = $attribute ? (int) $attribute->getId() : 0;

        return [
            'name' => 'catalog_product_entity_datetime',
            'condition' => sprintf(
                '%1$s.%2$s = e.%2$s AND %1$s.attribute_id = %3$d AND %1$s.store_id = 0',
                $alias,
                $linkField,
                $attributeId
            ),
            'columns' => [],
        ];
    }

    /**
     * @return string entity_id on Open Source, row_id on Adobe Commerce staging
     */
    private function linkField(): string
    {
        $linkField = $this->productResource->getLinkField();

        return is_string($linkField) && $linkField !== '' ? $linkField : 'entity_id';
    }
}
