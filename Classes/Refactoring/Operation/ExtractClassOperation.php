<?php
namespace TYPO3\Zubrovka\Refactoring\Operation;

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
use TYPO3\FLOW3\Utility\Files;

/**
 * @FLOW3\Scope("prototype")
 */
class ExtractClassOperation extends AbstractOperation {

	/**
	 * Contains the class node
	 *
	 * @var \PHPParser_Node_Stmt
	 */
	protected $node;

	/**
	 * Contains the target class filename
	 *
	 * @var array
	 */
	protected $targetClassFile;

	/**
	 * Contains prettyPrinter
	 *
	 * @var \TYPO3\Zubrovka\PrettyPrinter\TYPO3CGLPrettyPrinter
	 */
	protected $prettyPrinter;

	/**
	 * @param \PHPParser_Node_Stmt $node
	 * @param array $parts
	 */
	function __construct(\PHPParser_Node_Stmt $node, $targetClassFile) {
		$this->targetClassFile = $targetClassFile;
		parent::__construct($node);
	}

	/**
	 * Injector method for a \TYPO3\Zubrovka\PrettyPrinter\TYPO3CGLPrettyPrinter
	 *
	 * @param \TYPO3\Zubrovka\PrettyPrinter\TYPO3CGLPrettyPrinter
	 */
	public function injectPrettyPrinter(\TYPO3\Zubrovka\PrettyPrinter\TYPO3CGLPrettyPrinter $prettyPrinter) {
		$this->prettyPrinter = $prettyPrinter;
	}

	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		if (is_file($this->targetClassFile)) {
			return false;
		}
		$parent = $this->node->getParent();
		if (NULL !== $parent) {
			$parentSubNodeName = ucfirst($this->node->getParentSubNodeName());
			$singularName = substr($parentSubNodeName, -1) == 's' ? substr($parentSubNodeName, 0,  -1) : null;
			if (is_callable(array($parent, 'remove' . $parentSubNodeName))) {
				$parent->{'remove' . $parentSubNodeName}($this->node);
			} elseif (NULL !== $singularName && is_callable(array($parent, 'remove' . $singularName))) {
				$parent->{'remove' . $singularName}($this->node);
			} else {
				$parent->{'set' . $parentSubNodeName}();
			}
		} else {
			foreach ($stmts as $key => $stmt) {
				if ($stmt === $this->node) {
					$stmts[$key] = new \TYPO3\Zubrovka\Parser\Node\BlankNode(-1, array(new \PHPParser_Node_Ignorable_Comment('/*
					* @deprecated since 6.0, the classname ' . $this->node->getName() . ' and this file is obsolete
					* and will be removed by 7.0. The class was renamed and is now located at:
					* ' .str_replace('/Users/tmaroschik/Sites/Core/', '', $this->targetClassFile) . '
					*/')));
					break;
				}
			}
		}
		$targetDirectory = dirname($this->targetClassFile);
		Files::createDirectoryRecursively($targetDirectory);
		return (bool) file_put_contents($this->targetClassFile, '<?php' . PHP_EOL . $this->prettyPrinter->prettyPrint(array($this->node)) . PHP_EOL . '?>');
	}


}
