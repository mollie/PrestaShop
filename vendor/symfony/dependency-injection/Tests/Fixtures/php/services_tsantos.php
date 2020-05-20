<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use _PhpScoper5ea00cc67502b\Symfony\Component\Stopwatch\Stopwatch;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\Encoder\JsonEncoder;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\EventDispatcher\EventDispatcher;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\EventEmitterSerializer;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\CollectionNormalizer;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\JsonNormalizer;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\ObjectNormalizer;
use _PhpScoper5ea00cc67502b\TSantos\Serializer\NormalizerRegistry;
use _PhpScoper5ea00cc67502b\TSantos\SerializerBundle\EventListener\StopwatchListener;
use _PhpScoper5ea00cc67502b\TSantos\SerializerBundle\Serializer\CircularReferenceHandler;
use function class_alias;
use function sprintf;
use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->normalizedIds = ['_PhpScoper5ea00cc67502b\\tsantos\\serializer\\serializerinterface' => '_PhpScoper5ea00cc67502b\\TSantos\\Serializer\\SerializerInterface'];
        $this->methodMap = ['tsantos_serializer' => 'getTsantosSerializerService'];
        $this->aliases = ['_PhpScoper5ea00cc67502b\\TSantos\\Serializer\\SerializerInterface' => 'tsantos_serializer'];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5ea00cc67502b\\Psr\\Container\\ContainerInterface' => true, '_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => true];
    }
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }
    public function isCompiled()
    {
        return true;
    }
    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);
        return true;
    }
    /**
     * Gets the public 'tsantos_serializer' shared service.
     *
     * @return \TSantos\Serializer\EventEmitterSerializer
     */
    protected function getTsantosSerializerService()
    {
        $a = new NormalizerRegistry();
        $b = new CollectionNormalizer();
        $c = new EventDispatcher();
        $c->addSubscriber(new StopwatchListener(new Stopwatch(true)));
        $this->services['tsantos_serializer'] = $instance = new EventEmitterSerializer(new JsonEncoder(), $a, $c);
        $b->setSerializer($instance);
        $d = new JsonNormalizer();
        $d->setSerializer($instance);
        $a->add(new ObjectNormalizer(new CircularReferenceHandler()));
        $a->add($b);
        $a->add($d);
        return $instance;
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class_alias('_PhpScoper5ea00cc67502b\\ProjectServiceContainer', 'ProjectServiceContainer', false);
