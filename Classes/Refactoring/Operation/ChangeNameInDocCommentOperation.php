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

/**
 * @FLOW3\Scope("prototype")
 */
class ChangeNameInDocCommentOperation extends AbstractOperation {

	/**
	 * @var \PHPParser_Node_Ignorable_DocComment
	 */
	protected $node;

	/**
	 * @var string
	 */
	protected $tagName;

	/**
	 * @var string
	 */
	protected $tagValue;

	/**
	 * @var string
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node $node
	 * @param string $tagName
	 * @param string $tagValue
	 * @param string $newName
	 */
	public function __construct(\PHPParser_Node_Ignorable_DocComment $node, $tagName, $tagValue, $newName) {
		$this->tagName = $tagName;
		$this->tagValue = $tagValue;
		$this->newName = $newName;
		parent::__construct($node);
	}

	/**
	 * @return bool
	 */
	public function execute() {
		$tagsValues =& $this->node->getTagsValues();
		foreach ($tagsValues[$this->tagName] as &$tagValue) {
			if ($tagValue == $this->tagValue) {
				$typeAndComment = preg_split('/\s/', $tagValue, 2);
				$tagValue = $this->newName;
				$type = $typeAndComment[0] ?: '';
				if (substr($type, 0, 1) == '$') {
					$tagValue = $type . ' ' . $tagValue;
				} elseif (isset($typeAndComment[1])) {
					$tagValue = $tagValue . ' ' . $typeAndComment[1];
				}
				return true;
			}
		}
		return false;
	}


}
