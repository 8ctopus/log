<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\Logger;
use Psr\Log\InvalidArgumentException;

/**
 * @internal
 *
 */
final class FileTest extends \PHPUnit\Framework\TestCase
{
    protected string $dest = 'test';

    protected function tearDown() : void
    {
        if (file_exists($this->dest)) {
            chmod($this->dest, 0777);
            unlink($this->dest);
        }
    }

    public function testThrowsInvalidArgumentExceptionWhenFileCannotBeCreated() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log file "" cannot be created');
        $this->expectExceptionCode(1);
        new Logger\File('');
    }

    public function testThrowsInvalidArgumentExceptionWhenNotWritable() : void
    {
        touch($this->dest);
        chmod($this->dest, 0000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Log file \"{$this->dest}\" is not writable");
        $this->expectExceptionCode(2);

        new Logger\File($this->dest);
    }
}
