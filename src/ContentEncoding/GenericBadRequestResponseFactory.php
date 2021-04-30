<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class GenericBadRequestResponseFactory implements BadRequestResponseFactoryInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function create(array $strategies, array $processedCompressions): ResponseInterface
    {
        return $this->responseFactory->createResponse(StatusCodeInterface::STATUS_BAD_REQUEST);
    }
}
