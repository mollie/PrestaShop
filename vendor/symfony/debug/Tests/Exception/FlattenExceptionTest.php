<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Debug\Tests\Exception;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Debug\Exception\FlattenException;
use MolliePrefix\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\GoneHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use MolliePrefix\Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
class FlattenExceptionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testStatusCode()
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \RuntimeException(), 403);
        $this->assertEquals('403', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \RuntimeException());
        $this->assertEquals('500', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\NotFoundHttpException());
        $this->assertEquals('404', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('Basic realm="My Realm"'));
        $this->assertEquals('401', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\BadRequestHttpException());
        $this->assertEquals('400', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException());
        $this->assertEquals('406', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\ConflictHttpException());
        $this->assertEquals('409', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException(['POST']));
        $this->assertEquals('405', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException());
        $this->assertEquals('403', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\GoneHttpException());
        $this->assertEquals('410', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException());
        $this->assertEquals('411', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException());
        $this->assertEquals('412', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException());
        $this->assertEquals('428', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException());
        $this->assertEquals('503', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException());
        $this->assertEquals('429', $flattened->getStatusCode());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException());
        $this->assertEquals('415', $flattened->getStatusCode());
        if (\class_exists(\MolliePrefix\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException::class)) {
            $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException());
            $this->assertEquals('400', $flattened->getStatusCode());
        }
    }
    public function testHeadersForHttpException()
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException(['POST']));
        $this->assertEquals(['Allow' => 'POST'], $flattened->getHeaders());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('Basic realm="My Realm"'));
        $this->assertEquals(['WWW-Authenticate' => 'Basic realm="My Realm"'], $flattened->getHeaders());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException('Fri, 31 Dec 1999 23:59:59 GMT'));
        $this->assertEquals(['Retry-After' => 'Fri, 31 Dec 1999 23:59:59 GMT'], $flattened->getHeaders());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException(120));
        $this->assertEquals(['Retry-After' => 120], $flattened->getHeaders());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException('Fri, 31 Dec 1999 23:59:59 GMT'));
        $this->assertEquals(['Retry-After' => 'Fri, 31 Dec 1999 23:59:59 GMT'], $flattened->getHeaders());
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create(new \MolliePrefix\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException(120));
        $this->assertEquals(['Retry-After' => 120], $flattened->getHeaders());
    }
    /**
     * @dataProvider flattenDataProvider
     */
    public function testFlattenHttpException(\Exception $exception)
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $flattened2 = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $flattened->setPrevious($flattened2);
        $this->assertEquals($exception->getMessage(), $flattened->getMessage(), 'The message is copied from the original exception.');
        $this->assertEquals($exception->getCode(), $flattened->getCode(), 'The code is copied from the original exception.');
        $this->assertInstanceOf($flattened->getClass(), $exception, 'The class is set to the class of the original exception');
    }
    /**
     * @dataProvider flattenDataProvider
     */
    public function testPrevious(\Exception $exception)
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $flattened2 = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $flattened->setPrevious($flattened2);
        $this->assertSame($flattened2, $flattened->getPrevious());
        $this->assertSame([$flattened2], $flattened->getAllPrevious());
    }
    /**
     * @requires PHP 7.0
     */
    public function testPreviousError()
    {
        $exception = new \Exception('test', 123, new \ParseError('Oh noes!', 42));
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception)->getPrevious();
        $this->assertEquals('Parse error: Oh noes!', $flattened->getMessage(), 'The message is copied from the original exception.');
        $this->assertEquals(42, $flattened->getCode(), 'The code is copied from the original exception.');
        $this->assertEquals('MolliePrefix\\Symfony\\Component\\Debug\\Exception\\FatalThrowableError', $flattened->getClass(), 'The class is set to the class of the original exception');
    }
    /**
     * @dataProvider flattenDataProvider
     */
    public function testLine(\Exception $exception)
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $this->assertSame($exception->getLine(), $flattened->getLine());
    }
    /**
     * @dataProvider flattenDataProvider
     */
    public function testFile(\Exception $exception)
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $this->assertSame($exception->getFile(), $flattened->getFile());
    }
    /**
     * @dataProvider flattenDataProvider
     */
    public function testToArray(\Exception $exception)
    {
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $flattened->setTrace([], 'foo.php', 123);
        $this->assertEquals([['message' => 'test', 'class' => 'Exception', 'trace' => [['namespace' => '', 'short_class' => '', 'class' => '', 'type' => '', 'function' => '', 'file' => 'foo.php', 'line' => 123, 'args' => []]]]], $flattened->toArray());
    }
    public function flattenDataProvider()
    {
        return [[new \Exception('test', 123)]];
    }
    public function testArguments()
    {
        if (\PHP_VERSION_ID >= 70400) {
            $this->markTestSkipped('PHP 7.4 removes arguments from exception traces.');
        }
        $dh = \opendir(__DIR__);
        $fh = \tmpfile();
        $incomplete = \unserialize('O:14:"BogusTestClass":0:{}');
        $exception = $this->createException([(object) ['foo' => 1], new \MolliePrefix\Symfony\Component\HttpKernel\Exception\NotFoundHttpException(), $incomplete, $dh, $fh, function () {
        }, [1, 2], ['foo' => 123], null, \true, \false, 0, 0.0, '0', '', \INF, \NAN]);
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $trace = $flattened->getTrace();
        $args = $trace[1]['args'];
        $array = $args[0][1];
        \closedir($dh);
        \fclose($fh);
        $i = 0;
        $this->assertSame(['object', 'stdClass'], $array[$i++]);
        $this->assertSame(['object', 'MolliePrefix\\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException'], $array[$i++]);
        $this->assertSame(['incomplete-object', 'BogusTestClass'], $array[$i++]);
        $this->assertSame(['resource', \defined('HHVM_VERSION') ? 'Directory' : 'stream'], $array[$i++]);
        $this->assertSame(['resource', 'stream'], $array[$i++]);
        $args = $array[$i++];
        $this->assertSame($args[0], 'object');
        $this->assertTrue('Closure' === $args[1] || \is_subclass_of($args[1], '\\Closure'), 'Expect object class name to be Closure or a subclass of Closure.');
        $this->assertSame(['array', [['integer', 1], ['integer', 2]]], $array[$i++]);
        $this->assertSame(['array', ['foo' => ['integer', 123]]], $array[$i++]);
        $this->assertSame(['null', null], $array[$i++]);
        $this->assertSame(['boolean', \true], $array[$i++]);
        $this->assertSame(['boolean', \false], $array[$i++]);
        $this->assertSame(['integer', 0], $array[$i++]);
        $this->assertSame(['float', 0.0], $array[$i++]);
        $this->assertSame(['string', '0'], $array[$i++]);
        $this->assertSame(['string', ''], $array[$i++]);
        $this->assertSame(['float', \INF], $array[$i++]);
        // assertEquals() does not like NAN values.
        $this->assertEquals('float', $array[$i][0]);
        $this->assertNan($array[$i][1]);
    }
    public function testRecursionInArguments()
    {
        if (\PHP_VERSION_ID >= 70400) {
            $this->markTestSkipped('PHP 7.4 removes arguments from exception traces.');
        }
        $a = null;
        $a = ['foo', [2, &$a]];
        $exception = $this->createException($a);
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $trace = $flattened->getTrace();
        $this->assertStringContainsString('*DEEP NESTED ARRAY*', \serialize($trace));
    }
    public function testTooBigArray()
    {
        if (\PHP_VERSION_ID >= 70400) {
            $this->markTestSkipped('PHP 7.4 removes arguments from exception traces.');
        }
        $a = [];
        for ($i = 0; $i < 20; ++$i) {
            for ($j = 0; $j < 50; ++$j) {
                for ($k = 0; $k < 10; ++$k) {
                    $a[$i][$j][$k] = 'value';
                }
            }
        }
        $a[20] = 'value';
        $a[21] = 'value1';
        $exception = $this->createException($a);
        $flattened = \MolliePrefix\Symfony\Component\Debug\Exception\FlattenException::create($exception);
        $trace = $flattened->getTrace();
        $this->assertSame($trace[1]['args'][0], ['array', ['array', '*SKIPPED over 10000 entries*']]);
        $serializeTrace = \serialize($trace);
        $this->assertStringContainsString('*SKIPPED over 10000 entries*', $serializeTrace);
        $this->assertStringNotContainsString('*value1*', $serializeTrace);
    }
    private function createException($foo)
    {
        return new \Exception();
    }
}
