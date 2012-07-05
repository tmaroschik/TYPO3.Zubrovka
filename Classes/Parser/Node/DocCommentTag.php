<?php
namespace TYPO3\Zubrovka\Parser\Node;

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
 * @FLOW3\Scope("prototype")
 */
class DocCommentTag extends \PHPParser_Node_Ignorable {

	/**
	 * @var \PHPParser_Node_Name|string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $options;

	/**
	 * @var \PHPParser_Node_Name|string
	 */
	protected $type;

	/**
	 * @var bool
	 */
	protected $typeAndValueFlipped = FALSE;

	/**
	 * Constructs a doc comment
	 *
	 * @param string $value Value
	 * @param int $line Line
	 */
	public function __construct($name, $options = NULL, $type = NULL, $value = NULL, $typeAndValueFlipped = FALSE) {
		$this->name = ($name instanceof \PHPParser_Node_Name) ? $this->setSelfAsSubNodeParent($name, 'name') : $name;
		$this->options = $options;
		$this->type = ($type instanceof \PHPParser_Node_Name) ? $this->setSelfAsSubNodeParent($type, 'type') : $type;
		$this->typeAndValueFlipped = $typeAndValueFlipped;
		parent::__construct($value, -1);
	}

	/**
	 * @param \PHPParser_Node_Name|string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return \PHPParser_Node_Name|string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param \PHPParser_Node_Name|string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return \PHPParser_Node_Name|string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns a string representation of the tag

	 * @return string String representation
	 */
	public function toString() {
		$result = array();
		if (NULL !== $this->type) {
			$result[] = $this->type;
		}
		if (NULL !== $this->value) {
			$result[] = $this->value;
		}
		if ($this->typeAndValueFlipped) {
			$result = array_reverse($result);
		}
		array_unshift($result, (string) $this->name . $this->options);
		return '@' . implode(' ', $result);
	}

	/**
	 * Returns a string representation of the tag
	 *
	 * @return string String representation
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * @param bool $typeAndValueFlipped
	 */
	public function setTypeAndValueFlipped($typeAndValueFlipped) {
		$this->typeAndValueFlipped = (bool) $typeAndValueFlipped;
	}

	/**
	 * @return bool
	 */
	public function getTypeAndValueFlipped() {
		return $this->typeAndValueFlipped;
	}

}
