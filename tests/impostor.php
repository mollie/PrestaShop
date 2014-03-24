<?php
/**
 * Mollie_Testing_Impostor
 *
 * Testing utility class that makes all the PRIVATE and PROTECTED methods and
 * properties of a base class PUBLIC for easier Unit Testing.
 *
 * @author Rick Wong <wong@mollie.nl> Aug 7, 2012
 * @copyright (C), Mollie B.V.
 */
class Mollie_Testing_Impostor
{
	/**
	 * @var mixed Base class instance
	 */
	protected $_base_class;

	/**
	 * Constructor
	 *
	 * @param string|object $base_class Class name of the base class, or an instance
	 * @param array $constructor_args (Optional) Arguments to pass to the constructor of the base class
	 * @param array $constants (Optional) Constants to override
	 */
	public function __construct ($base_class, array $constructor_args = array(), array $constants = array())
	{
		// @codeCoverageIgnoreStart
		if (PHP_VERSION_ID < 50300)
		{
			trigger_error(get_class().': This class requires PHP version 5.3 or higher.', E_USER_ERROR);
		}
		// @codeCoverageIgnoreEnd

		if (is_object($base_class) && count($constructor_args))
		{
			throw new Mollie_Testing_Exception('Unused constructor arguments for passed instance of "'.get_class($base_class).'".');
		}

		// If it's an instance, just accept it
		if (is_object($base_class))
		{
			$this->_base_class = $base_class;
		}
		// If it's a classname, use ReflectionClass to create an instance, even if it's abstract
		elseif (!class_exists($base_class))
		{
			throw new Mollie_Testing_Exception("Base class '$base_class' does not exist.");
		}
		else
		{
			$ref = new ReflectionClass($base_class);

			if ($ref->isAbstract()) {
				$ref = new ReflectionClass($this->createDerivedClass($base_class));
			}

			if ($ref->getConstructor() && count($constructor_args) < $ref->getConstructor()->getNumberOfRequiredParameters())
			{
				throw new Mollie_Testing_Exception($ref->getConstructor()->getNumberOfRequiredParameters() . " constructor arguments required for '$base_class::__construct(...)'.");
			}

			$this->_base_class = sizeof($constructor_args) ? $ref->newInstanceArgs($constructor_args) : $ref->newInstance();
		}
	}

	/**
	 * Note: it can auto-extend abstract classes but not implement abstract methods.
	 *
	 * @param string $abstract_class Must be an existing classname
	 * @return string Returns name of a new instantiable class
	 * @param array $constants (Optional) Constants to override
	 * @throws Mollie_Testing_Exception
	 */
	public function createDerivedClass ($base_class, array $constants = array())
	{
		$derived = "Derived_$base_class";

		if (!class_exists($derived, FALSE) && class_exists($base_class))
		{
			$class_definition = "final class $derived extends $base_class { ";

			foreach ($constants as $constant => $value)
			{
				$value = is_numeric($value) ? $value : "'$value'";
				$class_definition .= "const $constant = $value; ";
			}

			$reflection = new ReflectionClass($base_class);

			foreach ($reflection->getMethods() as $method)
			{
				if ($method->isAbstract())
				{
					$params = join(',', array_map(function (ReflectionParameter $p) { return '$'.$p->getName(); }, $method->getParameters()));
					$class_definition .= "public function {$method->getName()} ($params) {} ";
				}
			}

			$class_definition .= '}';
			eval($class_definition);
		}

		if (!class_exists($derived, FALSE))
		{
			throw new Mollie_Testing_Exception("Failed to create instance of auto-extended abstract class '$base_class'.");
		}

		return $derived;
	}

	/**
	 * Get the original object back, convenient when some typehinting needs to be satisfied.
	 *
	 * @return object
	 */
	public function getInstance ()
	{
		return $this->_base_class;
	}

	/**
	 * Use the magic method __call() to invoke protected methods
	 *
	 * @param string $method Name of the method
	 * @param array $args Arguments to provide to the method
	 * @return mixed Return value of the method
	 * @throws Mollie_Testing_Exception
	 */
	public function __call ($method, $args)
	{
		if (method_exists($this->_base_class, $method))
		{
			$ref = new ReflectionMethod($this->_base_class, $method);

			if (count($args) < $ref->getNumberOfRequiredParameters()) {
				throw new Mollie_Testing_Exception("More arguments required for '".get_class($this->_base_class)."::$method(...)'.");
			}

			$ref->setAccessible(TRUE);
			return $ref->invokeArgs($this->_base_class, $args);
		}

		if (method_exists($this->_base_class, '__call'))
		{
			// __call() is always public, no need for ReflectionMethod
			return call_user_func_array(array($this->_base_class, $method), $args);
		}

		throw new Mollie_Testing_Exception("Method '".get_class($this->_base_class)."::$method()' does not exist.");
	}

	/**
	 * Use the magic method __get() to get protected properties
	 *
	 * @param string $property Name of the property
	 * @return mixed Value of the property
	 * @throws Mollie_Testing_Exception
	 */
	public function __get ($property)
	{
		if (property_exists($this->_base_class, $property))
		{
			$ref = new ReflectionProperty($this->_base_class, $property);
			$ref->setAccessible(TRUE);
			return $ref->getValue($this->_base_class);
		}

		if (method_exists($this->_base_class, '__get'))
		{
			// __get() is always public, no need for ReflectionProperty
			return $this->_base_class->$property;
		}

		throw new Mollie_Testing_Exception("Property '".get_class($this->_base_class)."::$property' does not exist.");
	}

	/**
	 * Use the magic method __set() to set protected properties
	 *
	 * @param string $property Name of the property
	 * @param mixed $value Value to set the property to
	 * @return mixed New value of the property
	 * @throws Mollie_Testing_Exception
	 */
	public function __set ($property, $value)
	{
		if (property_exists($this->_base_class, $property))
		{
			$ref = new ReflectionProperty($this->_base_class, $property);
			$ref->setAccessible(TRUE);
			$ref->setValue($this->_base_class, $value);
			return $ref->getValue($this->_base_class);
		}

		if (method_exists($this->_base_class, '__set'))
		{
			// __set() is always public, no need for ReflectionProperty
			return $this->_base_class->$property = $value;
		}

		throw new Mollie_Testing_Exception("Property '".get_class($this->_base_class)."::$property' does not exist.");
	}
}