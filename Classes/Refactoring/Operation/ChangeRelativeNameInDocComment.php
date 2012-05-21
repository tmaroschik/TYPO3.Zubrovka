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
class ChangeRelativeNameInDocComment extends AbstractOperation {

	/**
	 * @var string
	 */
	protected $tagName;

	/**
	 * @var string
	 */
	protected $tagValue;

	/**
	 * @var null|\PHPParser_Node_Name
	 */
	protected $alias;

	/**
	 * @var null|\PHPParser_Node_Name
	 */
	protected $namespace;

	/**
	 * @var array
	 */
	protected $relativeNameParts;

	/**
	 * @param \PHPParser_Node $
	 * @param \PHPParser_Node_Name $newName
	 */
	public function __construct(\PHPParser_Node $node, $tagName, $tagValue, \PHPParser_Node_Name $newName, $namespace = null, $alias = null) {
		$this->node = $node;
		$this->tagName = $tagName;
		$this->tagValue = $tagValue;
		$this->newName = $newName;
		$this->namespace = $namespace;
		$this->alias = $alias;
	}

	/**
	 * @param array $nodes
	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
		if (NULL !== $alias = $this->alias) {
			if ($alias->name->parts != array_slice($this->newName->parts, 0, count($alias->name->parts))) {
				// Leaves imported namespace
				$queuedUseStatementChanges = array_filter(iterator_to_array($queue), function($operation) use ($alias) {
					/** @var $operation OperationInterface */
					if ($operation instanceof ChangeUseStatement && $operation->getNode()->name->parts == $alias->name->parts) {
						return true;
					} else {
						return false;
					}
				});
				if (empty($queuedUseStatementChanges)) {
					$changeUseStatement = new ChangeUseStatement($alias, $this->newName);
					$queue->queue($changeUseStatement);
					$changeUseStatement->prepare($nodes, $queue);
				}
				$this->relativeNameParts = array_merge(array($alias->alias), array_slice($this->newName->parts, count($alias->name->parts)));
			} else {
				// Stays in imported namespace
				$this->relativeNameParts = array_slice($this->newName->parts, count($alias->name->parts) - 1);
				if ($alias->alias != $this->relativeNameParts[0]) {
					$this->relativeNameParts[0] = $alias->alias;
				}
			}
		} elseif (NULL !== $namespace = $this->namespace) {
			if ($namespace->parts != array_slice($this->newName->parts, 0, count($namespace->parts))) {
				// Leaves namespace
				//TODO implement addition of use statement
			} else {
				// Stays in namespace
				$this->relativeNameParts = array_slice($this->newName->parts, count($namespace->parts));
			}
		}
	}

	/**
	 * @return void
	 */
	public function run() {
		if (!empty($this->relativeNameParts) && isset($this->node->tags[$this->tagName])) {
			foreach ($this->node->tags[$this->tagName] as &$tagValue) {
				if ($tagValue == $this->tagValue) {
					$typeAndComment = preg_split('/\s/', $tagValue, 2);
					$tagValue = implode('\\', $this->relativeNameParts);
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
