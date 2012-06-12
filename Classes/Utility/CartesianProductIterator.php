<?php
namespace TYPO3\Zubrovka\Utility;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 */
class CartesianProductIterator implements \Iterator {

	protected $data = null;
	protected $limit = null;
	protected $current = null;

	/** Thanks to http://stackoverflow.com/questions/2246493/concatenate-values-of-n-arrays-in-php */
	public function __construct(array $params) {
		// add parameter arrays in reverse order so we can use foreach() in current()
		// could use array_reverse(), but you might want to check is_array() for each element.
		$this->data = array();
		foreach ($params as $p) {
			// <-- add: test is_array() for each $p  -->
			array_unshift($this->data, array_values($p));
		}
		$this->current = 0;
		// there are |arr1|*|arr2|...*|arrN| elements in the result set
		$this->limit = array_product(array_map('count', $params));
	}

	public function current() {
		/* this works like a baseX->baseY converter (e.g. dechex() )
			   the only difference is that each "position" has its own number of elements/"digits"
			*/
		// <-- add: test this->valid() -->
		$rv  = array();
		$key = $this->current;
		foreach ($this->data as $e) {
			array_unshift($rv, $e[$key % count($e)]);
			$key = (int)($key / count($e));
		}
		return $rv;
	}

	public function key() {
		return $this->current;
	}

	public function next() {
		++$this->current;
	}

	public function rewind() {
		$this->current = 0;
	}

	public function valid() {
		return $this->current < $this->limit;
	}

}
