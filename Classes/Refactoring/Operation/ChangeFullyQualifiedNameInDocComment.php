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
class ChangeFullyQualifiedNameInDocComment extends AbstractOperation {

	/**
	 * @var string
	 */
	protected $tagName;

	/**
	 * @var string
	 */
	protected $tagValue;

	/**
	 * @param \PHPParser_Node $
	 * @param \PHPParser_Node_Name $newName
	 */
	public function __construct(\PHPParser_Node $node, $tagName, $tagValue, \PHPParser_Node_Name $newName) {
		$this->node = $node;
		$this->tagName = $tagName;
		$this->tagValue = $tagValue;
		$this->newName = $newName;
	}

	/**
	 * @param array $nodes
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
		return $this;
	}

	/**
	 * @return void
	 */
	public function run() {
		if (isset($this->node->tags[$this->tagName])) {
			foreach ($this->node->tags[$this->tagName] as &$tagValue) {
				if ($tagValue == $this->tagValue) {
					$typeAndComment = preg_split('/\s/', $tagValue, 2);
					$tagValue = (string) $this->newName;
					$type = $typeAndComment[0] ?: '';
					if (substr($type, 0, 1) == '$') {
						$tagValue = $type . ' ' . $tagValue;
					} elseif (isset($typeAndComment[1])) {
						$tagValue = $tagValue . ' ' . $typeAndComment[1];
					}
					break;
				}
			}
		}
	}
}
