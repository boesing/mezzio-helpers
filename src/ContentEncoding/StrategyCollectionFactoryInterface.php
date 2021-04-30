<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Container\ContainerInterface;

/**
 * @internal
 */
interface StrategyCollectionFactoryInterface
{
    /**
     * @psalm-param non-empty-list<class-string<ProvideDecompressionStreamInterface>> $strategyNames
     *
     * @psalm-return non-empty-list<ProvideDecompressionStreamInterface>
     */
    public function create(ContainerInterface $container, array $strategyNames): array;
}
