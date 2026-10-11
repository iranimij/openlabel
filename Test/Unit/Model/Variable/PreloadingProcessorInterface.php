<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Variable;

use Iranimij\OpenLabel\Api\VariablePreloadInterface;
use Iranimij\OpenLabel\Api\VariableProcessorInterface;

/**
 * Test double type: a processor that also preloads.
 */
interface PreloadingProcessorInterface extends VariableProcessorInterface, VariablePreloadInterface
{
}
