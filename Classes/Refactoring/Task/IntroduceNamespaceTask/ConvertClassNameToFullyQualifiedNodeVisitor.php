<?php
namespace TYPO3\Zubrovka\Refactoring\Task\IntroduceNamespaceTask;

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
class ConvertClassNameToFullyQualifiedNodeVisitor extends \PHPParser_NodeVisitorAbstract {

	/**
	 * @var \PHPParser_Node[]
	 */
	protected $alreadyChangedNodes;

	/**
	 * @var \PHPParser_Node[]
	 */
	protected $nodesToBeChanged;

	/**
	 * @param \PHPParser_Node[] $alreadyChangedNodes
	 */
	public function __construct(array $alreadyChangedNodes = array()) {
		$this->alreadyChangedNodes = $alreadyChangedNodes;
	}

	/**
	 * @param array $nodes
	 */
	public function beforeTraverse(array $nodes) {
		$this->nodesToBeChanged = array();
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
				if (NULL !== $node->getExtends()) {
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
	 * @param \PHPParser_Node $node
	 * @param string $property
	 */
	protected function checkRenaming(\PHPParser_Node $node) {
		if (in_array($node, $this->alreadyChangedNodes, true)) {
			return;
		}
		switch ($node) {
			case $node instanceof \PHPParser_Node_Name_FullyQualified:
			case $node instanceof \PHPParser_Node_Name_Relative:
			case $node instanceof \PHPParser_Node_Name:
				if (!$this->isScalar((string) $node)) {
					$this->nodesToBeChanged[] = $node;
				}
				break;
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function checkRenamingInDocComment(\PHPParser_Node $node) {
		if (in_array($node, $this->alreadyChangedNodes, true)) {
			return;
		}
		$docComments = array_filter($node->getIgnorables(), function($ignorable) {
			return $ignorable instanceof \TYPO3\Zubrovka\Parser\Node\DocCommentContainingNames;
		});
		foreach ($docComments as $docComment) {
			$allowedTags = array('param', 'var', 'return');
			$tagsValues = $docComment->getTagsValues();
			/** @var $docComment \TYPO3\Zubrovka\Parser\Node\DocCommentContainingNames */
			foreach ($tagsValues as $tagName => &$tagValues) {
				if (in_array(strtolower($tagName), $allowedTags)) {
					foreach ($tagValues as &$tagValue) {
						if ($tagValue instanceof \PHPParser_Node_Name) {
							$this->checkRenaming($tagValue);
						}
					}
				}
			}
		}
	}

	/**
	 * Normalize data types so they match the PHP type names:
	 *  int -> integer
	 *  double -> float
	 *  bool -> boolean
	 *
	 * @param string $type Data type to unify
	 * @return string unified data type
	 */
	protected function isScalar($type) {
		$scalarPattern = '/^(?:integer|int|float|double|boolean|bool|string|array)$/';
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

	/**
	 * @return \PHPParser_Node[]
	 */
	public function getNodesToBeChanged() {
		return $this->nodesToBeChanged;
	}

}
