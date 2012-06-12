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
class ChangeRelativeNameInDocCommentObjective extends AbstractObjective {

	/**
	 * @var \PHPParser_Node_Ignorable_DocComment
	 */
	protected $node;

	/**
	 * @var \PHPParser_Node_Name
	 */
	protected $newName;

	/**
	 * @var string
	 */
	protected $tagName;

	/**
	 * @var string
	 */
	protected $tagValue;

	/**
	 * @var \PHPParser_Node_Stmt_UseUse
	 */
	protected $alias;

	/**
	 * @var \PHPParser_Node_Stmt_Namespace
	 */
	protected $namespace;

	/**
	 * @param \PHPParser_Node_Ignorable_DocComment $node
	 * @param string $tagName
	 * @param string $tagValue
	 * @param \PHPParser_Node_Name $newName
	 * @param \PHPParser_Node_Stmt_Namespace $namespace
	 * @param \PHPParser_Node_Stmt_UseUse $alias
	 */
	public function __construct(\PHPParser_Node_Ignorable_DocComment $node, $tagName, $tagValue, \PHPParser_Node_Name $newName, \PHPParser_Node_Stmt_Namespace $namespace = null, \PHPParser_Node_Stmt_UseUse $alias = null) {
		$this->tagName = $tagName;
		$this->tagValue = $tagValue;
		$this->newName = $newName;
		$this->namespace = $namespace;
		$this->alias = $alias;
		parent::__construct($node);
	}

	/**
	 * @return \PHPParser_Node_Stmt_UseUse
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * @return \PHPParser_Node_Stmt_Namespace
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getTagName() {
		return $this->tagName;
	}

	/**
	 * @return string
	 */
	public function getTagValue() {
		return $this->tagValue;
	}

	/**
	 * @return \PHPParser_Node_Name
	 */
	public function getNewName() {
		return $this->newName;
	}
//
//	/**
//	 * @param array $nodes
//	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
//	 */
//	public function plan(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
//		if (NULL !== $alias = $this->alias) {
//			if ($alias->getName()->getParts() != array_slice($this->newName->getParts(), 0, count($alias->getName()->getParts()))) {
//				// Leaves imported namespace
//				$queuedUseStatementChanges = array_filter(iterator_to_array($queue), function($operation) use ($alias) {
//					/** @var $operation ObjectiveInterface */
//					if ($operation instanceof ChangeUseStatementObjective && $operation->getNode()->getName()->getParts() == $alias->getName()->getParts()) {
//						return true;
//					} else {
//						return false;
//					}
//				});
//				if (empty($queuedUseStatementChanges)) {
//					$changeUseStatement = new ChangeUseStatementObjective($alias, $this->newName);
//					$queue->queue($changeUseStatement);
//					$changeUseStatement->plan($nodes, $queue);
//				}
//				$this->relativeNameParts = array_merge(array($alias->getAlias()), array_slice($this->newName->getParts(), count($alias->getName()->getParts())));
//			} else {
//				// Stays in imported namespace
//				$this->relativeNameParts = array_slice($this->newName->getParts(), count($alias->getName()->getParts()) - 1);
//				if ($alias->getAlias() != $this->relativeNameParts[0]) {
//					$this->relativeNameParts[0] = $alias->getAlias();
//				}
//			}
//		} elseif (NULL !== $namespace = $this->namespace) {
//			if ($namespace->getParts() != array_slice($this->newName->getParts(), 0, count($namespace->getParts()))) {
//				// Leaves namespace
//				//TODO implement addition of use statement
//			} else {
//				// Stays in namespace
//				$this->relativeNameParts = array_slice($this->newName->getParts(), count($namespace->getParts()));
//			}
//		}
//		$this->setState(self::STATE_PLANED);
//	}
//
//	/**
//	 * @return void
//	 */
//	public function run() {
//		if (!empty($this->relativeNameParts) && $this->node->isTaggedWith($this->tagName)) {
//			$tagsValues =& $this->node->getTagsValues();
//			foreach ($tagsValues[$this->tagName] as &$tagValue) {
//				if ($tagValue == $this->tagValue) {
//					$typeAndComment = preg_split('/\s/', $tagValue, 2);
//					$tagValue = implode('\\', $this->relativeNameParts);
//					$type = $typeAndComment[0] ?: '';
//					if (substr($type, 0, 1) == '$') {
//						$tagValue = $type . ' ' . $tagValue;
//					} elseif (isset($typeAndComment[1])) {
//						$tagValue = $tagValue . ' ' . $typeAndComment[1];
//					}
//					break;
//				}
//			}
//		}
//	}
}
