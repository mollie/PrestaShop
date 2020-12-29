<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy;

use MolliePrefix\Prophecy\Doubler\CachedDoubler;
use MolliePrefix\Prophecy\Doubler\Doubler;
use MolliePrefix\Prophecy\Doubler\LazyDouble;
use MolliePrefix\Prophecy\Doubler\ClassPatch;
use MolliePrefix\Prophecy\Prophecy\ObjectProphecy;
use MolliePrefix\Prophecy\Prophecy\RevealerInterface;
use MolliePrefix\Prophecy\Prophecy\Revealer;
use MolliePrefix\Prophecy\Call\CallCenter;
use MolliePrefix\Prophecy\Util\StringUtil;
use MolliePrefix\Prophecy\Exception\Prediction\PredictionException;
use MolliePrefix\Prophecy\Exception\Prediction\AggregateException;
/**
 * Prophet creates prophecies.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Prophet
{
    private $doubler;
    private $revealer;
    private $util;
    /**
     * @var ObjectProphecy[]
     */
    private $prophecies = array();
    /**
     * Initializes Prophet.
     *
     * @param null|Doubler           $doubler
     * @param null|RevealerInterface $revealer
     * @param null|StringUtil        $util
     */
    public function __construct(\MolliePrefix\Prophecy\Doubler\Doubler $doubler = null, \MolliePrefix\Prophecy\Prophecy\RevealerInterface $revealer = null, \MolliePrefix\Prophecy\Util\StringUtil $util = null)
    {
        if (null === $doubler) {
            $doubler = new \MolliePrefix\Prophecy\Doubler\CachedDoubler();
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\SplFileInfoPatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\TraversablePatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\ThrowablePatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\DisableConstructorPatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\ProphecySubjectPatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\ReflectionClassNewInstancePatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\HhvmExceptionPatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\MagicCallPatch());
            $doubler->registerClassPatch(new \MolliePrefix\Prophecy\Doubler\ClassPatch\KeywordPatch());
        }
        $this->doubler = $doubler;
        $this->revealer = $revealer ?: new \MolliePrefix\Prophecy\Prophecy\Revealer();
        $this->util = $util ?: new \MolliePrefix\Prophecy\Util\StringUtil();
    }
    /**
     * Creates new object prophecy.
     *
     * @param null|string $classOrInterface Class or interface name
     *
     * @return ObjectProphecy
     */
    public function prophesize($classOrInterface = null)
    {
        $this->prophecies[] = $prophecy = new \MolliePrefix\Prophecy\Prophecy\ObjectProphecy(new \MolliePrefix\Prophecy\Doubler\LazyDouble($this->doubler), new \MolliePrefix\Prophecy\Call\CallCenter($this->util), $this->revealer);
        if ($classOrInterface && \class_exists($classOrInterface)) {
            return $prophecy->willExtend($classOrInterface);
        }
        if ($classOrInterface && \interface_exists($classOrInterface)) {
            return $prophecy->willImplement($classOrInterface);
        }
        return $prophecy;
    }
    /**
     * Returns all created object prophecies.
     *
     * @return ObjectProphecy[]
     */
    public function getProphecies()
    {
        return $this->prophecies;
    }
    /**
     * Returns Doubler instance assigned to this Prophet.
     *
     * @return Doubler
     */
    public function getDoubler()
    {
        return $this->doubler;
    }
    /**
     * Checks all predictions defined by prophecies of this Prophet.
     *
     * @throws Exception\Prediction\AggregateException If any prediction fails
     */
    public function checkPredictions()
    {
        $exception = new \MolliePrefix\Prophecy\Exception\Prediction\AggregateException("Some predictions failed:\n");
        foreach ($this->prophecies as $prophecy) {
            try {
                $prophecy->checkProphecyMethodsPredictions();
            } catch (\MolliePrefix\Prophecy\Exception\Prediction\PredictionException $e) {
                $exception->append($e);
            }
        }
        if (\count($exception->getExceptions())) {
            throw $exception;
        }
    }
}
