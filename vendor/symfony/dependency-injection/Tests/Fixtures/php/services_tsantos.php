<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container
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
        return ['_PhpScoper5ea00cc67502b\\Psr\\Container\\ContainerInterface' => \true, '_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
    }
    public function compile()
    {
        throw new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
    }
    public function isCompiled()
    {
        return \true;
    }
    public function isFrozen()
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), \E_USER_DEPRECATED);
        return \true;
    }
    /**
     * Gets the public 'tsantos_serializer' shared service.
     *
     * @return \TSantos\Serializer\EventEmitterSerializer
     */
    protected function getTsantosSerializerService()
    {
        $a = new \_PhpScoper5ea00cc67502b\TSantos\Serializer\NormalizerRegistry();
        $b = new \_PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\CollectionNormalizer();
        $c = new \_PhpScoper5ea00cc67502b\TSantos\Serializer\EventDispatcher\EventDispatcher();
        $c->addSubscriber(new \_PhpScoper5ea00cc67502b\TSantos\SerializerBundle\EventListener\StopwatchListener(new \_PhpScoper5ea00cc67502b\Symfony\Component\Stopwatch\Stopwatch(\true)));
        $this->services['tsantos_serializer'] = $instance = new \_PhpScoper5ea00cc67502b\TSantos\Serializer\EventEmitterSerializer(new \_PhpScoper5ea00cc67502b\TSantos\Serializer\Encoder\JsonEncoder(), $a, $c);
        $b->setSerializer($instance);
        $d = new \_PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\JsonNormalizer();
        $d->setSerializer($instance);
        $a->add(new \_PhpScoper5ea00cc67502b\TSantos\Serializer\Normalizer\ObjectNormalizer(new \_PhpScoper5ea00cc67502b\TSantos\SerializerBundle\Serializer\CircularReferenceHandler()));
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
\class_alias('_PhpScoper5ea00cc67502b\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
