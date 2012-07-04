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
 * @FLOW3\Scope("prototype")
 */
class ReplaceNameWithRelativeNameOperation extends AbstractOperation {

	/**
	 * Contains the class name node
	 *
	 * @var \PHPParser_Node_Name
	 */
	protected $node;

	/**
	 * Contains the new relative name
	 *
	 * @var \PHPParser_Node_Name_Relative
	 */
	protected $relativeName;

	/**
	 * @param \PHPParser_Node_Name $node
	 * @param \PHPParser_Node_Name_Relative $relativeName
	 */
	function __construct(\PHPParser_Node_Name $node, \PHPParser_Node_Name_Relative $relativeName) {
		$this->relativeName = $relativeName;
		parent::__construct($node);
	}

	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		$parent = $this->node->getParent();
		$parentSubNodeName = ucfirst($this->node->getParentSubNodeName());
		$singularName = substr($parentSubNodeName, -1) == 's' ? substr($parentSubNodeName, 0,  -1) : null;
		if (is_callable(array($parent, 'replace' . $parentSubNodeName))) {
			$parent->{'replace' . $parentSubNodeName}($this->relativeName);
		} elseif (NULL !== $singularName && is_callable(array($parent, 'replace' . $singularName))) {
			$parent->{'replace' . $singularName}($this->relativeName, $this->node);
		} else {
			$parent->{'set' . $parentSubNodeName}($this->relativeName, $this->node);
		}
		return TRUE;
	}


}
