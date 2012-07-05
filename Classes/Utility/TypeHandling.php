<?php
namespace TYPO3\Zubrovka\Utility;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * PHP type handling functions
 *
 */
class TypeHandling {

	/**
	 * @param string $type
	 * @return string unified data type
	 */
	static public function isBuiltin($type) {
		$scalarPattern = '/^(?:integer|int|float|double|boolean|bool|string|array|object|void)$/';
		$type = strtolower($type);
		switch ($type) {
			case 'int':
				$type = 'integer';
				break;
			case 'bool':
				$type = 'boolean';
				break;
			case 'double':
				$type = 'float';
				break;
		}
		return preg_match($scalarPattern, $type) === 1;
	}

}
?>
