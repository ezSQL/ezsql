<?php
/**
 * Author:  Lawrence Stubbs <technoexpressnet@gmail.com>
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */
declare(strict_types=1);

namespace ezsql\ezInjector;

/**
 * Dependency Injection Container 
 * @see https://gist.github.com/MustafaMagdi/2bb27aebf6ab078b1f3e5635c0282fac
 * 
 */
class ezInjector
{
	/**
	 * @var array
	 */
	protected $instances = [];

	/**
	 * @param      $abstract
	 * @param null $concrete
	 */
	public function set($abstract, $concrete = NULL)
	{
		if ($concrete === NULL) {
			$concrete = $abstract;
		}
		$this->instances[$abstract] = $concrete;
	}

	/**
	 * @param       $abstract
	 * @param array $values
	 *
	 * @return mixed|null|object
	 * @throws Exception
	 */
	public function get($abstract, $values = [])
	{
		// if we don't have it, just register it
		if (!$this->has($abstract)) {
			$this->set($abstract);
		}
		// Auto load any dependencies
		return $this->resolve($this->instances[$abstract], $values);
	}

	/**
	 * Do we have it
	 * @param       $abstract
     * @return bool
     */
    public function has($abstract)
    {
        return isset($this->instances[$abstract]);
	}
	
	/**
	 * resolve single
	 *
	 * @param $concrete
	 * @param $values
	 *
	 * @return mixed|object
	 * @throws Exception
	 */
	public function resolve($concrete, $values = [])
	{
		if ($concrete instanceof Closure) {
			return $concrete($this, $values);
		}

		$reflector = new ReflectionClass($concrete);
		// check if class is instantiable
		if (!$reflector->isInstantiable()) {
			throw new Exception("Class {$concrete} is not instantiable");
		}

		// get class constructor
		$constructor = $reflector->getConstructor();
		if (is_null($constructor)) {
			// get new instance from class
			return $reflector->newInstance();
		}

		// get constructor params
		$parameters   = $constructor->getParameters();
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
	 * @throws Exception
	 */
	public function getDependencies($parameters, $values)
	{
		$dependencies = [];
		foreach ($parameters as $parameter) {
			// get the type hinted class
			$dependency = $parameter->getClass();
			if ($dependency === NULL) {				
				// check if the constructor parameter name exists as a key in the values array
				if (array_key_exists($parameter->getName(), $values)) {			  
				  // get default value of parameter
				  $dependencies[] = $values[$parameter->getName()];			  
				} else {			  
				  // check if default value for a parameter is available
				  if ($parameter->isDefaultValueAvailable()) {			  
					// get default value of parameter
					$dependencies[] = $parameter->getDefaultValue();			  
				  } else {			  
					throw new Exception("Can not resolve class dependency {$parameter->name}");			  
				  }			  
				}							  
			} else {			  
				// get dependency resolved
				$dependencies[] = $this->get($dependency->name);			  
			}
		}

		return $dependencies;
	}
}
