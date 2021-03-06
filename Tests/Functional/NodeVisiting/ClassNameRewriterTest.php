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
	public function renameSimpleInterface() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Tests_Interface', 'ARenamedInterface'));
		$codeRefactorer->load($this->getSource('InterfaceWithAMethod'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RenameSimpleInterface'), $codeRefactorer->save());
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
	 * @test
	 */
	public function introduceNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_ClassMethodWithManyParameter', '\Tx\PhpParser\Test\ClassMethodWithManyParameter'));
		$codeRefactorer->load($this->getSource('ClassMethodWithManyParameter'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function introduceNamespaceToMultipleClasses() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_MultipleClasses', '\Tx\PhpParser\Test\MultipleClasses'));
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_MultipleClasses2', '\Tx\PhpParser\Test\MultipleClasses2'));
		$codeRefactorer->load($this->getSource('MultipleClasses'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceNamespaceToMultipleClasses'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function introduceMultipleNamespacesToMultipleClasses() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_MultipleClasses', '\Tx\PhpParser\Test\MultipleClasses'));
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_MultipleClasses2', '\Tx\PhpParser\Test2\MultipleClasses2'));
		$codeRefactorer->load($this->getSource('MultipleClasses'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceMultipleNamespacesToMultipleClasses'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function introduceNamespaceToOneOfMultipleClasses() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_MultipleClasses2', '\Tx\PhpParser\Test\SecondClass'));
		$codeRefactorer->load($this->getSource('MultipleClasses'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceNamespaceToOneOfMultipleClasses'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function introduceNamespaceAndReplaceRelativeNames() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_ClassMethodWithManyParameterAndOtherClassUsage', '\Tx\PhpParser\Test\ClassMethodWithManyParameterAndOtherClassUsage'));
		$codeRefactorer->load($this->getSource('ClassMethodWithManyParameterAndOtherClassUsage'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceNamespaceAndReplaceRelativeNames'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function introduceNamespaceAndChangeNames() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_ClassMethodWithManyParameterAndOtherClassUsage', '\Tx\PhpParser\Test\ClassMethodWithManyParameterAndOtherClassUsage'));
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_ClassMethodWithManyParameter', '\Tx\PhpParser\Test2\ClassMethodWithManyParameter'));
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('Tx_PhpParser_Test_StaticClassCall', '\Tx\PhpParser\Static\ClassCall'));
		$codeRefactorer->load($this->getSource('ClassMethodWithManyParameterAndStaticClassUsage'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('IntroduceNamespaceAndChangeNames'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function removeSimpleNamespace() {
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('\Test\Model\SimpleNamepaceTest', 'Test_Model_SimpleNamespaceTest'));
		$codeRefactorer->load($this->getSource('SimpleNamespace'));
		$codeRefactorer->refactor();
		$this->assertEquals($this->getTarget('RemoveSimpleNamespace'), $codeRefactorer->save());
	}

	/**
	 * @test
	 */
	public function removeNs() {
		$this->markTestSkipped('Under construction.');
		$codeRefactorer = new Refactoring\CodeRefactorer();
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('t3lib_pageSelect', '\TYPO3\Library\PageSelect'));
		$codeRefactorer->appendMission(new Refactoring\Mission\RenameClassNameMission('t3lib_div', '\TYPO3\Library\Utility'));
		$person = file_get_contents('/Users/tmaroschik/Sites/typo3_src-4.7.1/t3lib/class.t3lib_page.php');
		$codeRefactorer->load($person);
		$codeRefactorer->refactor();
		echo $codeRefactorer->save();
		$this->assertEquals($person, $codeRefactorer->save());
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