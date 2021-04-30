<?php
/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Mezzio\Helper\ContentEncoding;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class LazyDecompressionStream implements StreamInterface
{

    /**
     * @var StreamInterface
     */
    private $compressedStream;

    /**
     * @var StreamInterface|null
     */
    private $decompressedStream;

    /**
     * @var callable(StreamInterface):StreamInterface
     */
    private $decompressionCallback;

    /**
     * @param callable $decompressionCallback
     * @psalm-param callable(StreamInterface):StreamInterface $decompressionCallback
     */
    public function __construct(StreamInterface $compressedStream, callable $decompressionCallback)
    {
        $this->compressedStream = $compressedStream;
        $this->decompressionCallback = $decompressionCallback;
    }

    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    public function isReadable()
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream->isReadable();
        }

        return $this->compressedStream->isReadable();
    }

    public function isSeekable()
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream->isSeekable();
        }

        return $this->compressedStream->isSeekable();
    }

    public function rewind(): void
    {
        if ($this->decompressedStream) {
            $this->decompressedStream->rewind();
        }

        $this->compressedStream->rewind();
    }

    public function getContents()
    {
        $stream = $this->decompress();

        return $stream->getContents();
    }

    private function decompress(): StreamInterface
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream;
        }

        $this->decompressedStream = ($this->decompressionCallback)($this->compressedStream);
        return $this->decompressedStream;
    }

    public function close()
    {
        if ($this->decompressedStream) {
            $this->decompressedStream->close();
        }

        $this->compressedStream->close();
    }

    public function detach()
    {
        $detached = $this->compressedStream->detach();
        if ($this->decompressedStream) {
            $detached = $this->decompressedStream->detach();
        }

        return $detached;
    }

    public function getSize()
    {
        $decompressedStream = $this->decompress();

        return $decompressedStream->getSize();
    }

    public function tell()
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream->tell();
        }

        return $this->compressedStream->tell();
    }

    public function eof()
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream->eof();
        }

        return $this->compressedStream->eof();
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        $stream = $this->decompress();
        $stream->seek($offset, $whence);
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new RuntimeException('Stream is not writable!');
    }

    public function read($length)
    {
        $decompressed = $this->decompress();

        return $decompressed->read($length);
    }

    public function getMetadata($key = null)
    {
        if ($this->decompressedStream) {
            return $this->decompressedStream->getMetadata($key);
        }

        return $this->compressedStream->getMetadata($key);
    }
}
