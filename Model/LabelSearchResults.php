<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model;

use Iranimij\OpenLabel\Api\Data\LabelSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class LabelSearchResults extends SearchResults implements LabelSearchResultsInterface
{
}
