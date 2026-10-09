<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;

/**
 * The variable processors, keyed by code. Extend through di.xml:
 * <type name="Iranimij\OpenLabel\Model\Variable\Pool"><arguments><argument name="processors" xsi:type="array">...
 *
 * @api
 */
class Pool
{
    /** @var array<string, VariableProcessorInterface> */
    private array $processors = [];

    /**
     * @param VariableProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        foreach ($processors as $processor) {
            if ($processor instanceof VariableProcessorInterface) {
                $this->processors[strtoupper($processor->getCode())] = $processor;
            }
        }
    }

    /**
     * @param string $code
     * @return VariableProcessorInterface|null
     */
    public function get(string $code): ?VariableProcessorInterface
    {
        return $this->processors[strtoupper($code)] ?? null;
    }

    /**
     * @return array<string, VariableProcessorInterface>
     */
    public function getAll(): array
    {
        return $this->processors;
    }
}
