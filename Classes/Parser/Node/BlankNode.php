<?php
namespace TYPO3\Zubrovka\Parser\Node;

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
 * @FLOW3\Scope("prototype")
 */
class BlankNode extends \PHPParser_Node_Stmt {
	
	/**
	 * Gets the type of the node.
	 *
	 * @return string Type of the node
	 */
	public function getNodeType() {
		return 'BlankNode';
	}

}