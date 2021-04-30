<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @internal
 */
final class GenericBadRequestResponseFactoryFactory
{
    public function __invoke(ContainerInterface $container): GenericBadRequestResponseFactory
    {
        return new GenericBadRequestResponseFactory($container->get(ResponseFactoryInterface::class));
    }
}
