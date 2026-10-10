<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Console;

use Iranimij\OpenLabel\Model\Css\Regenerator;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * openlabel:css:regenerate: rebuild the generated stylesheet of every store view.
 */
class CssRegenerate extends Command
{
    /**
     * @param Regenerator $regenerator
     * @param State $appState
     */
    public function __construct(
        private readonly Regenerator $regenerator,
        private readonly State $appState
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('openlabel:css:regenerate')
            ->setDescription('Rebuild the OpenLabel stylesheet (pub/media/openlabel/<store>/openlabel.<hash>.css)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $paths = $this->appState->emulateAreaCode(Area::AREA_GLOBAL, fn (): array => $this->regenerator->regenerateAll());
            foreach ($paths as $storeId => $path) {
                $output->writeln(sprintf('<info>Store %d: pub/media/%s</info>', $storeId, $path));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
