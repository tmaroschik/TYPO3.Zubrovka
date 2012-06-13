<?php
namespace TYPO3\Zubrovka\Parser\NodeVisiting;

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
class NamespaceResolver extends \PHPParser_NodeVisitorAbstract {

	/**
	 * @var null|\PHPParser_Node_Stmt_Namespace Current namespace
	 */
	protected $namespace;

	/**
	 * @var array Currently defined namespace and class aliases
	 */
	protected $aliases;

	/**
	 * @var array
	 */
	protected $namespaceUsedBy;

	/**
	 * @var array
	 */
	protected $aliasesUsedBy;

	public function beforeTraverse(array $nodes) {
		$this->namespace = NULL;
		$this->namespaceUsedBy = array();
		$this->aliases   = array();
		$this->aliasesUsedBy = array();
	}

	public function enterNode(\PHPParser_Node $node) {
		$this->resolveNamespaceUsageInDocComment($node);
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Namespace:
				/** @var $node \PHPParser_Node_Stmt_Namespace */
				$this->namespace = $node;
				$this->namespaceUsedBy = array();
				$this->aliases   = array();
				$this->aliasesUsedBy = array();
				break;
			case $node instanceof \PHPParser_Node_Stmt_UseUse:
				/** @var $node \PHPParser_Node_Stmt_UseUse */
				if (isset($this->aliases[$node->getAlias()])) {
					throw new \PHPParser_Error(sprintf('Cannot use "%s" as "%s" because the name is already in use', $node->getName(), $node->getAlias()), $node->getLine());
				}
				$this->aliases[$node->getAlias()] = $node;
				break;
			case $node instanceof \PHPParser_Node_Stmt_Class:
				/** @var $node \PHPParser_Node_Stmt_Class */
				if (NULL !== $node->getExtends()) {
					$this->resolveNamespaceUsage($node->getExtends());
				}
				foreach ($node->getImplements() as $interface) {
					$this->resolveNamespaceUsage($interface);
				}
				$this->addNamespaceUsage($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				/** @var $node \PHPParser_Node_Stmt_Interface */
				foreach ($node->getExtends() as $interface) {
					$this->resolveNamespaceUsage($interface);
				}
				$this->addNamespaceUsage($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Trait:
				$this->addNamespaceUsage($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Function:
				$this->addNamespaceUsage($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Const:
				/** @var $node \PHPParser_Node_Stmt_Const */
				foreach ($node->getConsts() as $const) {
					$this->addNamespaceUsage($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				/** @var $node \PHPParser_Node_Expr_StaticCall */
				if ($node->getClass() instanceof \PHPParser_Node_Name) {
					$this->resolveNamespaceUsage($node->getClass());
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				/** @var $node \PHPParser_Node_Expr_FuncCall */
				if ($node->getName() instanceof \PHPParser_Node_Name) {
					$this->resolveOtherNameUsage($node->getName());
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				/** @var $node \PHPParser_Node_Stmt_TraitUse */
				foreach ($node->getTraits() as $trait) {
					$this->resolveNamespaceUsage($trait);
				}
				break;
			case $node instanceof \PHPParser_Node_Param:
				/** @var $node \PHPParser_Node_Param */
				if ($node->getType() instanceof \PHPParser_Node_Name) {
					$this->resolveNamespaceUsage($node->getType());
				}
				break;
		}
	}

	public function leaveNode(\PHPParser_Node $node) {
		if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
			if (NULL !== $this->namespace) {
				$this->namespace->setAttribute('usedBy', $this->namespaceUsedBy);
			}
			/** @var $alias \PHPParser_Node_Stmt_UseUse */
			foreach ($this->aliases as $key => $alias) {
				if (isset($this->aliasesUsedBy[$key])) {
					$alias->setAttribute('usedBy', $this->aliasesUsedBy[$key]);
				}
			}
		}
	}

	/**
	 * @param \PHPParser_Node_Name $name
	 * @return void
	 */
	protected function resolveNamespaceUsage(\PHPParser_Node_Name $name) {
		// don't resolve special class names
		if (in_array((string)$name, array('self', 'parent', 'static'))) {
			return;
		}
		// fully qualified names are already resolved
		if ($name->isFullyQualified()) {
			if (NULL !== $this->namespace) {
				$this->namespaceUsedBy[] = $name;
				$name->setAttribute('namespace', $this->namespace);
			}
			return;
		}
		// resolve aliases (for relative names)
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			// has an alias
			$this->aliasesUsedBy[$name->getFirst()][] = $name;
			$name->setAttribute('alias', $this->aliases[$name->getFirst()]);
			return;
		} elseif (NULL !== $this->namespace) {
				$this->namespaceUsedBy[] = $name;
				$name->setAttribute('namespace', $this->namespace);
			return;
		}
	}

	/**
	 * @param \PHPParser_Node_Name $name
	 * @return void
	 */
	protected function resolveOtherNameUsage(\PHPParser_Node_Name $name) {
		// fully qualified names are already resolved and we can't do anything about unqualified
		// ones at compiler-time
		if ($name->isFullyQualified()) {
			if (NULL !== $this->namespace) {
				$this->namespaceUsedBy[] = $name;
				$name->setAttribute('namespace', $this->namespace);
			}
			return;
		} elseif ($name->isUnqualified()) {
			if (NULL !== $this->namespace && count($name->getParts()) > 1) {
				$this->namespaceUsedBy[] = $name;
				$name->setAttribute('namespace', $this->namespace);
			}
			return;
		}
		// resolve aliases for qualified names
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			$this->namespaceUsedBy[] = $name;
			$name->setAttribute('namespace', $this->namespace);
			return;
		} elseif (NULL !== $this->namespace) {
			$this->namespaceUsedBy[] = $name;
			$name->setAttribute('namespace', $this->namespace);
			return;
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function resolveNamespaceUsageInDocComment(\PHPParser_Node $node) {
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
							$this->resolveNamespaceUsage($tagValue);
						}
					}
				}
			}
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function addNamespaceUsage(\PHPParser_Node $node) {
		if (NULL !== $this->namespace) {
			$this->namespaceUsedBy[] = $node;
			$node->setAttribute('namespace', $this->namespace);
		}
	}
}