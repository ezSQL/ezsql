<?php

declare(strict_types=1);

namespace ezsql;

use Psr\Container\ContainerInterface;
use ezsql\Exception\ContainerException;
use ezsql\Exception\NotFoundException;

/**
 * Dependency Injection Container
 *
 * @see https://gist.github.com/MustafaMagdi/2bb27aebf6ab078b1f3e5635c0282fac
 */
class DInjector implements ContainerInterface
{
	/**
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Register a service with the container.
	 *
	 * @param string $abstract - className
	 * @param string $concrete - friendlyName
	 */
	public function set($abstract, $concrete = NULL)
	{
		if ($concrete === NULL) {
			$concrete = $abstract;
		}

		$this->instances[$abstract] = $concrete;
	}

	/**
	 * @param $abstract
	 *
	 * @return null|object
	 * @throws NotFoundException
	 */
	public function get($abstract, $values = [])
	{
		if (!$this->has($abstract)) {
			throw new NotFoundException("{$abstract} does not exists");
		}

		return $this->resolve($this->instances[$abstract], $values);
	}

	/**
	 * Auto setup, execute, or resolve any dependencies.
	 *
	 * @param $abstract
	 * @param array $values
	 *
	 * @return mixed|null|object
	 * @throws ContainerException
	 */
	public function autoWire($abstract, $values = [])
	{
		// if we don't have it, just register it
		if (!$this->has($abstract)) {
			$this->set($abstract);
		}

		return $this->resolve($this->instances[$abstract], $values);
	}

	/**
	 * Do we have dependence
	 * @param $abstract
	 * @return bool
	 */
	public function has($abstract): bool
	{
		return isset($this->instances[$abstract]);
	}

	/**
	 * resolve single dependence
	 *
	 * @param $concrete
	 * @param $values
	 *
	 * @return mixed|object
	 * @throws ContainerException
	 */
	protected function resolve($concrete, $values = [])
	{
		if ($concrete instanceof \Closure) {
			return $concrete($this, $values);
		}

		$reflector = new \ReflectionClass($concrete);
		// check if class is instantiable
		if (!$reflector->isInstantiable()) {
			throw new ContainerException("Class {$concrete} is not instantiable");
		}

		// get class constructor
		$constructor = $reflector->getConstructor();
		if (\is_null($constructor)) {
			// get new instance from class
			return $reflector->newInstance();
		}

		// get constructor params
		$parameters = $constructor->getParameters();
		$dependencies = $this->getDependencies($parameters, $values);

		// get new instance with dependencies resolved
		return $reflector->newInstanceArgs($dependencies);
	}

	/**
	 * get all dependencies resolved
	 *
	 * @param $parameters
	 *
	 * @return array
	 * @throws ContainerException
	 */
	protected function getDependencies($parameters, $values = null)
	{
		$dependencies = [];
		if (\is_array($parameters)) {
			foreach ($parameters as $parameter) {
				// get the type hinted class
				$dependency = $parameter->getType() && !$parameter->getType()->isBuiltin()
					? new \ReflectionClass($parameter->getType()->getName())
					: NULL;
				if ($dependency === NULL) {
					// check if the constructor parameter name exists as a key in the values array
					if (\array_key_exists($parameter->getName(), $values)) {
						// get default value of parameter
						$dependencies[] = $values[$parameter->getName()];
					} else {
						// check if default value for a parameter is available
						if ($parameter->isDefaultValueAvailable()) {
							// get default value of parameter
							$dependencies[] = $parameter->getDefaultValue();
						} else {
							throw new ContainerException("Can not resolve class dependency {$parameter->name}");
						}
					}
				} else {
					// get dependency resolved
					$dependencies[] = $this->autoWire($dependency->name, $values);
				}
			}
		}

		return $dependencies;
	}
}
