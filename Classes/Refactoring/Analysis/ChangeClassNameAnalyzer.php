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

/**
 * @FLOW3\Scope("prototype")
 */
class ChangeClassNameAnalyzer extends \PHPParser_NodeVisitorAbstract implements \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface {

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
	protected $oldName;

	/**
	 * @var \PHPParser_Node_Name
	 */
	protected $newName;

	/**
	 * @var \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	protected $objectives;

	/**
	 * @param string $oldName
	 * @param string $newName
	 */
	public function __construct($oldName, $newName) {
		$this->oldName = strpos($oldName, '\\') !== FALSE ? new \PHPParser_Node_Name_FullyQualified($oldName) : new \PHPParser_Node_Name($oldName);
		$this->newName = strpos($oldName, '\\') !== FALSE ? new \PHPParser_Node_Name_FullyQualified($newName) : new \PHPParser_Node_Name($newName);
	}

	/**
	 * @param array $nodes
	 */
	public function beforeTraverse(array $nodes) {
		$this->namespace = null;
		$this->aliases   = array();
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
				$this->aliases   = array();
				break;
			case $node instanceof \PHPParser_Node_Stmt_UseUse:
				/** @var $node \PHPParser_Node_Stmt_UseUse */
				if (isset($this->aliases[$node->getAlias()])) {
					throw new \PHPParser_Error(sprintf('Cannot use "%s" as "%s" because the name is already in use', $node->getName(), $node->getAlias()), $node->getLine());
				}
				$this->aliases[$node->getAlias()] = $node;
				break;
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 * @return \PHPParser_Node_Name
	 */
	public function leaveNode(\PHPParser_Node $node) {
		$this->checkRenamingInDocComment($node);
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Class:
				/** @var $node \PHPParser_Node_Stmt_Class */
				if (null !== $node->getExtends()) {
					$this->checkRenaming($node->getExtends());
				}
				foreach ($node->getImplements() as $interface) {
					$this->checkRenaming($interface);
				}
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				/** @var $node \PHPParser_Node_Stmt_Interface */
				foreach ($node->getExtends() as $interface) {
					$this->checkRenaming($interface);
				}
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Trait:
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Function:
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_ClassMethod:
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Const:
				/** @var $node \PHPParser_Node_Stmt_Const */
				foreach ($node->getConsts() as $const) {
					$this->checkRenaming($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				/** @var $node \PHPParser_Node_Expr_StaticPropertyFetch */
				if ($node->getClass() instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node->getClass());
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				/** @var $node \PHPParser_Node_Expr_FuncCall */
				if ($node->getName() instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node);
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				/** @var $node \PHPParser_Node_Stmt_TraitUse */
				foreach ($node->getTraits() as $trait) {
					$this->checkRenaming($trait);
				}
				break;
			case $node instanceof \PHPParser_Node_Param:
				/** @var $node \PHPParser_Node_Param */
				if ($node->getType() instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node->getType());
				}
				break;
		}
	}

	/**
	 * @param array $nodes
	 */
	public function afterTraverse(array $nodes) {

	}

	/**
	 * @param \PHPParser_Node $node
	 * @param string $property
	 */
	protected function checkRenaming(\PHPParser_Node $node) {
		if (NULL !== $node->getAttribute('namespacedName') && $node->getAttribute('namespacedName')->getParts() == $this->oldName->getParts()) {
			switch ($node) {
				case $node instanceof \PHPParser_Node_Name_FullyQualified:
					$this->objectives[] = (new Refactoring\Objective\ChangeFullyQualifiedNameObjective($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Name_Relative:
					$this->objectives[] = (new Refactoring\Objective\ChangeRelativeNameObjective($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Name:
					$this->objectives[] = (new Refactoring\Objective\ChangeNameObjective($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Stmt_Class:
					$this->objectives[] = (new Refactoring\Objective\ChangeClassNameObjective($node, $this->newName));
					break;
				default:
					break;
			}
		} elseif ($node instanceof \PHPParser_Node_Stmt_Namespace && $node->getName()->getParts() == array_slice($this->oldName->parts, 0, count($this->oldName->getParts()) - 1)) {
			$this->objectives[] = (new Refactoring\Objective\ChangeNamespaceNameObjective($node->getName(), $this->newName));
		} elseif ($node instanceof \PHPParser_Node_Name && $node->getParts() == $this->oldName->getParts()) {
			$this->objectives[] = (new Refactoring\Objective\ChangeNameObjective($node, $this->newName));
		} elseif ($node instanceof \PHPParser_Node_Stmt_Class && $node->getName() === (string) $this->oldName) {
			$this->objectives[] = (new Refactoring\Objective\ChangeClassNameObjective($node, $this->newName));
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function checkRenamingInDocComment(\PHPParser_Node $node) {
		$docComments = array_filter($node->getIgnorables() ?: array(), function($ignorable) {
			return $ignorable instanceof \PHPParser_Node_Ignorable_DocComment;
		});
		/** @var $docComment \PHPParser_Node_Ignorable_DocComment */
		foreach ($docComments as $docComment) {
			foreach (array('param', 'var') as $tagName) {
				if ($docComment->isTaggedWith($tagName)) {
					foreach ($docComment->getTagValues($tagName) as $tagValue) {
						$typeAndComment = preg_split('/\s/', $tagValue, 2);
						$type = $typeAndComment[0] ?: '';
						if (substr($type, 0, 1) == '$') {
							// If tag starts with variable switch type and comment, if no comment set continue
							if (!isset($typeAndComment[1])) {
								continue;
							}
							$type = $typeAndComment[1];
						}
						$typeParts = explode('\\', ltrim($type, '\\'));
						if ($typeParts == $this->oldName->getParts()) {
							// Fully Qualified or no namespace
							if (NULL !== $this->namespace) {
								$this->objectives[] = (new Refactoring\Objective\ChangeFullyQualifiedNameInDocCommentObjective($docComment, $tagName, $tagValue, $this->newName));
							} else {
								$this->objectives[] = (new Refactoring\Objective\ChangeNameInDocCommentObjective($docComment, $tagName, $tagValue, $this->newName));
							}
						} elseif (NULL !== $this->namespace) {
							if (isset($this->aliases[$typeParts[0]]) && array_merge($this->aliases[$typeParts[0]]->getName()->getParts(), array_slice($typeParts, 1)) == $this->oldName->getParts()) {
								// Relative to imported namespace
								$this->objectives[] = (new Refactoring\Objective\ChangeRelativeNameInDocCommentObjective($docComment, $tagName, $tagValue, $this->newName, null, $this->aliases[$typeParts[0]]));
							} elseif (array_merge($this->namespace->getParts(), $typeParts) == $this->oldName->getParts()) {
								// Relative to current namespace
								$this->objectives[] = (new Refactoring\Objective\ChangeRelativeNameInDocCommentObjective($docComment, $tagName, $tagValue, $this->newName, $this->namespace));
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	public function getObjectives() {
		return $this->objectives;
	}
}