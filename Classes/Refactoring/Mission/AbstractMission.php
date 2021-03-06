<?php
namespace TYPO3\Zubrovka\Refactoring\Mission;

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
abstract class AbstractMission implements MissionInterface  {


	/**
	 * Contains analyzer
	 *
	 * @var \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface
	 */
	protected $analyzer;

	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Analysis\AnalyzerInterface
	 */
	public function getAnalyzer() {
		return $this->analyzer;
	}

}