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
		$this->namespace = null;
		$this->namespaceUsedBy = array();
		$this->aliases   = array();
		$this->aliasesUsedBy = array();
	}

	public function enterNode(\PHPParser_Node $node) {
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Namespace:
				$this->namespace = $node->name;
				$this->namespaceUsedBy = array();
				$this->aliases   = array();
				$this->aliasesUsedBy = array();
				break;
			case $node instanceof \PHPParser_Node_Stmt_UseUse:
				if (isset($this->aliases[$node->alias])) {
					throw new \PHPParser_Error(sprintf('Cannot use "%s" as "%s" because the name is already in use', $node->name, $node->alias), $node->getLine());
				}
				$this->aliases[$node->alias] = $node;
				break;
			case $node instanceof \PHPParser_Node_Stmt_Class:
				if (null !== $node->extends) {
					$this->resolveNamespaceUsage($node->extends);
				}
				foreach ($node->implements as &$interface) {
					$this->resolveNamespaceUsage($interface);
				}
				$this->addNamespaceUsage($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				foreach ($node->extends as &$interface) {
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
				foreach ($node->consts as $const) {
					$this->addNamespaceUsage($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				if ($node->class instanceof \PHPParser_Node_Name) {
					$this->resolveNamespaceUsage($node->class);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				if ($node->name instanceof \PHPParser_Node_Name) {
					$this->resolveOtherNameUsage($node->name);
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				foreach ($node->traits as &$trait) {
					$this->resolveNamespaceUsage($trait);
				}
				break;
			case $node instanceof \PHPParser_Node_Param:
				if ($node->type instanceof \PHPParser_Node_Name) {
					$this->resolveNamespaceUsage($node->type);
				}
				break;
		}
	}

	public function leaveNode(\PHPParser_Node $node) {
		if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
			if (null !== $this->namespace) {
				$this->namespace->setAttribute('usedBy', $this->namespaceUsedBy);
			}
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
			if (null !== $this->namespace) {
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
			if (null !== $this->namespace) {
				$this->namespaceUsedBy[] = $name;
				$name->setAttribute('namespace', $this->namespace);
			}
			return;
		} elseif ($name->isUnqualified()) {
			if (null !== $this->namespace) {
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
		} elseif (null !== $this->namespace) {
			$this->namespaceUsedBy[] = $name;
			$name->setAttribute('namespace', $this->namespace);
			return;
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function addNamespaceUsage(\PHPParser_Node $node) {
		if (null !== $this->namespace) {
			$this->namespaceUsedBy[] = $node;
			$node->setAttribute('namespace', $this->namespace);
		}
	}
}