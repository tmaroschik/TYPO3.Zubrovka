<?php
namespace TYPO3\Zubrovka\Refactoring\Analysis;

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
use TYPO3\Zubrovka\Refactoring;
use PHPParser_Exception_EscapeDeeperTraversalException;

/**
 * @FLOW3\Scope("prototype")
 */
class ExtractClassAnalyzer extends \PHPParser_NodeVisitorAbstract implements \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface {

	/**
	 * @var null|\PHPParser_Node_Name Current namespace
	 */
	protected $namespace;

	/**
	 * @var array Currently defined namespace and class aliases
	 */
	protected $aliases;

	/**
	 * @var \PHPParser_Node_Name
	 */
	protected $className;

	/**
	 * @var string
	 */
	protected $targetClassFile;

	/**
	 * @var \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	protected $objectives;

	/**
	 * @param string $oldName
	 * @param string $newName
	 */
	public function __construct($className, $targetClassFile) {
		$this->className = strpos($className, '\\') !== FALSE ? new \PHPParser_Node_Name_FullyQualified($className) : new \PHPParser_Node_Name($className);
		$this->targetClassFile = $targetClassFile;
	}

	/**
	 * @param array $nodes
	 */
	public function beforeTraverse(array $nodes) {
		$this->namespace = NULL;
		$this->aliases = array();
		$this->objectives = array();
	}

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function enterNode(\PHPParser_Node $node) {
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Namespace:
				/** @var $node \PHPParser_Node_Stmt_Namespace */
				$this->namespace = $node->getName();
				$this->aliases = array();
				break;
			case $node instanceof \PHPParser_Node_Stmt_UseUse:
				/** @var $node \PHPParser_Node_Stmt_UseUse */
				if (isset($this->aliases[$node->getAlias()])) {
					throw new \PHPParser_Error(sprintf('Cannot use "%s" as "%s" because the name is already in use', $node->getName(), $node->getAlias()), $node->getLine());
				}
				$this->aliases[$node->getAlias()] = $node;
				break;
			case $node instanceof \PHPParser_Node_Stmt_Class || $node instanceof \PHPParser_Node_Stmt_Interface:
				if ((NULL !== $node->getAttribute('namespacedName') && $node->getAttribute('namespacedName')->getParts() == $this->className->getParts())
					|| $node->getName() == (string) $this->className) {
					$this->objectives[] = (new Refactoring\Objective\ExtractClassObjective($node, $this->targetClassFile));
				}
				throw new PHPParser_Exception_EscapeDeeperTraversalException();
				break;
		}
	}

	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	public function getObjectives() {
		return $this->objectives;
	}
}
