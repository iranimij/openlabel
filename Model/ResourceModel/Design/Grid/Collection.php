<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel\Design\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Designs grid rows: the design, its default-store text and how many labels use it.
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
        $usedBy = $this->getConnection()->select()
            ->from(['l' => $this->getTable('openlabel_label')], [new \Zend_Db_Expr('COUNT(*)')])
            ->where('l.design_id = main_table.design_id');

        $this->getSelect()
            ->joinLeft(
                ['ds' => $this->getTable('openlabel_design_store')],
                'ds.design_id = main_table.design_id AND ds.store_id = 0',
                ['text' => 'ds.text', 'alt_text' => 'ds.alt_text']
            )
            ->columns(['used_by' => new \Zend_Db_Expr('(' . $usedBy . ')')]);
        $this->addFilterToMap('design_id', 'main_table.design_id');
    }
}
