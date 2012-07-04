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
class CodeRefactorer extends AbstractRefactorer {

	/**
	 * Contains prettyPrinter
	 *
	 * @var \PHPParser_PrettyPrinter_TYPO3CGL
	 */
	protected $prettyPrinter;

	/**
	 * Constructor method for a refactorer
	 */
	function __construct() {
		$this->prettyPrinter = new \PHPParser_PrettyPrinter_TYPO3CGL;
		parent::__construct();
	}

	/**
	 * @param string $code
	 */
	public function load($code) {
		$this->stmts = $this->parser->parse(new \PHPParser_Lexer($code));
	}

	/**
	 * @return string
	 */
	public function save() {
		return '<?php' . PHP_EOL . $this->prettyPrinter->prettyPrint($this->stmts);
	}

	/**
	 *
	 */
	public function refactor() {
		$objectives = $this->analyze();
		$transaction = $this->transactionBuilder->build($objectives);
		$transaction->addOptimizer(new \TYPO3\Zubrovka\Refactoring\TransactionOptimizer\NamespaceImportOptimizer);
		$this->stmts = $transaction->commit($this->stmts);
	}

}