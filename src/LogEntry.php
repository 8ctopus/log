<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Apix\Log\Format\Standard;

/**
 * Describes a log Entry.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class LogEntry
{
    /**
     * Holds this log timestamp.
     *
     * @var int
     */
    public int $timestamp;

    /**
     * Holds this log name.
     *
     * @var string
     */
    public string $name;

    /**
     * Holds this log level code.
     *
     * @var int
     */
    public int $levelCode;

    /**
     * Holds this log message.
     *
     * @var string
     */
    public string $message;

    /**
     * Holds this log context.
     *
     * @var mixed[]
     */
    public array $context;

    /**
     * Constructor.
     *
     * @param int|string $level   the level
     * @param string     $message the message for this log entry
     * @param mixed[]    $context the contexts for this log entry
     */
    public function __construct(string|int $level, string $message, array $context = [])
    {
        $this->timestamp = time();

        if (\gettype($level) === 'string') {
            $this->name = $level;
            $this->levelCode = Logger::getLevelCode($level);
        } else {
            $this->name = Logger::getLevelName($level);
            $this->levelCode = $level;
        }

        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Returns the formatted string for this log entry.
     *
     * @return string
     */
    public function __toString() : string
    {
        $format = new Standard();
        return $format->format($this);
    }
}
