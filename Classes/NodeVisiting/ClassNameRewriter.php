<?php
namespace TYPO3\Zubrovka\NodeVisiting;

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
class ClassNameRewriter extends \PHPParser_NodeVisitorAbstract {

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
	 * @var \TYPO3\Zubrovka\Refactoring\OperationQueue
	 */
	protected $operationQueue;

	/**
	 * @param string $oldName
	 * @param string $newName
	 */
	public function __construct($oldName, $newName, Refactoring\OperationQueue $operationQueue) {
		$this->oldName = strpos($oldName, '\\') !== FALSE ? new \PHPParser_Node_Name_FullyQualified($oldName) : new \PHPParser_Node_Name($oldName);
		$this->newName = strpos($oldName, '\\') !== FALSE ? new \PHPParser_Node_Name_FullyQualified($newName) : new \PHPParser_Node_Name($newName);
		$this->operationQueue = $operationQueue;
	}

	/**
	 * @param array $nodes
	 */
	public function beforeTraverse(array $nodes) {
		$this->namespace = null;
		$this->aliases   = array();
	}

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function enterNode(\PHPParser_Node $node) {
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Namespace:
				$this->namespace = $node->name;
				$this->aliases   = array();
				break;
			case $node instanceof \PHPParser_Node_Stmt_UseUse:
				if (isset($this->aliases[$node->alias])) {
					throw new \PHPParser_Error(sprintf('Cannot use "%s" as "%s" because the name is already in use', $node->name, $node->alias), $node->getLine());
				}
				$this->aliases[$node->alias] = $node;
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
				if (null !== $node->extends) {
					$this->checkRenaming($node->extends);
				}
				foreach ($node->implements as &$interface) {
					$this->checkRenaming($interface);
				}
				$this->checkRenaming($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				foreach ($node->extends as &$interface) {
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
				foreach ($node->consts as &$const) {
					$this->checkRenaming($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				if ($node->class instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node->class);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				if ($node->name instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node);
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				foreach ($node->traits as &$trait) {
					$this->checkRenaming($trait);
				}
				break;
			case $node instanceof \PHPParser_Node_Param:
				if ($node->type instanceof \PHPParser_Node_Name) {
					$this->checkRenaming($node->type);
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
		if (NULL !== $node->getAttribute('namespacedName') && $node->getAttribute('namespacedName')->parts == $this->oldName->parts) {
			switch ($node) {
				case $node instanceof \PHPParser_Node_Name_FullyQualified:
					$this->operationQueue->queue(new Refactoring\Operation\ChangeFullyQualifiedName($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Name_Relative:
					$this->operationQueue->queue(new Refactoring\Operation\ChangeRelativeName($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Name:
					$this->operationQueue->queue(new Refactoring\Operation\ChangeName($node, $this->newName));
					break;
				case $node instanceof \PHPParser_Node_Stmt_Class:
					$this->operationQueue->queue(new Refactoring\Operation\ChangeClassName($node, $this->newName));
					break;
				default:
					break;
			}
		} elseif ($node instanceof \PHPParser_Node_Stmt_Namespace && $node->name->parts == array_slice($this->oldName->parts, 0, count($this->oldName->parts) - 1)) {
			$this->operationQueue->queue(new Refactoring\Operation\ChangeNamespaceName($node->name, $this->newName));
		} elseif ($node instanceof \PHPParser_Node_Name && $node->parts == $this->oldName->parts) {
			$this->operationQueue->queue(new Refactoring\Operation\ChangeName($node, $this->newName));
		} elseif ($node instanceof \PHPParser_Node_Stmt_Class && $node->name === (string) $this->oldName) {
			$this->operationQueue->queue(new Refactoring\Operation\ChangeClassName($node, $this->newName));
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function checkRenamingInDocComment(\PHPParser_Node $node) {
		$docComments = array_filter($node->getIgnorables() ?: array(), function($ignorable) {
			return $ignorable instanceof \PHPParser_Node_Ignorable_DocComment;
		});
		foreach ($docComments as $docComment) {
			foreach (array('param', 'var') as $tagName) {
				if (isset($docComment->tags[$tagName])) {
					foreach ($docComment->tags[$tagName] as $tagValue) {
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
						if ($typeParts == $this->oldName->parts) {
							// Fully Qualified or no namespace
							$this->operationQueue->queue(new Refactoring\Operation\ChangeFullyQualifiedNameInDocComment($docComment, $tagName, $tagValue, $this->newName));
						} elseif (NULL !== $this->namespace) {
							if (isset($this->aliases[$typeParts[0]]) && array_merge($this->aliases[$typeParts[0]]->name->parts, array_slice($typeParts, 1)) == $this->oldName->parts) {
								// Relative to imported namespace
								$this->operationQueue->queue(new Refactoring\Operation\ChangeRelativeNameInDocComment($docComment, $tagName, $tagValue, $this->newName, null, $this->aliases[$typeParts[0]]));
							} elseif (array_merge($this->namespace->parts, $typeParts) == $this->oldName->parts) {
								// Relative to current namespace
								$this->operationQueue->queue(new Refactoring\Operation\ChangeRelativeNameInDocComment($docComment, $tagName, $tagValue, $this->newName, $this->namespace));
							}
						}
					}
				}
			}
		}
	}
}
