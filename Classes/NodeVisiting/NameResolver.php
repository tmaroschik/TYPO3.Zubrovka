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
class NameResolver extends \PHPParser_NodeVisitorAbstract {

	/**
	 * @var null|\PHPParser_Node_Name Current namespace
	 */
	protected $namespace;

	/**
	 * @var array Currently defined namespace and class aliases
	 */
	protected $aliases;

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
			case $node instanceof \PHPParser_Node_Stmt_Class:
				if (null !== $node->extends) {
					$node->extends = $this->resolveClassName($node->extends);
				}
				foreach ($node->implements as &$interface) {
					$interface = $this->resolveClassName($interface);
				}
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				foreach ($node->extends as &$interface) {
					$interface = $this->resolveClassName($interface);
				}
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Trait:
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Function:
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Const:
				foreach ($node->consts as $const) {
					$this->addNamespacedName($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				if ($node->class instanceof \PHPParser_Node_Name) {
					$node->class = $this->resolveClassName($node->class);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				if ($node->name instanceof \PHPParser_Node_Name) {
					$node->name = $this->resolveOtherName($node->name);
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				foreach ($node->traits as &$trait) {
					$trait = $this->resolveClassName($trait);
				}
				break;
			case $node instanceof \PHPParser_Node_Param:
				if ($node->type instanceof \PHPParser_Node_Name) {
					$node->type = $this->resolveClassName($node->type);
				}
				break;
		}
	}

	/**
	 * @param \PHPParser_Node_Name $name
	 * @return \PHPParser_Node_Name|\PHPParser_Node_Name_Relative
	 */
	protected function resolveClassName(\PHPParser_Node_Name $name) {
		// don't resolve special class names
		if (in_array((string)$name, array('self', 'parent', 'static'))) {
			return $name;
		}
		// fully qualified names are already resolved
		if ($name->isFullyQualified()) {
			if (null !== $this->namespace) {
				$name->setAttribute('namespacedName', new \PHPParser_Node_Name_FullyQualified($name));
			}
			return $name;
		}
		// resolve aliases (for relative names)
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			// has an alias
			$useStmt        = $this->aliases[$name->getFirst()];
			$namespacedName = new \PHPParser_Node_Name_FullyQualified($useStmt->name->parts);
			$namespacedName->append(array_slice($name->parts, 1));
			$name->setAttribute('namespacedName', $namespacedName);
			return $name;
		} elseif (NULL !== $this->namespace) {
			$name = new \PHPParser_Node_Name_Relative($name->parts);
			if (null !== $this->namespace) {
				$namespacedName = new \PHPParser_Node_Name_FullyQualified($this->namespace);
				$namespacedName->append($name->parts);
				$name->setAttribute('namespacedName', $namespacedName);
			}
			return $name;
		}
		return $name;
	}

	/**
	 * @param \PHPParser_Node_Name $name
	 * @return \PHPParser_Node_Name|\PHPParser_Node_Name_FullyQualified
	 */
	protected function resolveOtherName(\PHPParser_Node_Name $name) {
		// fully qualified names are already resolved and we can't do anything about unqualified
		// ones at compiler-time
		if ($name->isFullyQualified()) {
			if (null !== $this->namespace) {
				$name->setAttribute('namespacedName', clone $name);
			}
			return $name;
		} elseif ($name->isUnqualified()) {
			if (null !== $this->namespace) {
				$namespacedName = clone $name;
				$namespacedName->prepend($this->namespace);
				$name->setAttribute('namespacedName', $namespacedName);
			}
			return $name;
		}
		// resolve aliases for qualified names
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			$name->setFirst($this->aliases[$name->getFirst()]->name);
			// prepend namespace for relative names
		} elseif (null !== $this->namespace) {
			$name->prepend($this->namespace);
		}
		$fullyQualifiedName = new \PHPParser_Node_Name_FullyQualified($name->parts);
		$fullyQualifiedName->setAttribute('namespacedName', clone $fullyQualifiedName);
		return $fullyQualifiedName;
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function addNamespacedName(\PHPParser_Node $node) {
		if (null !== $this->namespace) {
			$namespacedName = new \PHPParser_Node_Name_FullyQualified($this->namespace);
			$namespacedName->append($node->name);
			$node->setAttribute('namespacedName', $namespacedName);
		}
	}
}