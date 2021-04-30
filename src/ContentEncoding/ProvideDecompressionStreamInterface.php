<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface ProvideDecompressionStreamInterface
{
    public function canDecompress(ServerRequestInterface $request): bool;

    public function castToDecompressionStream(StreamInterface $body): StreamInterface;

    /**
     * @psalm-return non-empty-string
     */
    public function getContentEncoding(): string;
}
