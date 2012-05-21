<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nico de Haen
 *  All rights reserved
 *
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(dirname(__FILE__) . '/../BaseTest.php');

class Tx_PhpParser_Tests_Unit_ParserTest extends Tx_PhpParser_Tests_BaseTest {

	/**
	 * @var Tx_PhpParser_Service_Parser
	 */
	protected $parser;

	/**
	 * @test
	 */
	function parseSimpleProperty() {
		$classFileObject = $this->parseFile('SimpleProperty.php');
		$this->assertEquals(count($classFileObject->getClasses()),1);
		$classObject = $classFileObject->getFirstClass();
		$this->assertEquals(count($classObject->getMethods()), 0);
		$this->assertEquals(count($classObject->getProperties()), 1);
		$this->assertEquals($classObject->getProperty('property')->getValue(),'foo');
		$this->assertEquals($classObject->getProperty('property')->getModifierNames(), array('protected'));
		$this->assertTrue($classObject->isTaggedWith('author'));
		$this->assertTrue($classObject->getProperty('property')->isTaggedWith('var'));

	}

	/**
	 * @test
	 */
	function parseSimplePropertyWithGetterAndSetter() {
		$this->parser->setTraverser(new Tx_PhpParser_Parser_Traverser);
		$classFileObject = $this->parseFile('SimplePropertyWithGetterAndSetter.php');
		$this->assertEquals(count($classFileObject->getFirstClass()->getMethods()), 2);
		$this->assertEquals(count($classFileObject->getFirstClass()->getProperties()), 1);
		$this->assertEquals($classFileObject->getFirstClass()->getProperty('property')->getValue(),'foo');
		$this->assertEquals($classFileObject->getFirstClass()->getProperty('property')->getModifierNames(), array('protected'));
	}

	/**
	 * @test
	 */
	function parseArrayProperty() {
		$this->parser->setTraverser(new Tx_PhpParser_Parser_Traverser);
		$classFileObject = $this->parseFile('ClassWithArrayProperty.php');
		$this->assertEquals(count($classFileObject->getFirstClass()->getProperties()), 1);
		$this->assertNotEquals($classFileObject->getFirstClass()->getProperty('property')->getValue(),array('a' => 'b','5' => 1223,'foo' => array('foo' => 'bar'),array(1,4,3)));
		$this->assertEquals($classFileObject->getFirstClass()->getProperty('property')->getValue(),array('a' => 'b','5' => 1223,'foo' => array('foo' => 'bar'),array(1,2,3)));
		$this->assertEquals($classFileObject->getFirstClass()->getProperty('property')->getModifierNames(), array('protected'));
	}

	/**
	 * @test
	 */
	function parseSimpleNonBracedNamespace() {
		$classFileObject = $this->parseFile('Namespaces/SimpleNamespace.php');
		$this->assertEquals('Test\\Model',$classFileObject->getFirstClass()->getNamespaceName());
	}

	/**
	 * @test
	 */
	function parseClassMethodWithManyParameter() {
		$classFileObject = $this->parseFile('ClassMethodWithManyParameter.php');
		$parameters = $classFileObject->getFirstClass()->getMethod('testMethod')->getParameters();
		$this->assertEquals( 6, count($parameters));
		$this->assertEquals($parameters[3]->getName(), 'booleanParam');
		$this->assertEquals($parameters[3]->getVarType(), 'boolean');
		$this->assertEquals($parameters[5]->getTypeHint(), 'Tx_PhpParser_Parser_Utility_NodeConverter');
	}

	/**
	 * @test
	 */
	function parseClassWithVariousModifiers() {
		$classFileObject = $this->parseFile('ClassWithVariousModifiers.php');
		$classObject = $classFileObject->getFirstClass();
		$this->assertTrue($classObject->isAbstract());

		$this->assertTrue($classObject->getProperty('publicProperty')->isPublic());
		$this->assertTrue($classObject->getProperty('protectedProperty')->isProtected());
		$this->assertTrue($classObject->getProperty('privateProperty')->isPrivate());
		$this->assertFalse($classObject->getProperty('publicProperty')->isProtected());
		$this->assertFalse($classObject->getProperty('privateProperty')->isPublic());
		$this->assertTrue($classObject->getMethod('abstractMethod')->isAbstract());
		$this->assertTrue($classObject->getMethod('staticFinalFunction')->isStatic());
		$this->assertTrue($classObject->getMethod('staticFinalFunction')->isFinal());
	}

	/**
	 * @test
	 */
	function parserFindsFunction() {
		$fileObject = $this->parseFile('FunctionsWithoutClasses.php');
		$functions = $fileObject->getFunctions();
		$this->assertEquals(count($functions),2);
		$this->assertTrue(isset($functions['simpleFunction']));
		$this->assertEquals(count($fileObject->getFunction('functionWithParameter')->getParameters()),2);
		$this->assertEquals($fileObject->getFunction('functionWithParameter')->getParameterByPosition(1)->getName(),'bar');
	}
}

?>
