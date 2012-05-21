<?php
namespace TYPO3\Zubrovka;

use TYPO3\FLOW3\Package\Package as BasePackage;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Package base class of the TYPO3.Zubrovka package.
 *
 * @FLOW3\Scope("singleton")
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		include_once($this->getResourcesPath() . '/Private/PHP/PHP-Parser/lib/bootstrap.php');
	}

}

?>