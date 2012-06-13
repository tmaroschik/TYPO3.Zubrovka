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
class DocCommentNameResolver extends \PHPParser_NodeVisitorAbstract {

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function enterNode(\PHPParser_Node $node) {
		$ignorables = $node->getIgnorables();
		if (!empty($ignorables)) {
			$docComments = array_filter($ignorables, function($value) {
				return $value instanceof \PHPParser_Node_Ignorable_DocComment;
			});
			if (!empty($docComments)) {
				foreach ($docComments as $key => $docComment) {
					$ignorables[$key] = new \TYPO3\Zubrovka\Parser\Node\DocCommentContainingNames($docComment->getValue(), $docComment->getLine());
				}
				$node->setIgnorables($ignorables);
			}
		}
	}

}