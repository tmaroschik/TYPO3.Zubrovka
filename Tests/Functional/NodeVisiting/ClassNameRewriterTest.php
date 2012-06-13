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

use \TYPO3\Zubrovka\Refactoring;

/**
 * Test suite for Class Name Rewriter
 *
 */
class ClassNameRewriterTest extends \TYPO3\FLOW3\Tests\FunctionalTestCase {

	/**
	 * @test
	 */
	public function renameClassAndTypeHint() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_ClassMethodWithManyParameter', 'ClassMethodWithManyParameter'));
		$codeRefactorer->load($this->getSource('ClassMethodWithManyParameter'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameClassAndTypeHint'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\Test2\SimpleNamepaceTest'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSimpleNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedClassName() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\Model\RenamedSimpleNamespacedClass'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedClassName'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedExtendedClassName() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamespaceExtendedClass', 'Test\Model\RenamedSimpleNamespaceExtendedClass'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceExtendedClass'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedExtendedClassName'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameSimpleNamespacedClassAndNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\Test2\RenameSimpleNamespacedClassAndNamespace'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSimpleNamespacedClassAndNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function extendSimpleNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\Test2\Model\SimpleNamepaceTest'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('ExtendSimpleNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function reduceSimpleNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\SimpleNamepaceTest'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('ReduceSimpleNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameFirstNamespaceInMultipleNamespaces() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\MultipleNamespaces', 'Test\ChangedNamespace\MultipleNamespaces'));
		$codeRefactorer->load($this->getSource('MultipleNamespaces'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameFirstNamespaceInMultipleNamespaces'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameSecondNamespaceInMultipleNamespaces() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model2\MultipleNamespaces', 'Test\ChangedNamespace\MultipleNamespaces'));
		$codeRefactorer->load($this->getSource('MultipleNamespaces'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSecondNamespaceInMultipleNamespaces'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameImportedClassName() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('\Foo\Bar\Buh', '\Foo\Bar\FOOO'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceWithUseStatement'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameImportedClassName'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function renameImportedNamespaceAndClassName() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('\Foo\Bar\Buh', '\Foo\Boo\FOOO'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceWithUseStatement'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameImportedNamespaceAndClassName'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function extendImportedNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('\Foo\Bar\Buh', '\Foo\Bar\Gah\Buh'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceWithUseStatement'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('ExtendImportedNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function reduceImportedNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('\Foo\Bar\Buh', '\Foo\Buh'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceWithUseStatement'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('ReduceImportedNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function extendNamespacedExtendedClassName() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Test\Model\SimpleNamepaceTest', 'Test\Model\Model2\SimpleNamepaceTest'));
		$codeRefactorer->load($this->getSource('SimpleNamespaceExtendedClass'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('ExtendNamespacedExtendedClassName'), $codeRefactorer->save());
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function getSource($name) {
		return file_get_contents(__DIR__ . '/Fixtures/Sources/' . $name . '.txt');
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function getTarget($name) {
		return file_get_contents(__DIR__ . '/Fixtures/Targets/' . $name . '.txt');
	}

}
?>