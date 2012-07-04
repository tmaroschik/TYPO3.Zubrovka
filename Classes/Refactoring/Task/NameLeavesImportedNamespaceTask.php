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
use TYPO3\Zubrovka\Refactoring\Objective;

/**
 * @FLOW3\Scope("prototype")
 */
class NameLeavesImportedNamespaceTask extends AbstractSubObjectiveTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective',
		'TYPO3\Zubrovka\Refactoring\Objective\NameLeavesImportedNamespaceObjective{*}',
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		// TODO pay attention to the right namespace as multiple namespace and class declarations can occur in the same file
		list($objectivesByNamespace, $namespaces, $mandatoryNamespaceChangeObjectives) = $this->getObjectivesByNamespace();
		$nodesToBeChanged = $this->getNodesToBeChanged($this->objectives);
		foreach ($mandatoryNamespaceChangeObjectives as $mandatoryNamespaceChange) {
			/** @var $mandatoryNamespaceChange \TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective */
			$nodesNotLeavingNamespace = $this->getNodesNotLeavingNamespace($mandatoryNamespaceChange->getNode(), $nodesToBeChanged);
			if (!empty($nodesNotLeavingNamespace)) {
				$this->subObjectives[] = clone $mandatoryNamespaceChange;
				foreach ($nodesNotLeavingNamespace as $nodeNotLeavingNamespace) {
					$this->subObjectives[] = new Objective\NameLeavesImportedNamespaceObjective($nodeNotLeavingNamespace, $nodeNotLeavingNamespace->getAttribute('namespacedName'));
				}
			}
		}
		if (!empty($this->subObjectives)) {
			return -50;
		}
		$newSharedNamespaces = array();
		foreach ($mandatoryNamespaceChangeObjectives as $mandatoryNamespaceChange) {
			$newSharedNamespaces['\\' . implode('\\', $mandatoryNamespaceChange->getNewName())] = 'namespace';
			$this->operations[] = $this->operationFactory->create(
				'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
				$mandatoryNamespaceChange->getNode()->getName(), $mandatoryNamespaceChange->getNewName()
			);
		}
		$commonTargetNamespaces = $this->getCommonTargetNamespaces($this->objectives);
		$changeRelativeNames = array();
		foreach ($objectivesByNamespace as $objectives) {
			foreach ($objectives as $objective) {
				$existingNamespaceParts = $this->getMatchingNamespaceParts($objective, $newSharedNamespaces);
				$existingNamespaceString = '\\' . implode('\\', $existingNamespaceParts);
				if (empty($existingNamespaceParts)) {
					// There is no suitable namespace
					$commonNamespaceParts = $this->getMatchingNamespaceParts($objective, $commonTargetNamespaces);
					if (!empty($commonNamespaceParts)) {
						// Assign to that NS
						$commonNamespaceString = '\\' . implode('\\', $commonNamespaceParts);
						if (!isset($newSharedNamespaces[$commonNamespaceString])) {
							$newSharedNamespaces[$commonNamespaceString] = 'alias';
						}
						$relativeNameParts = array_slice($objective->getNewName()->getParts(), count($commonNamespaceParts));
						$changeRelativeNames[] = array(
							'namespace' => $commonNamespaceString,
							'node' => $objective->getNode(),
							'relativeNameParts' => $relativeNameParts
						);
					} else {
						$this->operations[] = $this->operationFactory->create(
							'\TYPO3\Zubrovka\Refactoring\Operation\ReplaceNameWithFullyQualifiedNameOperation',
							$objective->getNode(), clone $objective->getNewName()
						);
					}
				} else {
					// Assign to other namespace
					$relativeNameParts = array_slice($objective->getNewName()->getParts(), count($existingNamespaceParts));
					$changeRelativeNames[] = array(
						'namespace' => $existingNamespaceString,
						'node' => $objective->getNode(),
						'relativeNameParts' => $relativeNameParts
					);
				}
			}
		}
		$changeableUseStmts = array();
		foreach ($namespaces as $namespace) {
			if ($namespace instanceof \PHPParser_Node_Stmt_UseUse) {
				$nodesNotLeavingUseStmt = $this->getNodesNotLeavingNamespace($namespace, $nodesToBeChanged);
				if (empty($nodesNotLeavingUseStmt)) {
					$changeableUseStmts[] = $namespace;
				}
			}
		}
		$newNamespaces = array_keys(array_filter($newSharedNamespaces, function($value) {
			return $value == 'namespace';
		}));
		foreach ($newNamespaces as $newNamespace) {
			foreach ($changeRelativeNames as $crnKey => $relativeNameChange) {
				if ($relativeNameChange['namespace'] == $newNamespace) {
					$this->operations[] = $this->operationFactory->create(
						'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
						$relativeNameChange['node'], $relativeNameChange['relativeNameParts']
					);
					unset($changeRelativeNames[$crnKey]);
				}
			}
		}
		// TODO eliminate duplication and extract to some methods
		$newNamespaces = array_keys(array_filter($newSharedNamespaces, function($value) {
			return $value == 'alias';
		}));
		// Change all availabe use stmts
		foreach ($changeableUseStmts as $key => $changeableUseStmt) {
			if (isset($newNamespaces[$key])) {
				$alias = $changeableUseStmt->getAlias();
				$this->operations[] = $this->operationFactory->create(
					'\TYPO3\Zubrovka\Refactoring\Operation\ChangeUseStatementOperation',
					$changeableUseStmt, explode('\\', ltrim($newNamespaces[$key], '\\')), $alias
				);
				foreach ($changeRelativeNames as $crnKey => $relativeNameChange) {
					if ($relativeNameChange['namespace'] == $newNamespaces[$key]) {
						array_unshift($relativeNameChange['relativeNameParts'], $alias);
						$this->operations[] = $this->operationFactory->create(
							'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
							$relativeNameChange['node'], $relativeNameChange['relativeNameParts']
						);
						unset($changeRelativeNames[$crnKey]);
					}
				}
				unset($newNamespaces[$key]);
				unset($changeableUseStmts[$key]);
			}
		}
		foreach ($changeableUseStmts as $removeableUseStmt) {
			$this->operations[] = $this->operationFactory->create(
				'\TYPO3\Zubrovka\Refactoring\Operation\RemoveUseStatementOperation', $removeableUseStmt
			);
		}
		if (!empty($newNamespaces)) {
			// TODO, this has to happen at the right place.
			// Introduce a use statement
//			foreach ($newNamespaces as $introduceableUseStmt) {
//				$this->operations[] = $this->operationFactory->create(
//					'\TYPO3\Zubrovka\Refactoring\Operation\IntroduceUseStatementOperation', $introduceableUseStmt, explode('\\', ltrim($introduceableUseStmt, '\\'))
//				);
//			}
		}
		if (!empty($this->operations)) {
			return 50;
		}
		return 0;
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\NameLeavesImportedNamespaceObjective $objective
	 * @param array $targetNamespaces
	 * @return array
	 */
	protected  function getMatchingNamespaceParts($objective, $targetNamespaces) {
		$namespaceParts = array_slice($objective->getNewName()->getParts(), 0, -1);
		$potentialNamespace = '\\' . implode('\\', $namespaceParts);
		while (!isset($targetNamespaces[$potentialNamespace])) {
			$namespaceParts = array_slice($namespaceParts, 0, -1);
			if (count($namespaceParts) == 0) {
				break;
			}
		}
		return $namespaceParts;
	}

	/**
	 * @return array
	 */
	public function getObjectivesByNamespace() {
		// TODO split here by namespace only, not by alias. This enables to work on a single namespace at once.
		$objectivesByNamespace = array();
		$namespaces = array();
		$mandatoryNamespaceChangeObjectives = array();
		foreach ($this->objectives as $objective) {
			if ($objective instanceof Objective\NameLeavesImportedNamespaceObjective) {
				$namespaceNode = $objective->getNode()->getAttribute('alias') ? : $objective->getNode()->getAttribute('namespace');
				if (NULL !== $namespaceNode) {
					$namespace = (string) $namespaceNode->getName();
					if (!isset($namespaces[$namespace])) {
						$namespaces[$namespace] = $namespaceNode;
					}
					if (!isset($objectivesByNamespace[$namespace])) {
						$objectivesByNamespace[$namespace] = array();
					}
					$objectivesByNamespace[$namespace][] = $objective;
					$objective->getNewName();
				}
			} elseif ($objective instanceof Objective\ChangeNamespaceNameObjective) {
				$mandatoryNamespaceChangeObjectives[] = $objective;
			}
		}
		return array($objectivesByNamespace, $namespaces, $mandatoryNamespaceChangeObjectives);
	}

	/**
	 * @param Objective\NameLeavesImportedNamespaceObjective[] $objectives
	 */
	protected function getCommonTargetNamespaces(array $objectives) {
		$objectivesByNewName = array();
		foreach ($objectives as $objective) {
			if ($objective instanceof Objective\NameLeavesImportedNamespaceObjective) {
				$newName = '\\' . implode('\\', array_slice($objective->getNewName()->getParts(), 0, -1));
				if (!isset($objectivesByNewName[$newName])) {
					$objectivesByNewName[$newName] = 1;
				} else {
					$objectivesByNewName[$newName]++;
				}
			}
		}
		return array_flip(array_keys(array_filter($objectivesByNewName, function($value) {
			return $value > 1;
		})));
	}

	/**
	 * @param Objective\NameLeavesImportedNamespaceObjective[] $objectives $objectives
	 * @return \PHPParser_Node[]
	 */
	protected function getNodesToBeChanged($objectives) {
		$nodesToBeChanged = array();
		foreach ($objectives as $objective) {
			if ($objective instanceof Objective\ChangeNamespaceNameObjective) {
				foreach ($objective->getClassNodes() as $classNode) {
					$nodesToBeChanged[] = $classNode;
				}
			} elseif ($objective instanceof Objective\NameLeavesImportedNamespaceObjective) {
				$nodesToBeChanged[] = $objective->getNode();
			}
		}
		return $nodesToBeChanged;
	}

	/**
	 * @param \PHPParser_Node_Stmt $namespaceNode
	 * @param \PHPParser_Node[] $nodesToBeChanged
	 * @return \PHPParser_Node[]
	 */
	protected function getNodesNotLeavingNamespace(\PHPParser_Node_Stmt $namespaceNode, array $nodesToBeChanged) {
		$nodesUsingNamespace = $namespaceNode->getAttribute('usedBy');
		if (NULL !== $nodesUsingNamespace) {
			$nodesUsingNamespaceAfterNamespaceChange = array_filter($nodesUsingNamespace, function($nodeUsingNamespace) use($namespaceNode, $nodesToBeChanged) {
				return !in_array($nodeUsingNamespace, $nodesToBeChanged);
			});
			if (!empty($nodesUsingNamespaceAfterNamespaceChange)) {
				return $nodesUsingNamespaceAfterNamespaceChange;
			}
		}
		return array();
	}

}