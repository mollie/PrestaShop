<?php

namespace MolliePrefix\Dotenv\Environment;

use MolliePrefix\Dotenv\Environment\Adapter\AdapterInterface;
use MolliePrefix\Dotenv\Environment\Adapter\ApacheAdapter;
use MolliePrefix\Dotenv\Environment\Adapter\EnvConstAdapter;
use MolliePrefix\Dotenv\Environment\Adapter\PutenvAdapter;
use MolliePrefix\Dotenv\Environment\Adapter\ServerConstAdapter;
/**
 * The default implementation of the environment factory interface.
 */
class DotenvFactory implements \MolliePrefix\Dotenv\Environment\FactoryInterface
{
    /**
     * The set of adapters to use.
     *
     * @var \Dotenv\Environment\Adapter\AdapterInterface[]
     */
    protected $adapters;
    /**
     * Create a new dotenv environment factory instance.
     *
     * If no adapters are provided, then the defaults will be used.
     *
     * @param \Dotenv\Environment\Adapter\AdapterInterface[]|null $adapters
     *
     * @return void
     */
    public function __construct(array $adapters = null)
    {
        $this->adapters = \array_filter($adapters === null ? [new \MolliePrefix\Dotenv\Environment\Adapter\ApacheAdapter(), new \MolliePrefix\Dotenv\Environment\Adapter\EnvConstAdapter(), new \MolliePrefix\Dotenv\Environment\Adapter\ServerConstAdapter(), new \MolliePrefix\Dotenv\Environment\Adapter\PutenvAdapter()] : $adapters, function (\MolliePrefix\Dotenv\Environment\Adapter\AdapterInterface $adapter) {
            return $adapter->isSupported();
        });
    }
    /**
     * Creates a new mutable environment variables instance.
     *
     * @return \Dotenv\Environment\VariablesInterface
     */
    public function create()
    {
        return new \MolliePrefix\Dotenv\Environment\DotenvVariables($this->adapters, \false);
    }
    /**
     * Creates a new immutable environment variables instance.
     *
     * @return \Dotenv\Environment\VariablesInterface
     */
    public function createImmutable()
    {
        return new \MolliePrefix\Dotenv\Environment\DotenvVariables($this->adapters, \true);
    }
}
