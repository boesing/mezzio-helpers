<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Mezzio\Helper\ContentEncodingDecompressionMiddleware;
use Mezzio\Helper\Exception\MissingExtensionException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function extension_loaded;
use function gzuncompress;
use function in_array;

final class GzipDecompressionProvider implements ProvideDecompressionStreamInterface
{
    private const EXTENSION_ZLIB = 'zlib';
    private const CONTENT_ENCODING_GZIP = 'gzip';

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        if (!extension_loaded(self::EXTENSION_ZLIB)) {
            throw MissingExtensionException::fromMissingRequiredExtension(self::EXTENSION_ZLIB);
        }

        $this->streamFactory = $streamFactory;
    }

    public function canDecompress(ServerRequestInterface $request): bool
    {
        return in_array(self::CONTENT_ENCODING_GZIP, $request->getHeader(ContentEncodingDecompressionMiddleware::HEADER_CONTENT_ENCODING), true);
    }

    public function castToDecompressionStream(StreamInterface $body): StreamInterface
    {
        return new LazyDecompressionStream($body, function (StreamInterface $stream): StreamInterface {
            return $this->streamFactory->createStream(
                gzuncompress((string) $stream)
            );
        });
    }

    public function getContentEncoding(): string
    {
        return self::CONTENT_ENCODING_GZIP;
    }
}
