<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor;
class EnvVarProcessorTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    const TEST_CONST = 'test';
    /**
     * @dataProvider validStrings
     */
    public function testGetEnvString($value, $processed)
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(foo)', $value);
        $container->compile();
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor($container);
        $result = $processor->getEnv('string', 'foo', function () {
            $this->fail('Should not be called');
        });
        $this->assertSame($processed, $result);
    }
    public function validStrings()
    {
        return [['hello', 'hello'], ['true', 'true'], ['false', 'false'], ['null', 'null'], ['1', '1'], ['0', '0'], ['1.1', '1.1'], ['1e1', '1e1']];
    }
    /**
     * @dataProvider validBools
     */
    public function testGetEnvBool($value, $processed)
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('bool', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
        $this->assertSame($processed, $result);
    }
    public function validBools()
    {
        return [['true', \true], ['false', \false], ['null', \false], ['1', \true], ['0', \false], ['1.1', \true], ['1e1', \true]];
    }
    /**
     * @dataProvider validInts
     */
    public function testGetEnvInt($value, $processed)
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('int', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
        $this->assertSame($processed, $result);
    }
    public function validInts()
    {
        return [['1', 1], ['1.1', 1], ['1e1', 10]];
    }
    /**
     * @dataProvider invalidInts
     */
    public function testGetEnvIntInvalid($value)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Non-numeric env var');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('int', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
    }
    public function invalidInts()
    {
        return [['foo'], ['true'], ['null']];
    }
    /**
     * @dataProvider validFloats
     */
    public function testGetEnvFloat($value, $processed)
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('float', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
        $this->assertSame($processed, $result);
    }
    public function validFloats()
    {
        return [['1', 1.0], ['1.1', 1.1], ['1e1', 10.0]];
    }
    /**
     * @dataProvider invalidFloats
     */
    public function testGetEnvFloatInvalid($value)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Non-numeric env var');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('float', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
    }
    public function invalidFloats()
    {
        return [['foo'], ['true'], ['null']];
    }
    /**
     * @dataProvider validConsts
     */
    public function testGetEnvConst($value, $processed)
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('const', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
        $this->assertSame($processed, $result);
    }
    public function validConsts()
    {
        return [['Symfony\\Component\\DependencyInjection\\Tests\\EnvVarProcessorTest::TEST_CONST', self::TEST_CONST], ['E_ERROR', \E_ERROR]];
    }
    /**
     * @dataProvider invalidConsts
     */
    public function testGetEnvConstInvalid($value)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('undefined constant');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('const', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return $value;
        });
    }
    public function invalidConsts()
    {
        return [['Symfony\\Component\\DependencyInjection\\Tests\\EnvVarProcessorTest::UNDEFINED_CONST'], ['UNDEFINED_CONST']];
    }
    public function testGetEnvBase64()
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('base64', 'foo', function ($name) {
            $this->assertSame('foo', $name);
            return \base64_encode('hello');
        });
        $this->assertSame('hello', $result);
    }
    public function testGetEnvJson()
    {
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $result = $processor->getEnv('json', 'foo', function ($name) {
            $this->assertSame('foo', $name);
            return \json_encode([1]);
        });
        $this->assertSame([1], $result);
    }
    public function testGetEnvInvalidJson()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Syntax error');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('json', 'foo', function ($name) {
            $this->assertSame('foo', $name);
            return 'invalid_json';
        });
    }
    /**
     * @dataProvider otherJsonValues
     */
    public function testGetEnvJsonOther($value)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Invalid JSON env var');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('json', 'foo', function ($name) use($value) {
            $this->assertSame('foo', $name);
            return \json_encode($value);
        });
    }
    public function otherJsonValues()
    {
        return [[1], [1.1], [\true], [\false]];
    }
    public function testGetEnvUnknown()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Unsupported env var prefix');
        $processor = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessor(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container());
        $processor->getEnv('unknown', 'foo', function ($name) {
            $this->assertSame('foo', $name);
            return 'foo';
        });
    }
}
