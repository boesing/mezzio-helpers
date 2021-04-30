<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Helper;

use ArrayObject;
use Mezzio\Helper\ContentEncoding\BadRequestResponseFactoryInterface;
use Mezzio\Helper\ContentEncoding\ProvideDecompressionStreamInterface;
use Mezzio\Helper\ContentEncoding\StrategyCollectionFactoryInterface;
use Mezzio\Helper\ContentEncodingDecompressionMiddleware;
use Mezzio\Helper\ContentEncodingDecompressionMiddlewareFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function array_map;
use function assert_options;
use const ASSERT_ACTIVE;

final class ContentEncodingDecompressionMiddlewareFactoryTest extends TestCase
{
    /**
     * @var ContentEncodingDecompressionMiddlewareFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ContentEncodingDecompressionMiddlewareFactory();
    }

    /**
     * @psalm-param non-empty-list<non-empty-string> $strategies
     * @param array<string,mixed>|null $config
     * @return MockObject&ContainerInterface
     */
    private function createContainerMockWithRequiredDependencies(array $strategies, ?array $config = null, bool $configAsArrayObject = false): MockObject
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn($config !== null);

        $servicesFromContainer = [];
        $serviceReturnValuesFromContainer = [];

        // Prepare services for the configuration request
        if ($config !== null) {
            $configFromContainer = $config;
            if ($configAsArrayObject) {
                $configFromContainer = new ArrayObject($config);
            }

            $servicesFromContainer[] = 'config';
            $serviceReturnValuesFromContainer[] = $configFromContainer;
        }

        // Prepare services for the strategy collection factory
        $servicesFromContainer[] = StrategyCollectionFactoryInterface::class;
        $strategyCollectionFactory = $this->createMock(StrategyCollectionFactoryInterface::class);
        $strategyCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->with($container, $strategies)
            ->willReturn([]);

        $serviceReturnValuesFromContainer[] = $strategyCollectionFactory;

        // Prepare services for the BadRequestResponseFactoryInterface request
        $servicesFromContainer[] = BadRequestResponseFactoryInterface::class;
        $serviceReturnValuesFromContainer[] = $this->createMock(BadRequestResponseFactoryInterface::class);

        $container
            ->expects(self::exactly(count($servicesFromContainer)))
            ->method('get')
            ->withConsecutive(...array_map(static function (string $serviceName): array {return [$serviceName];}, $servicesFromContainer))
            ->willReturnOnConsecutiveCalls(...$serviceReturnValuesFromContainer);

        return $container;
    }

    public function testWillReturnMiddlewareWithBuiltInStrategiesIfContainerHasNoConfig(): void
    {

        $container = $this->createContainerMockWithRequiredDependencies(ContentEncodingDecompressionMiddlewareFactory::BUILT_IN_DECOMPRESSION_STRATEGIES);

        ($this->factory)($container);
    }

    public function testWillReturnMiddlewareWithBuiltInStrategiesIfContainerHasConfigWithoutConfiguredStrategies(): void
    {
        $container = $this->createContainerMockWithRequiredDependencies(ContentEncodingDecompressionMiddlewareFactory::BUILT_IN_DECOMPRESSION_STRATEGIES, []);

        ($this->factory)($container);
    }

    public function testWillReturnMiddlewareWithBuiltInStrategiesIfContainerHasConfigWithConfiguredStrategiesEmpty(): void
    {
        $container = $this->createContainerMockWithRequiredDependencies(
            ContentEncodingDecompressionMiddlewareFactory::BUILT_IN_DECOMPRESSION_STRATEGIES,
            [
                ContentEncodingDecompressionMiddleware::DECOMPRESSION_PROVIDERS_CONFIGURATION_IDENTIFIER => []
            ]
        );

        ($this->factory)($container);
    }

    public function testWillReturnStrategiesFromConfiguration(): void
    {
        $mock = $this->createMock(ProvideDecompressionStreamInterface::class);
        $strategies = [get_class($mock)];
        $container = $this->createContainerMockWithRequiredDependencies(
            $strategies,
            [
                ContentEncodingDecompressionMiddleware::DECOMPRESSION_PROVIDERS_CONFIGURATION_IDENTIFIER => $strategies,
            ]
        );

        ($this->factory)($container);
    }

    public function testWontAssertInvalidConfigurationsWhenAssertionsAreDisabled(): void
    {
        assert_options(ASSERT_ACTIVE, 0);

        $strategies = ['foo' => 'bar'];
        $container = $this->createContainerMockWithRequiredDependencies(
            $strategies,
        [
                ContentEncodingDecompressionMiddleware::DECOMPRESSION_PROVIDERS_CONFIGURATION_IDENTIFIER => $strategies,
            ]
        );
        ($this->factory)($container);
    }
}
