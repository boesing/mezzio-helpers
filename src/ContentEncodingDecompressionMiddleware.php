<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper;

use Mezzio\Helper\ContentEncoding\BadRequestResponseFactoryInterface;
use Mezzio\Helper\ContentEncoding\ProvideDecompressionStreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContentEncodingDecompressionMiddleware implements MiddlewareInterface
{
    public const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    public const DECOMPRESSION_PROVIDERS_CONFIGURATION_IDENTIFIER = 'content-encoding-decompression-providers';

    /**
     * @var array<int,ProvideDecompressionStreamInterface>
     * @psalm-var list<ProvideDecompressionStreamInterface>
     */
    private $strategies;

    /**
     * @var BadRequestResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @param array<int,ProvideDecompressionStreamInterface> $strategies
     * @psalm-param non-empty-list<ProvideDecompressionStreamInterface> $strategies
     * @no-named-arguments
     */
    public function __construct(array $strategies, BadRequestResponseFactoryInterface $responseFactory)
    {
        $this->strategies = $strategies;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->hasHeader(self::HEADER_CONTENT_ENCODING)) {
            return $handler->handle($request);
        }

        $processedCompressions = [];

        foreach ($this->strategies as $strategy) {
            if (!$strategy->canDecompress($request)) {
                continue;
            }

            $processedCompressions[] = $strategy->getContentEncoding();

            $request = $request->withBody(
                $strategy->castToDecompressionStream(
                    $request->getBody()
                )
            );
        }

        if ($processedCompressions !== $request->getHeader(self::HEADER_CONTENT_ENCODING)) {
            return $this->responseFactory->create($this->strategies, $processedCompressions);
        }

        return $handler->handle($request);
    }
}
