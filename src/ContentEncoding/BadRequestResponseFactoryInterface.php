<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Http\Message\ResponseInterface;

interface BadRequestResponseFactoryInterface
{
    /**
     * Should create a response based on the informations provided by the strategies.
     * So in case you want to provide a response to the client that the used `Content-Encoding` header contains
     * invalid value.
     *
     * @param array<int,ProvideDecompressionStreamInterface>  $strategies
     * @param array<int,string> $processedCompressions
     *
     * @psalm-param list<ProvideDecompressionStreamInterface> $strategies
     * @psalm-param list<non-empty-string> $processedCompressions
     */
    public function create(array $strategies, array $processedCompressions): ResponseInterface;
}
