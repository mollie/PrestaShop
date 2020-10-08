<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Container;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\LogicException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \MolliePrefix\Symfony\Component\DependencyInjection\Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->normalizedIds = ['MolliePrefix\\tsantos\\serializer\\serializerinterface' => 'MolliePrefix\\TSantos\\Serializer\\SerializerInterface'];
        $this->methodMap = ['tsantos_serializer' => 'getTsantosSerializerService'];
        $this->aliases = ['MolliePrefix\\TSantos\\Serializer\\SerializerInterface' => 'tsantos_serializer'];
    }
    public function getRemovedIds()
    {
        return ['MolliePrefix\\Psr\\Container\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
    }
    public function compile()
    {
        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
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
        $a = new \MolliePrefix\TSantos\Serializer\NormalizerRegistry();
        $b = new \MolliePrefix\TSantos\Serializer\Normalizer\CollectionNormalizer();
        $c = new \MolliePrefix\TSantos\Serializer\EventDispatcher\EventDispatcher();
        $c->addSubscriber(new \MolliePrefix\TSantos\SerializerBundle\EventListener\StopwatchListener(new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch(\true)));
        $this->services['tsantos_serializer'] = $instance = new \MolliePrefix\TSantos\Serializer\EventEmitterSerializer(new \MolliePrefix\TSantos\Serializer\Encoder\JsonEncoder(), $a, $c);
        $b->setSerializer($instance);
        $d = new \MolliePrefix\TSantos\Serializer\Normalizer\JsonNormalizer();
        $d->setSerializer($instance);
        $a->add(new \MolliePrefix\TSantos\Serializer\Normalizer\ObjectNormalizer(new \MolliePrefix\TSantos\SerializerBundle\Serializer\CircularReferenceHandler()));
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
\class_alias('MolliePrefix\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
