<?php
namespace TYPO3\Zubrovka\Command;

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
use TYPO3\FLOW3\Cli\Response;
use TYPO3\FLOW3\Utility\Files;

/**
 * Command controller for managing caches
 *
 * NOTE: This command controller will run in compile time (as defined in the package bootstrap)
 *
 * @FLOW3\Scope("singleton")
 */
// TEST
class TestCommandController /* FOOO */ extends \TYPO3\FLOW3\Cli\CommandController {

	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 */
	protected $packageManager; // TEST

	/**
	 * @param \TYPO3\FLOW3\Package\PackageManagerInterface
	 */
	public function injectPackageManager(\TYPO3\FLOW3\Package\PackageManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 * @return void
	 */
	public function testCommand($force = FALSE) {
		/** @var $package \TYPO3\FLOW3\Package\PackageInterface */
		$package = $this->packageManager->getPackage('TYPO3.Zubrovka');;
		$parser        = new \PHPParser_Parser;
		$traverser     = new \PHPParser_NodeTraverser;
		$nodeDumper    = new \PHPParser_NodeDumper;
		$prettyPrinter = new \PHPParser_PrettyPrinter_TYPO3CGL;
		// we will need resolved names
		$traverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver);
		// our own node visitor
//		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceConverter);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('TYPO3\FLOW3\Aop\AspectContainer', 'TYPO3\FLOW3\CoolAop\AspectContainer'));
		try {
			// parse
//			$stmts = $parser->parse(new \PHPParser_Lexer(file_get_contents($classesPath .'Command/TestCommandController.php')));
			$stmts = $parser->parse(new \PHPParser_Lexer(file_get_contents($package->getPackagePath() . 'Tests/Fixtures/Namespaces/SimpleNamespace.php')));
			// Traverse
			$stmts = $traverser->traverse($stmts);
			// Dump Nodes
//			$this->outputLine($nodeDumper->dump($stmts));
			// pretty print
			$this->outputLine('<?php' . PHP_EOL . $prettyPrinter->prettyPrint($stmts));
		}
		catch (\PHPParser_Error $e) {
			echo 'Parse Error: ', $e->getMessage();
		}
		$this->sendAndExit(0);
	}

}

class testfoo {

}