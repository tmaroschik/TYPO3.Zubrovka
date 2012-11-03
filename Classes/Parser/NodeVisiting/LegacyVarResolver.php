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
use TYPO3\Zubrovka\Parser\Node;

/**
 * @FLOW3\Scope("prototype")
 */
class LegacyVarResolver extends \PHPParser_NodeVisitorAbstract {

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function leaveNode(\PHPParser_Node $node) {
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Property || $node instanceof \PHPParser_Node_Stmt_ClassMethod:
				/** @var $node \PHPParser_Node_Stmt_Property|\PHPParser_Node_Stmt_ClassMethod */
				if ($node->getType() === \PHPParser_Node_Stmt_Class::MODIFIER_LEGACY) {
					$this->addTodo($node);
				}
				break;
		}
	}

	/**
	 * @param \PHPParser_Node $node
	 */
	protected function addTodo(\PHPParser_Node $node) {
		/** @var $node \PHPParser_Node_Stmt_Property|\PHPParser_Node_Stmt_ClassMethod */
		$node->setType(\PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC);
		$ignorables = $node->getIgnorables();
		$docComments = array_filter($ignorables, function($ignorable) {
			return $ignorable instanceof \PHPParser_Node_Ignorable_DocComment;
		});
		/** @var $docComment Node\DocCommentContainingTags */
		if (empty($docComments)) {
			$docComment = new Node\DocCommentContainingTags('');
			$ignorables[] = $docComment;
		} else {
			$docComment = array_pop($docComments);
		}
		$tags = $docComment->getTags();
		$tags[] = new Node\DocCommentTag('todo', NULL, NULL, 'Define visibility');
		$docComment->setTags($tags);
		$node->setIgnorables($ignorables);
	}

}