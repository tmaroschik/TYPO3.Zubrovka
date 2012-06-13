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
		$this->namespace = NULL;
		$this->aliases = array();
	}

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function enterNode(\PHPParser_Node $node) {
		$this->resolveNamesInDocComment($node);
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
			case $node instanceof \PHPParser_Node_Stmt_Class:
				/** @var $node \PHPParser_Node_Stmt_Class */
				if (NULL !== $node->getExtends()) {
					$node->setExtends($this->resolveClassName($node->getExtends()));
				}
				$implements = $node->getImplements();
				foreach ($implements as &$interface) {
					$interface = $this->resolveClassName($interface);
				}
				$node->setImplements($implements);
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Interface:
				/** @var $node \PHPParser_Node_Stmt_Interface */
				$extends = $node->getExtends();
				foreach ($extends as &$interface) {
					$interface = $this->resolveClassName($interface);
				}
				$node->setExtends($extends);
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Trait:
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Function:
				$this->addNamespacedName($node);
				break;
			case $node instanceof \PHPParser_Node_Stmt_Const:
				/** @var $node \PHPParser_Node_Stmt_Const */
				foreach ($node->getConsts() as $const) {
					$this->addNamespacedName($const);
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_StaticCall
					|| $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
					|| $node instanceof \PHPParser_Node_Expr_ClassConstFetch
					|| $node instanceof \PHPParser_Node_Expr_New
					|| $node instanceof \PHPParser_Node_Expr_Instanceof:
				/** @var $node \PHPParser_Node_Expr_StaticCall */
				if ($node->getClass() instanceof \PHPParser_Node_Name) {
					$node->setClass($this->resolveClassName($node->getClass()));
				}
				break;
			case $node instanceof \PHPParser_Node_Expr_FuncCall
					|| $node instanceof \PHPParser_Node_Expr_ConstFetch:
				/** @var $node \PHPParser_Node_Expr_FuncCall */
				if ($node->getName() instanceof \PHPParser_Node_Name) {
					$node->setName($this->resolveOtherName($node->getName()));
				}
				break;
			case $node instanceof \PHPParser_Node_Stmt_TraitUse:
				/** @var $node \PHPParser_Node_Stmt_TraitUse */
				$traits = $node->getTraits();
				foreach ($traits as &$trait) {
					$trait = $this->resolveClassName($trait);
				}
				$node->setTraits($traits);
				break;
			case $node instanceof \PHPParser_Node_Param:
				/** @var $node \PHPParser_Node_Param */
				if ($node->getType() instanceof \PHPParser_Node_Name) {
					$node->setType($this->resolveClassName($node->getType()));
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
			if (NULL !== $this->namespace) {
				$name->setAttribute('namespacedName', new \PHPParser_Node_Name_FullyQualified($name));
			}
			return $name;
		}
		// resolve aliases (for relative names)
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			// has an alias
			/** @var $useStmt \PHPParser_Node_Stmt_UseUse */
			$useStmt = $this->aliases[$name->getFirst()];
			$namespacedName = new \PHPParser_Node_Name_FullyQualified($useStmt->getName()->getParts());
			$namespacedName->append(array_slice($name->getParts(), 1));
			$name->setAttribute('namespacedName', $namespacedName);
			return $name;
		} elseif (NULL !== $this->namespace) {
			$name = new \PHPParser_Node_Name_Relative($name->getParts());
			if (NULL !== $this->namespace) {
				$namespacedName = new \PHPParser_Node_Name_FullyQualified($this->namespace);
				$namespacedName->append($name->getParts());
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
			if (NULL !== $this->namespace) {
				$name->setAttribute('namespacedName', clone $name);
			}
			return $name;
		} elseif ($name->isUnqualified()) {
			if (NULL !== $this->namespace && count($name->getParts()) > 1) {
				$namespacedName = clone $name;
				$namespacedName->prepend($this->namespace);
				$name->setAttribute('namespacedName', $namespacedName);
			}
			return $name;
		}
		if ($name->isQualified() && isset($this->aliases[$name->getFirst()])) {
			// resolve aliases for qualified names
			$name->setFirst($this->aliases[$name->getFirst()]->getName());
		} elseif (NULL !== $this->namespace) {
			// prepend namespace for relative names
			$name->prepend($this->namespace);
		}
		$fullyQualifiedName = new \PHPParser_Node_Name_FullyQualified($name->getParts(), $name->getIgnorables());
		$fullyQualifiedName->setAttribute('namespacedName', clone $fullyQualifiedName);
		return $fullyQualifiedName;
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function resolveNamesInDocComment(\PHPParser_Node $node) {
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
							$tagValue = $this->resolveClassName($tagValue);
						}
					}
				}
			}
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function addNamespacedName(\PHPParser_Node $node) {
		if (NULL !== $this->namespace) {
			$namespacedName = new \PHPParser_Node_Name_FullyQualified($this->namespace);
			$namespacedName->append($node->getName());
			$node->setAttribute('namespacedName', $namespacedName);
		}
	}
}