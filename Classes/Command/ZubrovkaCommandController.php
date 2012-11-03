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
use TYPO3\Zubrovka\Refactoring\Mission\RenameClassNameMission;

/**
 * Command controller for managing caches
 *
 * NOTE: This command controller will run in compile time (as defined in the package bootstrap)
 *
 * @FLOW3\Scope("singleton")
 */
class ZubrovkaCommandController extends \TYPO3\FLOW3\Cli\CommandController {

	/**
	 * Contains configurationManager
	 *
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @FLOW3\Inject
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\FLOW3\Configuration\Source\YamlSource
	 * @FLOW3\Inject
	 */
	protected $yamlSource;

	/**
	 * @return void
	 */
	public function testCommand() {
		$directoryScanner = new \TYPO3\Zubrovka\Scanning\DirectoryScanner();
		$directoryScanner->addAnalyzer(new \TYPO3\Zubrovka\Scanning\Analysis\ClassNameAnalyzer());
		$results = $directoryScanner->scan(array('/Users/tmaroschik/Sites/Core/typo3', '/Users/tmaroschik/Sites/Core/t3lib'));
		foreach ($results as $filename => $result) {
			foreach ($result['TYPO3\Zubrovka\Scanning\Analysis\ClassNameAnalyzer'] as $className) {
				echo $filename . ';' . $className;
			}
		}
		$this->sendAndExit(0);
	}

	/**
	 * Refactor a directory containing PHP files
	 *
	 * The directory refactorer will traverse the given directory recursively.
	 * A child process is spawned for every filename to speed up the refactoring.
	 *
	 * See <b>./flow3 help zubrovka:refactor</b> for a configuration example
	 *
	 * @param string $directory Define the path to a refactorable directory containing files with .php and .inc extensions
	 * @param string $configuration Define the path to a refactorer configuration yaml file
	 * @param int $maxProcesses Limit the count of refactorer child processes running in parallel
	 *
	 * @see zubrovka.zubrovka:refactor
	 */
	public function refactorDirectoryCommand($directory, $configuration = NULL, $maxProcesses = 2) {
		/** @var $iterator \RecursiveDirectoryIterator */
		$runningProcesses = array();
		$results = array();
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
		$filenames = array();
		while ($iterator->valid()) {
			if (!$iterator->isDot()) {
				$filename = $iterator->key();
				if ('php' == strtolower(substr($filename, -3)) || 'inc' == strtolower(substr($filename, -3))) {
					$filenames[] = $filename;
				}
			}
			$iterator->next();
		}
		foreach ($filenames as $filename) {
			list($outputfile, $pidfile) = $this->executeBackgroundCommand('zubrovka:refactor', array('filename' => $filename, 'configuration' => $configuration));
			$pid = (int)file_get_contents($pidfile);
			$runningProcesses[$pid] = array(
				'filename' => $filename,
				'outputfile' => $outputfile,
				'pidfile' => $pidfile
			);
			while (count($runningProcesses) >= $maxProcesses) {
				usleep(100000);
				foreach ($runningProcesses as $pid => $data) {
					if (!$this->backgroundCommandIsRunning($pid)) {
						$results[$data['filename']] = file_get_contents($data['outputfile']);
						unset($runningProcesses[$pid]);
						unlink($data['outputfile']);
						unlink($data['pidfile']);
						var_dump(array($data['filename'] => $results[$data['filename']]));
					}
				}
			}
		}
	}

	/**
	 * Refactor a PHP file
	 *
	 * The refactor command will traverse the parsed PHP file and change the
	 * AST according to the configured missions.
	 *
	 * A configuration file follows the following schema:
	 *
	 * {FullyQualifiedMissionClassName}:
	 * ..{defaultConstructorArgumentName}: {defaultConstructorArgumentValue}
	 * ..-
	 * ....# The first mission
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 * ..-
	 * ....# A second mission
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 * {AnotherFullyQualifiedMissionClassName}:
	 * ..-
	 * ....# The third mission
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 * ....{constructorArgumentName}: {constructorArgumentValue}
	 *
	 * [Replace dots with spaces]
	 *
	 * @param string $filename Define the path to a refactorable php file
	 * @param string $configuration Define the path to a refactorer configuration yaml file
	 * @return void
	 */
	public function refactorCommand($filename, $configuration = NULL) {
		$codeRefactorer = new \TYPO3\Zubrovka\Refactoring\CodeRefactorer();
		$this->appendMissionsFromConfigurationFile($codeRefactorer, $configuration);
		try {
			$codeRefactorer->load(file_get_contents($filename));
			$codeRefactorer->refactor();
			file_put_contents($filename, $codeRefactorer->save());
		} catch (\Exception $e) {
			var_dump(array('ERROR:' . $filename => $e));
		}
		$this->sendAndExit(0);
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\CodeRefactorer $codeRefactorer
	 * @param string $configurationFilename
	 */
	protected function appendMissionsFromConfigurationFile(\TYPO3\Zubrovka\Refactoring\CodeRefactorer $codeRefactorer, $configurationFilename) {
		if (NULL !== $configurationFilename) {
			if (substr($configurationFilename, -5) == '.yaml') {
				$configurationFilename = substr($configurationFilename, 0, -5);
			}
			$configuration = $this->yamlSource->load($configurationFilename, '.');
			foreach ($configuration as $missionClassName => &$definitions) {
				$constructorParameters = $this->reflectionService->getMethodParameters($missionClassName, '__construct');
				$definedDefaultArguments = array();
				foreach ($definitions as $parameterName => $parameterValue) {
					if (is_numeric($parameterName)) {
						continue;
					}
					if (is_scalar($parameterValue)) {
						$definedDefaultArguments[$parameterName] = $parameterValue;
						unset($definitions[$parameterName]);
					}
				}
				foreach ($definitions as $definition) {
					$constructorArguments = array();
					foreach ($constructorParameters as $constructorParameterName => $constructorParameterMeta) {
						if (isset($definition[$constructorParameterName])) {
							$constructorArguments[] = $definition[$constructorParameterName];
						} elseif (isset($definedDefaultArguments[$constructorParameterName])) {
							$constructorArguments[] = $definedDefaultArguments[$constructorParameterName];
						} else {
							$constructorArguments[] = $constructorParameterMeta['defaultValue'];
						}
					}
					$mission = $this->instantiateClass($missionClassName, $constructorArguments);
					if (NULL !== $mission) {
						$codeRefactorer->appendMission($mission);
					}
				}
			}
		}
	}

	/**
	 * @param int $pid
	 * @return bool
	 */
	protected function backgroundCommandIsRunning($pid) {
		try {
			$result = shell_exec(sprintf("ps %d", $pid));
			if (count(preg_split("/\n/", $result)) > 2) {
				return TRUE;
			}
		}
		catch (\Exception $e) {
		}

		return FALSE;
	}

	/**
	 * Executes the given command as a sub-request to the FLOW3 CLI system.
	 *
	 * @param string $commandIdentifier E.g. typo3.flow3:cache:flush
	 * @return array
	 */
	protected function executeBackgroundCommand($commandIdentifier, array $arguments = array()) {
		$settings = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.FLOW3');
		$phpBinaryPathAndFilename = escapeshellcmd(\TYPO3\FLOW3\Utility\Files::getUnixStylePath($settings['core']['phpBinaryPathAndFilename']));
		if (DIRECTORY_SEPARATOR === '/') {
			$command = 'XDEBUG_CONFIG="idekey=FLOW3_SUBREQUEST" FLOW3_ROOTPATH=' . escapeshellarg(FLOW3_PATH_ROOT) . ' ' . 'FLOW3_CONTEXT=' . escapeshellarg($settings['core']['context']) . ' "';
		} else {
			$command = 'SET FLOW3_ROOTPATH=' . escapeshellarg(FLOW3_PATH_ROOT) . '&' . 'SET FLOW3_CONTEXT=' . escapeshellarg($settings['core']['context']) . '&"';
		}
		$command .= $phpBinaryPathAndFilename . '" -c ' . escapeshellarg(php_ini_loaded_file()) . ' ' . escapeshellarg(FLOW3_PATH_FLOW3 . 'Scripts/flow3.php') . ' ' . escapeshellarg($commandIdentifier);
		if (!empty($arguments)) {
			foreach ($arguments as $argumentName => $argumentValue) {
				$command .= ' -' . $argumentName . ' "' . $argumentValue . '"';
			}
		}
		$outputfile = tempnam(sys_get_temp_dir(), 'BackgroundCommandOutput');
		$pidfile = tempnam(sys_get_temp_dir(), 'BackgroundCommandPid');
		exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $command, $outputfile, $pidfile));
		return array($outputfile, $pidfile);
	}

	/**
	 * @param string $className
	 * @param array $arguments
	 * @return object
	 */
	protected function instantiateClass($className, array $arguments = array()) {
		switch (count($arguments)) {
			case 0:
				$object = new $className();
				break;
			case 1:
				$object = new $className($arguments[0]);
				break;
			case 2:
				$object = new $className($arguments[0], $arguments[1]);
				break;
			case 3:
				$object = new $className($arguments[0], $arguments[1], $arguments[2]);
				break;
			case 4:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
				break;
			case 5:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
				break;
			case 6:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
				break;
			default:
				$class = new \ReflectionClass($className);
				$object = $class->newInstanceArgs($arguments);
		}
		return $object;
	}

}
