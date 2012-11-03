<?php
namespace TYPO3\Zubrovka\Scanning;

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
abstract class AbstractScanner implements ScannerInterface  {

	/**
	 * Contains parser
	 *
	 * @var \PHPParser_Parser
	 */
	protected $parser;

	/**
	 * Contains traverser
	 *
	 * @var \PHPParser_NodeTraverser
	 */
	protected $traverser;

	/**
	 * Constructor method for a refactorer
	 */
	function __construct() {
		$this->parser = new \PHPParser_Parser;
		$this->traverser = new \PHPParser_NodeTraverser;
	}

	/**
	 * @param Analysis\AnalyzerInterface $analyzer
	 */
	public function addAnalyzer(\TYPO3\Zubrovka\Scanning\Analysis\AnalyzerInterface $analyzer) {
		$this->traverser->appendVisitor($analyzer);
	}

	protected function parse($filename) {
		return $this->parser->parse(new \PHPParser_Lexer(file_get_contents($filename)));
	}

}