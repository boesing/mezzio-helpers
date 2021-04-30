<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Helper;

use ArrayAccess;
use Mezzio\Helper\ContentEncoding\BadRequestResponseFactoryInterface;
use Mezzio\Helper\ContentEncoding\GzipDecompressionProvider;
use Mezzio\Helper\ContentEncoding\ProvideDecompressionStreamInterface;
use Mezzio\Helper\ContentEncoding\StrategyCollectionFactoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;
use function assert_options;
use const ASSERT_ACTIVE;

/**
 * @internal
 */
final class ContentEncodingDecompressionMiddlewareFactory
{
    public const BUILT_IN_DECOMPRESSION_STRATEGIES = [
        GzipDecompressionProvider::class,
    ];
    private const STRATEGY_ASSERTION_ERROR = <<<'TXT'
    Strategies must be configured as a list of class names which implement the `ProvideDecompressionStreamInterface'
    TXT;

    public function __invoke(ContainerInterface $container): ContentEncodingDecompressionMiddleware
    {
        $strategies = $this->createStreamDecompressionStrategies($container);

        return new ContentEncodingDecompressionMiddleware(
            $strategies,
            $container->get(BadRequestResponseFactoryInterface::class)
        );
    }

    /**
     * @psalm-return non-empty-list<ProvideDecompressionStreamInterface>
     */
    private function createStreamDecompressionStrategies(ContainerInterface $container): array
    {
        if (!$container->has('config')) {
            return $this->createDefaultStreamDecompressionStrategies($container);
        }

        $config = $container->get('config');
        assert(
            is_array($config) || $config instanceof ArrayAccess,
            'Configuration fetched from the PSR-11 container must be be an array or implement `ArrayAccess`'
        );
        $strategies = $config[ContentEncodingDecompressionMiddleware::DECOMPRESSION_PROVIDERS_CONFIGURATION_IDENTIFIER] ?? [];

        if ($strategies === []) {
            return $this->createDefaultStreamDecompressionStrategies($container);
        }
        $this->assertStrategiesMatchRequirements($strategies);

        return $this->createStreamDecompressionStrategiesByName($container, $strategies);
    }

    /**
     * @psalm-return non-empty-list<ProvideDecompressionStreamInterface>
     */
    private function createDefaultStreamDecompressionStrategies(ContainerInterface $container): array
    {
        return $this->createStreamDecompressionStrategiesByName($container, self::BUILT_IN_DECOMPRESSION_STRATEGIES);
    }

    /**
     * @psalm-param non-empty-list<class-string<ProvideDecompressionStreamInterface>> $strategyNames
     *
     * @psalm-return non-empty-list<ProvideDecompressionStreamInterface>
     */
    private function createStreamDecompressionStrategiesByName(
        ContainerInterface $container,
        array $strategyNames
    ) {
        $factory = $container->get(StrategyCollectionFactoryInterface::class);
        return $factory->create($container, $strategyNames);
    }

    /**
     * @param mixed $strategies
     *
     * @psalm-assert non-empty-list<class-string<ProvideDecompressionStreamInterface>> $strategies
     */
    private function assertStrategiesMatchRequirements($strategies): void
    {
        if (assert_options(ASSERT_ACTIVE) === 0) {
            return;
        }

        Assert::isNonEmptyList($strategies, self::STRATEGY_ASSERTION_ERROR);
        Assert::allStringNotEmpty($strategies, self::STRATEGY_ASSERTION_ERROR);
        Assert::allImplementsInterface($strategies, ProvideDecompressionStreamInterface::class, self::STRATEGY_ASSERTION_ERROR);
    }
}
