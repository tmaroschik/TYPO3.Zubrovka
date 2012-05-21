<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nico de Haen <mail@ndh-websolutions.de>
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

require_once dirname(__FILE__) . '/../../PHP-Parser/lib/bootstrap.php';
require_once dirname(__FILE__) . '/../Classes/AutoLoader.php';
Tx_PhpParser_AutoLoader::register();

/**
 * @package
 * @author Nico de Haen
 */
abstract class Tx_PhpParser_Tests_BaseTest extends PHPUnit_Framework_TestCase{

	/**
	 * @var string
	 */
	protected $testDir = '';

	/**
	 * @var Tx_PhpParser_Service_Parser
	 */
	protected $parser;

	/**
	 * @var Tx_PhpParser_Service_Printer
	 */
	protected $printer;

	public function setUp(){
		$this->parser = new Tx_PhpParser_Service_Parser();
		$this->printer = new Tx_PhpParser_Service_Printer();
		//vfsStream::setup('testDir');
		//$this->testDir = vfsStream::url('testDir').'/';

		// just for inspecting the generated files
		$this->testDir = dirname(__FILE__) . '/Fixtures/tmp/';
		if(!is_dir($this->testDir)) {
			mkdir($this->testDir);
		}
	}

	public function tearDown() {
//		$tmpFiles = t3lib_div::getFilesInDir($this->testDir);
//		foreach($tmpFiles as $tmpFile) {
			//unlink($this->testDir . $tmpFile);
//		}
		//rmdir($this->testDir);
	}

	protected function parseFile($fileName) {
		$classFilePath = dirname(__FILE__) . '/Fixtures/' . $fileName;
		$classFileObject = $this->parser->parseFile($classFilePath);
		return $classFileObject;
	}

	protected function compareClasses($classFileObject, $classFilePath) {
		$originalFile = file_get_contents($classFileObject->getFilePathAndName());
		$newFile = file_get_contents($classFilePath);
		$this->assertEquals($originalFile, $newFile);
		return;
//		$this->assertTrue(file_exists($classFilePath), $classFilePath . 'not exists');
//		$classObject = $classFileObject->getFirstClass();
//		$this->assertTrue($classObject instanceof Tx_PhpParser_Domain_Model_Class);
//		$className = $classObject->getName();
//		if(!class_exists($className)) {
//			require_once($classFilePath);
//		}
//		$this->assertTrue(class_exists($className), 'Class "' . $className . '" does not exist! Tried ' . $classFilePath);
//		$reflectedClass = new ReflectionClass($className);
//		$this->assertEquals(count($reflectedClass->getMethods()), count($classObject->getMethods()), 'Method count does not match');
//		$this->assertEquals(count($reflectedClass->getProperties()), count($classObject->getProperties()));
//		$this->assertEquals(count($reflectedClass->getConstants()), count($classObject->getConstants()));
//		if(strlen($classObject->getNamespaceName()) > 0 ) {
//			$this->assertEquals( $reflectedClass->getNamespaceName(), $classObject->getNamespaceName());
//		}
//		return $reflectedClass;
	}
}
