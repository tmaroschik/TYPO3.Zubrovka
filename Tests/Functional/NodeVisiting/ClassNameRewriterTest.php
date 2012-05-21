<?php
namespace TYPO3\Zubrovka\Tests\Functional\NodeVisiting;

/*                                                                        *
 * This script belongs to the FLOW3 package "Zubrovka".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Test suite for Class Name Rewriter
 *
 */
class ClassNameRewriterTest extends \TYPO3\FLOW3\Tests\FunctionalTestCase {

	/**
	 * @var \PHPParser_Parser
	 */
	protected $parser;

	/**
	 * @var \PHPParser_PrettyPrinter_TYPO3CGL
	 */
	protected $prettyPrinter;

	/**
	 * Sets up test requirements depending on the enabled tests.
	 * @return void
	 */
	public function setUp() {
		$this->parser = new \PHPParser_Parser;
		$this->prettyPrinter = new \PHPParser_PrettyPrinter_TYPO3CGL;
		parent::setUp();
	}

	/**
	 * @test
	 */
	public function renameClassAndTypeHint() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Tx_PhpParser_Test_ClassMethodWithManyParameter', 'ClassMethodWithManyParameter', $operationQueue));
		$stmts = $this->getParsedSource('ClassMethodWithManyParameter');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameClassAndTypeHint'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespace() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamepaceTest', 'Test\Test2\SimpleNamepaceTest', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespace');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameSimpleNamespace'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedClassName() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamepaceTest', 'Test\Model\RenamedSimpleNamespacedClass', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespace');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedClassName'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedExtendedClassName() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamespaceExtendedClass', 'Test\Model\RenamedSimpleNamespaceExtendedClass', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespaceExtendedClass');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedExtendedClassName'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedClassAndNamespace() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamepaceTest', 'Test\Test2\RenameSimpleNamespacedClassAndNamespace', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespace');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedClassAndNamespace'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function extendSimpleNamespace() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamepaceTest', 'Test\Test2\Model\SimpleNamepaceTest', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespace');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('ExtendSimpleNamespace'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function reduceSimpleNamespace() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\SimpleNamepaceTest', 'Test\SimpleNamepaceTest', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespace');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('ReduceSimpleNamespace'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameFirstNamespaceInMultipleNamespaces() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model\MultipleNamespaces', 'Test\ChangedNamespace\MultipleNamespaces', $operationQueue));
		$stmts = $this->getParsedSource('MultipleNamespaces');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameFirstNamespaceInMultipleNamespaces'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameSecondNamespaceInMultipleNamespaces() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('Test\Model2\MultipleNamespaces', 'Test\ChangedNamespace\MultipleNamespaces', $operationQueue));
		$stmts = $this->getParsedSource('MultipleNamespaces');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameSecondNamespaceInMultipleNamespaces'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameImportedClassName() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('\Foo\Bar\Buh', '\Foo\Bar\FOOO', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespaceWithUseStatement');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameImportedClassName'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function renameImportedNamespaceAndClassName() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('\Foo\Bar\Buh', '\Foo\Boo\FOOO', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespaceWithUseStatement');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		$this->assertEquals($this->getTarget('RenameImportedNamespaceAndClassName'), $this->getNewCode($stmts));
	}

	/**
	 * @test
	 */
	public function extendImportedNamespace() {
		$operationQueue = new \TYPO3\Zubrovka\Refactoring\OperationQueue();
		$traverser = new \PHPParser_NodeTraverser;
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NameResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\NamespaceResolver);
		$traverser->addVisitor(new \TYPO3\Zubrovka\NodeVisiting\ClassNameRewriter('\Foo\Bar\Buh', '\Foo\Bar\Gah\Buh', $operationQueue));
		$stmts = $this->getParsedSource('SimpleNamespaceWithUseStatement');
		$stmts = $traverser->traverse($stmts);
		$operationQueue->run($stmts);
		var_dump($this->getNewCode($stmts));die();
		$this->assertEquals($this->getTarget('RenameImportedNamespaceAndClassName'), $this->getNewCode($stmts));
	}

	/**
	 * @param string $name
	 * @return array statements
	 */
	protected function getParsedSource($name) {
		return $this->parser->parse(new \PHPParser_Lexer(file_get_contents(__DIR__ . '/Fixtures/Sources/' . $name . '.txt')));
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function getTarget($name) {
		return file_get_contents(__DIR__ . '/Fixtures/Targets/' . $name . '.txt');
	}

	/**
	 * @param array $stmts
	 * @return string
	 */
	protected function getNewCode(array $stmts) {
		return '<?php' . PHP_EOL . $this->prettyPrinter->prettyPrint($stmts);
	}

}
?>