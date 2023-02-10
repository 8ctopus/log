<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Exception;
use Psr\Log\LoggerInterface;
use stdClass;

abstract class TestCase implements LoggerInterface
{
    protected $dest = 'build/apix-unit-test-logger.log';

    public static function normalizeLogs($logs)
    {
        $normalize = function ($log) {
            return preg_replace_callback(
                '{^\[.+\] (\w+) (.+)?}',
                function ($match) {
                    return strtolower($match[1]) . ' '
                    . (
                        isset($match[2]) ? $match[2] : null
                    );
                },
                $log
            );
        };

        return array_map($normalize, $logs);
    }

    /**
     * This must return the log messages in order with a simple formatting: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo"
     *
     * @return string[]
     */
    public function getLogs() : array
    {
        return self::normalizeLogs(file($this->dest, FILE_IGNORE_NEW_LINES));
    }

    public function providerMessagesAndContextes()
    {
        $obj = new stdClass();
        $obj->baz = 'biz';
        $obj->nested = new stdClass();
        $obj->nested->buz = 'bez';

        return [
            ['null', null, ''],
            ['bool1', true, '[bool: 1]'],
            ['bool2', false, '[bool: 0]'],
            ['string', 'Foo', 'Foo'],
            ['int', 0, '0'],
            ['float', 0.5, '0.5'],
            ['resource', fopen('php://memory', 'r'), '[type: resource]'],

            // objects
            ['obj__toString', new DummyTest(), '__toString!'],
            ['obj_stdClass', new stdClass(), '{}'],
            ['obj_instance', $obj, '{"baz":"biz","nested":{"buz":"bez"}}'],

            // nested arrays...
            ['nested_values', ['foo', 'bar'], '["foo","bar"]'],
            ['nested_asso', ['foo' => 1, 'bar' => '2'], '{"foo":1,"bar":"2"}'],
            ['nested_object', [new DummyTest()], '[{"foo":"bar"}]'],
            ['nested_unicode', ['ƃol-xᴉdɐ'], '["\u0183ol-x\u1d09d\u0250"]'],
        ];
    }

    /**
     * @dataProvider providerMessagesAndContextes
     *
     * @param mixed $msg
     * @param mixed $context
     * @param mixed $exp
     */
    public function testMessageWithContext($msg, $context, $exp)
    {
        $this->getLogger()->alert('{' . $msg . '}', [$msg => $context]);

        $this->assertEquals(['alert ' . $exp], $this->getLogs());
    }

    /**
     * @dataProvider providerMessagesAndContextes
     *
     * @param mixed $msg
     * @param mixed $context
     * @param mixed $exp
     */
    public function testContextIsPermutted($msg, $context, $exp)
    {
        $this->getLogger()->notice($context);

        $this->assertEquals(['notice ' . $exp], $this->getLogs());
    }

    public function testContextIsAnException()
    {
        $this->getLogger()->critical(new Exception('Boo!'));

        $logs = $this->getLogs();

        $prefix = version_compare(PHP_VERSION, '7.0.0-dev', '>=')
                ? 'critical Exception: Boo! in '
                : "critical exception 'Exception' with message 'Boo!' in ";

        $this->assertStringStartsWith(
            $prefix,
            $logs[0]
        );
    }
}

class DummyTest
{
    public $foo = 'bar';
    protected $foo2 = 'bar2';

    public function __toString()
    {
        return '__toString!';
    }
}
