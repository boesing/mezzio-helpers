<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @internal
 */
final class GzipDecompressionProviderFactory
{
    public function __invoke(ContainerInterface $container): GzipDecompressionProvider
    {
        return new GzipDecompressionProvider($container->get(StreamFactoryInterface::class));
    }
}
