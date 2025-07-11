<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Test;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\AliasedFixerOption;

/**
 * @internal
 *
 * @template TFixer of AbstractFixer
 *
 * @extends AbstractFixerTestCase<TFixer>
 *
 * @author ntzm
 */
abstract class AbstractFixerWithAliasedOptionsTestCase extends AbstractFixerTestCase
{
    /**
     * @var null|TFixer
     */
    private ?AbstractFixer $fixerWithAliasedConfig = null;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->fixerWithAliasedConfig = null;
    }

    protected function doTest(string $expected, ?string $input = null, ?\SplFileInfo $file = null): void
    {
        parent::doTest($expected, $input, $file);

        if (null !== $this->fixerWithAliasedConfig) {
            $fixer = $this->fixer;
            $fixerWithAliasedConfig = $this->fixerWithAliasedConfig;

            $this->fixer = $fixerWithAliasedConfig;
            $this->fixerWithAliasedConfig = null;

            $this->doTest($expected, $input, $file);

            $this->fixerWithAliasedConfig = $fixerWithAliasedConfig;
            $this->fixer = $fixer;
        }
    }

    /**
     * @param array<string, mixed> $configuration
     */
    protected function configureFixerWithAliasedOptions(array $configuration): void
    {
        if (!$this->fixer instanceof ConfigurableFixerInterface) {
            throw new \LogicException('Fixer is not configurable.');
        }

        $this->fixer->configure($configuration);
        $options = $this->fixer->getConfigurationDefinition()->getOptions();
        $hasAliasedOptions = false;

        foreach ($options as $option) {
            if (!$option instanceof AliasedFixerOption) {
                continue;
            }

            $hasAliasedOptions = true;

            $alias = $option->getAlias();

            if (\array_key_exists($alias, $configuration)) {
                $configuration[$option->getName()] = $configuration[$alias];
                unset($configuration[$alias]);
            }
        }

        if (!$hasAliasedOptions) {
            throw new \LogicException('Fixer has no aliased options.');
        }

        $this->fixerWithAliasedConfig = clone $this->fixer;
        $this->fixerWithAliasedConfig->configure($configuration);
    }
}
