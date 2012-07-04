<?php
namespace TYPO3\Zubrovka\Refactoring\Task;

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
class IntroduceNamespaceTask extends AbstractTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\IntroduceNamespaceObjective{*}',
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeNameObjective{*}',
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		$introduceNamespaceObjectives = array_filter($this->objectives, function ($objective) { return $objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\IntroduceNamespaceObjective; });
		if (empty($introduceNamespaceObjectives)) {
			return 0;
		}
		foreach ($introduceNamespaceObjectives as $introduceNamespaceObjective) {
			$this->operations[] = $this->operationFactory->create(
				'TYPO3\Zubrovka\Refactoring\Operation\IntroduceNamespaceOperation',
				$introduceNamespaceObjective->getNewName(), $introduceNamespaceObjective->getClassNodes()
			);
		}
		$namespacesByClassName = $this->getNamespacesByClassName($introduceNamespaceObjectives);
		$alreadyChangedNodes = array();
		foreach ($this->getChangeNameObjectivesByClassName() as $className => $changeNameObjectives) {
			foreach ($changeNameObjectives as $changeNameObjective) {
				/** @var $changeNameObjective \TYPO3\Zubrovka\Refactoring\Objective\ChangeNameObjective */
				$changeNameNode = $changeNameObjective->getNode();
				$newName = clone $changeNameObjective->getNewName();
				if ($namespacesByClassName[$className] == array_slice($newName->getParts(), 0, count($namespacesByClassName[$className]))) {
					$this->operations[] = $this->operationFactory->create(
						'TYPO3\Zubrovka\Refactoring\Operation\ReplaceNameWithRelativeNameOperation',
						$changeNameNode, new \PHPParser_Node_Name_Relative(array_slice($newName->getParts(), count($namespacesByClassName[$className])), $changeNameNode->getLine(), $changeNameNode->getIgnorables())
					);
				} else {
					$newName->setLine($changeNameNode->getLine())->setIgnorables($changeNameNode->getIgnorables());
					$this->operations[] = $this->operationFactory->create(
						'TYPO3\Zubrovka\Refactoring\Operation\ReplaceNameWithFullyQualifiedNameOperation',
						$changeNameNode, $newName
					);
				}
				$alreadyChangedNodes[] = $changeNameNode;
			}
		}
		$nodesToBeChanged = $this->getNodesToBeChanged($introduceNamespaceObjectives, $alreadyChangedNodes);
		foreach ($nodesToBeChanged as $nodeToBeChanged) {
			$this->operations[] = $this->operationFactory->create(
				'TYPO3\Zubrovka\Refactoring\Operation\ReplaceNameWithFullyQualifiedNameOperation',
				$nodeToBeChanged, new \PHPParser_Node_Name_FullyQualified($nodeToBeChanged->getParts(), $nodeToBeChanged->getLine(), $nodeToBeChanged->getIgnorables())
			);
		}
		if (!empty($this->objectives)) {
			return 50;
		}
		return 0;
	}

	protected function getNodesToBeChanged(array $introduceNamespaceObjectives, array $nodesAlreadyChanged) {
		$nodesToBeChanged = array();
		$convertClassNameVisitor = new IntroduceNamespaceTask\ConvertClassNameToFullyQualifiedNodeVisitor($nodesAlreadyChanged);
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->appendVisitor($convertClassNameVisitor);
		foreach ($introduceNamespaceObjectives as $introduceNamespaceObjective) {
			foreach ($introduceNamespaceObjective->getClassNodes() as $classNode) {
				$traverser->traverse($classNode->getStmts());
				$nodesToBeChanged += $convertClassNameVisitor->getNodesToBeChanged();
			}
		}
		return $nodesToBeChanged;
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[] $introduceNamespaceObjectives
	 * @return array
	 */
	protected function getNamespacesByClassName(array $introduceNamespaceObjectives) {
		$namespacesByClassName = array();
		foreach ($introduceNamespaceObjectives as $introduceNamespaceObjective) {
			/** @var $introduceNamespaceObjective \TYPO3\Zubrovka\Refactoring\Objective\IntroduceNamespaceObjective */
			foreach ($introduceNamespaceObjective->getClassNodes() as $classNode) {
				$namespacesByClassName[$classNode->getName()] = $introduceNamespaceObjective->getNewName();
			}
		}
		return $namespacesByClassName;
	}

	/**
	 * @return array
	 */
	public function getChangeNameObjectivesByClassName() {
		$objectivesByClass = array();
		foreach ($this->objectives as $objective) {
			if ($objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeNameObjective) {
				$currentNode = $objective->getNode();
				while(!$currentNode instanceof \PHPParser_Node_Stmt_Class) {
					$currentNode = $currentNode->getParent();
					if (NULL === $currentNode) {
						break;
					}
				}
				if (NULL !== $currentNode) {
					$className = $currentNode->getName();
					if (!isset($objectivesByClass[$className])) {
						$objectivesByClass[$className] = array();
					}
					$objectivesByClass[$className][] = $objective;
				}
			}
		}
		return $objectivesByClass;
	}

}