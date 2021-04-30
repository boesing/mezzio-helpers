<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\Exception;

final class MissingExtensionException extends RuntimeException
{
    private function __construct(string $extension)
    {
        parent::__construct(sprintf('Required extension "%s" is either missing or not loaded.', $extension));
    }

    /**
     * @psalm-param non-empty-string $extension
     */
    public static function fromMissingRequiredExtension(string $extension): self
    {
        return new self($extension);
    }
}
