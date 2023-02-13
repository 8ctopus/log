<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Logger;

use Apix\Log\LogEntry;
use LogicException;
use Psr\Log\InvalidArgumentException;

/**
 * Stream log wrapper.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Stream extends AbstractLogger implements LoggerInterface
{
    /**
     * Holds the stream.
     *
     * @var resource
     */
    protected $stream;

    /**
     * Constructor.
     *
     * @param resource|string $stream the stream to append to
     * @param string           $mode
     *
     * @throws InvalidArgumentException if the stream cannot be created/opened
     */
    public function __construct($stream = 'php://stdout', string $mode = 'a')
    {
        if (!\is_resource($stream)) {
            $stream = @fopen($stream, $mode);
        }

        if (!\is_resource($stream)) {
            throw new InvalidArgumentException(sprintf(
                'The stream "%s" cannot be created or opened',
                $stream
            ));
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function write(LogEntry|string $log) : bool
    {
        if (!\is_resource($this->stream)) {
            throw new LogicException('The stream resource has been destructed too early');
        }

        if (gettype($log) === 'object') {
            $log = (string) $log . $log->formatter->separator;
        }

        return (bool) fwrite($this->stream, $log);
    }

    /**
     * {@inheritDoc}
     */
    public function close() : void
    {
        if (\is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
