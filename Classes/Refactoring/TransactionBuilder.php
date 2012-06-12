<?php
namespace TYPO3\Zubrovka\Refactoring;

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
class TransactionBuilder {

	/**
	 * @var Task\TaskInterface[]
	 */
	protected $tasks = array();

	/**
	 * @param OperationFactory $operationFactory
	 */
	public function __construct() {
		$tasks = array(
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeClassNameTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeFullyQualifiedNameInDocCommentTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeNameTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeNamespaceNameTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeNameInDocCommentTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeNamespaceAndClassNameTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeRelativeNameInDocCommentTask',
			'\TYPO3\Zubrovka\Refactoring\Task\NameLeavesImportedNamespaceTask',


			'\TYPO3\Zubrovka\Refactoring\Task\ChangeRelativeNameLeavingImportedNamespaceTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeRelativeNameStayingInImportedNamespaceTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeRelativeNameLeavingNamespaceTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeRelativeNameStayingInNamespaceTask',
			'\TYPO3\Zubrovka\Refactoring\Task\ChangeNamespacedClassNameTask',
		);
		foreach ($tasks as $task) {
			$this->tasks[] = new $task();
		}
	}

	/**
	 * @param Objective\ObjectiveInterface[] $objectives
	 * @param Task\TaskInterface[] $tasks
	 * @return Transaction
	 * @throws \Exception
	 */
	public function build(array $objectives) {
		$unsatisfiedObjectives = $objectives;
		$counter = 0;
		while (!empty($unsatisfiedObjectives)) {
			list($satisfactionGraph, $subObjectives) = $this->getSatisfactionGraphAndSubObjectives($this->tasks, $unsatisfiedObjectives);
			$objectives = array_merge($objectives, $subObjectives);
			$unsatisfiedObjectives = array_merge($unsatisfiedObjectives, $subObjectives);
			$satisfactionGraph = $this->orderSatisfactionGraph($satisfactionGraph);
			$this->satisfyObjectives($satisfactionGraph, $unsatisfiedObjectives);
			if ($counter > 100) {
				$unsatisfiedCountByType = array();
				foreach ($unsatisfiedObjectives as $unsatisfiedObjective) {
					$class = get_class($unsatisfiedObjective);
					if (!isset($unsatisfiedCountByType[$class])) {
						$unsatisfiedCountByType[$class] = 1;
					} else {
						$unsatisfiedCountByType[$class]++;
					}
				}
				$list = array();
				foreach ($unsatisfiedCountByType as $type => $count) {
					$list[] = $type . '{' . $count . '};';
				}
				throw new Objective\UnsatisfiedObjectivesException('These objectives{count} could not be satisfied after 100 runs:' . implode(', ', $list) . '.', 1339153254);
				break;
			}
			$counter++;
		}
		$transaction = new Transaction();
		foreach ($objectives as $objective) {
			/** @var $objective Objective\ObjectiveInterface */
			$transaction->addOperations($objective->getTask()->getOperations());
		}
		return $transaction;
	}

	/**
	 * @param array $satisfactionGraph
	 * @param Objective\ObjectiveInterface[] $unsatisfiedObjectives
	 */
	protected function satisfyObjectives(array &$satisfactionGraph, array &$unsatisfiedObjectives) {
		foreach ($satisfactionGraph as $satisfactionGraphPart) {
			$alreadySatisfied = false;
			foreach ($satisfactionGraphPart['objectiveCombination'] as $objective) {
				if ($objective->isSatisfied()) {
					$alreadySatisfied = true;
					break;
				}
			}
			if ($alreadySatisfied) {
				continue;
			} else {
				foreach ($satisfactionGraphPart['objectiveCombination'] as $objective) {
					/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface */
					$objective->setTask($satisfactionGraphPart['task']);
					$removeableObjectives = array_keys($unsatisfiedObjectives, $objective);
					foreach ($removeableObjectives as $removeableObjectiveKey) {
						unset($unsatisfiedObjectives[$removeableObjectiveKey]);
					}
				}
			}
		}
	}

	/**
	 * @param array $satisfactionGraph
	 * @return array
	 */
	protected function orderSatisfactionGraph(array $satisfactionGraph) {
		uasort($satisfactionGraph, function($a, $b) {
			if (
				(count($a['objectiveCombination']) === count($b['objectiveCombination']) && $a['score'] < $b['score'])
				||
				($a['score'] === $b['score'] && count($a['objectiveCombination']) < count($b['objectiveCombination']))
			) {
				return -1;
			}
			return 1;
		});
		return $satisfactionGraph;
	}

	/**
	 * @param Task\TaskInterface[] $task
	 * @param Objective\ObjectiveInterface[] $objectives
	 * @return array
	 */
	protected function getSatisfactionGraphAndSubObjectives(array $tasks, array $objectives) {
		$satisfactionResuls = array();
		$subObjectives = array();
		foreach ($tasks as $task) {
			/** @var $task Task\TaskInterface */
			foreach ($this->buildPossibleObjectiveCombinations($objectives, $task->getSatisfiableObjectiveTypes()) as $objectiveCombination) {
				/** @var $localTask Task\TaskInterface */
				$localTask = clone $task;
				$score = $localTask->setObjectives($objectiveCombination)->canSatisfyObjectives();
				if (abs($score) > 0) {
					$satisfactionResuls[] = array(
						'score' => abs($score),
						'task'  => $localTask,
						'objectiveCombination'  => $objectiveCombination
					);
				}
				if ($localTask instanceof Task\SubObjectiveTaskInterface) {
					/** @var $localTask Task\SubObjectiveTaskInterface */
					$subObjectives = array_merge($subObjectives, $localTask->getSubObjectives());
				}
			}
		}
		return array($satisfactionResuls, $subObjectives);
	}

	/**
	 * @param Objective\ObjectiveInterface[] $objectives
	 * @param string[] $possibleObjectiveTypes
	 */
	protected function buildPossibleObjectiveCombinations(array $objectives, array $possibleObjectiveTypes) {
		$objectivesByType = array();
		foreach ($possibleObjectiveTypes as $possibleObjectiveType) {
			preg_match('/^(.*?)\{([0-9\*]+)\}$/', $possibleObjectiveType, $typeAndMultiplier);
			if (!empty($typeAndMultiplier) && isset($typeAndMultiplier[1])) {
				$possibleObjectiveType = $typeAndMultiplier[1];
			}
			$allObjectivesOfPossibleType = array_filter($objectives, function($objective) use ($possibleObjectiveType) {
				return $objective instanceof $possibleObjectiveType;
			});
			if (empty($allObjectivesOfPossibleType)) {
				continue;
			}
			if (!empty($typeAndMultiplier) && isset($typeAndMultiplier[2])) {
				if ($typeAndMultiplier[2] == '*') {
					$j = 1;
					foreach ($allObjectivesOfPossibleType as $possibleObjective) {
						$objectivesByType[$possibleObjectiveType . '|' . $j] = array($possibleObjective);
						$j++;
					}
				} elseif (is_numeric($typeAndMultiplier[2]) && $typeAndMultiplier[2] > 0 && $typeAndMultiplier[2] >= count($allObjectivesOfPossibleType)) {
					for ($i = 1; $i <= $typeAndMultiplier[2]; $i++) {
						$objectivesByType[$possibleObjectiveType . '|' . $i] = $allObjectivesOfPossibleType;
					}
				}
			} else {
				$objectivesByType[$possibleObjectiveType] = $allObjectivesOfPossibleType;
			}
		}
		if (empty($objectivesByType)) {
			return array();
		}
		return new \TYPO3\Zubrovka\Utility\CartesianProductIterator($objectivesByType);
	}


}
