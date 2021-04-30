<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Container\ContainerInterface;

final class StrategyCollectionFactory implements StrategyCollectionFactoryInterface
{

    public function create(ContainerInterface $container, array $strategyNames): array
    {
        $strategies = [];
        foreach ($strategyNames as $strategyName) {
            /** @psalm-suppress MixedAssignment */
            $strategy = $container->get($strategyName);
            assert($strategy instanceof ProvideDecompressionStreamInterface);
            $strategies[] = $strategy;
        }

        return $strategies;
    }
}
