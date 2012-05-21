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
class NamespaceConverter extends \PHPParser_NodeVisitorAbstract {

	public function leaveNode(\PHPParser_Node $node) {
		if ($node instanceof \PHPParser_Node_Name) {
			return new \PHPParser_Node_Name(str_replace('TYPO3_FLOW3', 'Drupal', $node->toString('_')));
		} elseif ($node instanceof \PHPParser_Node_Stmt_Class
				|| $node instanceof \PHPParser_Node_Stmt_Interface
				|| $node instanceof \PHPParser_Node_Stmt_Function
		) {
			$node->name = $node->namespacedName->toString('_');
			$node->name = str_replace('TYPO3_FLOW3', 'Drupal', $node->name);
		} elseif ($node instanceof \PHPParser_Node_Stmt_Const) {
			foreach ($node->consts as $const) {
				$const->name = $const->namespacedName->toString('_');
				$const->name = str_replace('TYPO3_FLOW3', 'Drupal', $const->name);
			}
		} elseif ($node instanceof \PHPParser_Node_Stmt_Namespace) {
			// returning an array merges is into the parent array
			return $node->stmts;
		} elseif ($node instanceof \PHPParser_Node_Stmt_Use) {
			// returning false removed the node altogether
			return false;
		}
	}

}
