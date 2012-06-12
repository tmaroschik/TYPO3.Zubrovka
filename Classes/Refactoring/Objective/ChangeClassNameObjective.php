<?php
namespace TYPO3\Zubrovka\Refactoring\Objective;

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
class ChangeClassNameObjective extends AbstractObjective {

	/**
	 * Contains newName
	 *
	 * @var \PHPParser_Node_Name
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node_Stmt_Class $classNode
	 * @param \PHPParser_Node_Name $newName
	 */
	public function __construct(\PHPParser_Node_Stmt_Class $classNode, $newName) {
		$this->newName = $newName;
		parent::__construct($classNode);
	}

	/**
	 * @return \PHPParser_Node_Name
	 */
	public function getNewName() {
		return $this->newName;
	}

//	/**
//	 * @param array $nodes
//	 */
//	public function plan(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
//		/** @var $namespace \PHPParser_Node_Name */
//		$namespace = $this->node->getAttribute('namespace');
//		if ($namespace !== NULL) {
//			$newNamespaceParts = array_slice($this->newName->getParts(), 0, count($this->newName->getParts()) - 1);
//			if ($namespace->getParts() != $newNamespaceParts) {
//				$queuedNamespaceChanges = array_filter(
//					$queue->getOperationByType('TYPO3\Zubrovka\Refactoring\Operation\ChangeNamespaceNameObjective'),
//					function(ChangeNamespaceNameObjective $operation) use ($namespace) {
//						if ($operation->getNode()->getParts() == $namespace->getParts()) {
//							return true;
//						} else {
//							return false;
//						}
//					}
//				);
//				if (empty($queuedNamespaceChanges)) {
//					$changeNamespacedName = new ChangeNamespaceNameObjective($namespace, $this->newName);
//					$queue->queue($changeNamespacedName);
//				}
//			}
//		}
//		$this->setState(self::STATE_PLANED);
//		return $this;
//	}

//	/**
//	 * @return void
//	 */
//	public function run() {
//		$this->node->setName($this->newName->getLast());
//	}
}
