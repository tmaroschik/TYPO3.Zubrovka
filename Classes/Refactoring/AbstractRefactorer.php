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
 * @FLOW3\Scope("singleton")
 */
abstract class AbstractRefactorer implements RefactorerInterface  {

	/**
	 * Contains missions
	 *
	 * @var Mission\MissionInterface[]
	 */
	protected $missions;

	/**
	 * Contains transaction builder
	 *
	 * @var TransactionBuilder
	 */
	protected $transactionBuilder;

	/**
	 * Contains parser
	 *
	 * @var \PHPParser_Parser
	 */
	protected $parser;

	/**
	 * Contains stmts
	 *
	 * @var array
	 */
	protected $stmts;

	/**
	 * Constructor method for a refactorer
	 */
	function __construct() {
		$this->parser = new \PHPParser_Parser;
		$this->traverser = new \PHPParser_NodeTraverser;
		$this->transactionBuilder = new TransactionBuilder;
	}

	/**
	 * @param Mission\MissionInterface[] $missions
	 */
	public function setMissions($missions) {
		$this->missions = $missions;
		return $this;
	}

	/**
	 * @return Mission\MissionInterface[]
	 */
	public function getMissions() {
		return $this->missions;
	}

	/**
	 * @param Mission\MissionInterface $mission
	 */
	public function appendMission(Mission\MissionInterface $mission) {
		if (!is_array($this->missions)) {
			$this->missions = array();
		}
		$this->missions[] = $mission;
		return $this;
	}

	/**
	 * @param Mission\MissionInterface $mission
	 */
	public function removeMission(Mission\MissionInterface $mission) {
		if (!is_array($this->missions)) {
			foreach ($this->missions as $key => $existingMission) {
				if ($mission === $existingMission) {
					unset($this->missions[$key]);
					break;
				}
			}
		}
		return $this;
	}

	/**
	 * @param Mission\MissionInterface $missionNew
	 * @param Mission\MissionInterface $missionOld
	 */
	public function replaceMission(Mission\MissionInterface $missionNew, Mission\MissionInterface $missionOld) {
		if (!is_array($this->missions)) {
			foreach ($this->missions as $key => $existingMission) {
				if ($missionOld === $existingMission) {
					$this->missions[$key] = $missionNew;
					break;
				}
			}
		}
	}

	/**
	 * @return Objective\ObjectiveInterface[]
	 */
	protected function analyze() {
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->appendVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->appendVisitor (new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		foreach ($this->missions as $mission) {
			$traverser->appendVisitor($mission->getAnalyzer());
		}
		$this->stmts = $traverser->traverse($this->stmts);
		return $this->getObjectivesFromAnalyzers($traverser->getVisitors());
	}

	/**
	 * @param \PHPParser_NodeTraverser $traverser
	 * @return \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	protected function getObjectivesFromAnalyzers(array $analyzers) {
		$objectives = array();
		foreach ($analyzers as $analyzer) {
			if ($analyzer instanceof \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface) {
				/** @var $analyzer \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface */
				$objectives = array_merge($objectives, $analyzer->getObjectives());
			}
		}
		return $objectives;
	}

}