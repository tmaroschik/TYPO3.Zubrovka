<?php
namespace TYPO3\Zubrovka\Refactoring\Operation;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The operation factory creates operations and returns already instanciated operations
 * in case a newly created operation has the same constructor arguments like an existing.
 *
 * @FLOW3\Scope("singleton")
 */
class OperationFactory  {

	/**
	 * Contains instanciatedOperations
	 *
	 * @var array
	 */
	protected $instanciatedOperations = array();

	/**
	 * @param string $type The type of the operation as fully qualified class name
	 * @param mixed $constructorArgument,... The according constructor arguments for that class name.
	 * @return OperationInterface
	 */
	public function create($type) {
		$arguments = array_slice(func_get_args(), 1);
		$constructorHash = $this->createConstructorHash($type, $arguments);
		if (isset($this->instanciatedOperations[$constructorHash])) {
			return $this->instanciatedOperations[$constructorHash];
		}
		$operation = $this->instantiateClass($type, $arguments);
		if (!$operation instanceof OperationInterface) {
			throw new \InvalidArgumentException('The operation to be created must implement \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface. Operation of type "' . $type . '" given.', 1339429098);
		}
		$this->instanciatedOperations[$constructorHash] = $operation;
		return $operation;
	}

	/**
	 * @param string $type
	 * @param array $arguments
	 * @return string
	 */
	protected function createConstructorHash($type, array $arguments = array()) {
		$hashArray = array($type);
		foreach ($arguments as $argument) {
			$hashArray[] = $this->getHashablePartOfArgument($argument);
		}
		return sha1(implode('###', $hashArray));
	}

	/**
	 * @param mixed $argument
	 * @return string
	 */
	protected function getHashablePartOfArgument($argument) {
		if (is_object($argument)) {
			return spl_object_hash($argument);
		} elseif (is_array($argument)) {
			$hashableParts = array();
			foreach ($argument as $argumentPart) {
				$hashableParts[] = $this->getHashablePartOfArgument($argumentPart);
			}
			return sha1(implode('###', $hashableParts));
		} elseif (is_scalar($argument)) {
			return $argument;
		}
	}

	/**
	 * Speed optimized alternative to ReflectionClass::newInstanceArgs()
	 *
	 * @param string $className Name of the class to instantiate
	 * @param array $arguments Arguments to pass to the constructor
	 * @return object The object
	 */
	protected function instantiateClass($className, array $arguments) {
		try {
			switch (count($arguments)) {
				case 0: return new $className();
				case 1: return new $className($arguments[0]);
				case 2: return new $className($arguments[0], $arguments[1]);
				case 3: return new $className($arguments[0], $arguments[1], $arguments[2]);
				case 4: return new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
				case 5: return new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
				case 6: return new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
			}
			$class = new \ReflectionClass($className);
			$object =  $class->newInstanceArgs($arguments);
		} catch (\Exception $exception) {
			throw $exception;
		}

		return $object;
	}

}