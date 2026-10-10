<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel\Label\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Labels grid rows: the label plus its design (for the thumbnail), the placements summary, the matched product
 * count from the index and whether it is active right now. Everything in one SELECT.
 */
class Collection extends SearchResult
{
    /**
     * Joins the extra grid columns. No return value: the parent's signature differs across 2.4.7–2.4.9.
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $connection = $this->getConnection();
        $now = $connection->quote(gmdate('Y-m-d H:i:s'));

        $matched = $connection->select()
            ->from(['i' => $this->getTable('openlabel_index')], [new \Zend_Db_Expr('COUNT(DISTINCT i.product_id)')])
            ->where('i.label_id = main_table.label_id');
        $placements = $connection->select()
            ->from(
                ['p' => $this->getTable('openlabel_placement')],
                [new \Zend_Db_Expr(
                    "GROUP_CONCAT(CONCAT(p.area, ':', p.position) ORDER BY p.sort_order, p.placement_id SEPARATOR ',')"
                )]
            )
            ->where('p.label_id = main_table.label_id');

        $this->getSelect()
            ->joinLeft(
                ['d' => $this->getTable('openlabel_design')],
                'd.design_id = main_table.design_id',
                [
                    'design_name' => 'd.name',
                    'design_type' => 'd.type',
                    'design_shape' => 'd.shape',
                    'design_bg_color' => 'd.bg_color',
                    'design_text_color' => 'd.text_color',
                    'design_border_color' => 'd.border_color',
                    'design_image_path' => 'd.image_path',
                ]
            )
            ->joinLeft(
                ['ds' => $this->getTable('openlabel_design_store')],
                'ds.design_id = main_table.design_id AND ds.store_id = 0',
                ['design_text' => 'ds.text', 'design_alt_text' => 'ds.alt_text']
            )
            ->columns([
                'matched_count' => new \Zend_Db_Expr('(' . $matched . ')'),
                'placements' => new \Zend_Db_Expr('(' . $placements . ')'),
                'active_now' => $this->activeNowExpression($now),
            ]);

        foreach (['label_id', 'name', 'status', 'priority', 'design_id', 'valid_from', 'valid_to', 'created_at',
                     'updated_at'] as $field) {
            $this->addFilterToMap($field, 'main_table.' . $field);
        }
    }

    /**
     * Adds the virtual filters "active now" and "store view" (store_ids is a CSV where empty means all).
     *
     * @param string|array<int, string> $field
     * @param mixed $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'active_now') {
            $value = is_array($condition) ? reset($condition) : $condition;
            $this->getSelect()->where(
                $this->activeNowExpression($this->getConnection()->quote(gmdate('Y-m-d H:i:s'))) . ' = ?',
                (int) $value
            );

            return $this;
        }
        if ($field === 'store_ids') {
            $value = is_array($condition) ? reset($condition) : $condition;
            $this->getSelect()->where(
                "main_table.store_ids IS NULL OR main_table.store_ids = '' OR FIND_IN_SET(?, main_table.store_ids)",
                (string) (int) $value
            );

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $quotedNow
     * @return \Zend_Db_Expr
     */
    private function activeNowExpression(string $quotedNow): \Zend_Db_Expr
    {
        return new \Zend_Db_Expr(
            '(CASE WHEN main_table.status = 1'
            . ' AND (main_table.valid_from IS NULL OR main_table.valid_from <= ' . $quotedNow . ')'
            . ' AND (main_table.valid_to IS NULL OR main_table.valid_to >= ' . $quotedNow . ')'
            . ' THEN 1 ELSE 0 END)'
        );
    }
}
