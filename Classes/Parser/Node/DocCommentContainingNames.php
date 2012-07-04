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
class DocCommentContainingNames extends \PHPParser_Node_Ignorable_DocComment {

	/**
	 * Parses a line of a doc comment for a tag and its value.
	 * The result is stored in the internal tags array.
	 *
	 * @param string $line A line of a doc comment which starts with an @-sign
	 * @return void
	 */
	protected function parseTag($line) {
		$tagAndValue = array();
		if (preg_match('/@[A-Za-z0-9\\\\]+\\\\([A-Za-z0-9]+)(?:\\((.*)\\))?$/', $line, $tagAndValue) === 0) {
			$tagAndValue = preg_split('/\s/', $line, 2);
		} else {
			array_shift($tagAndValue);
		}
		$tag = strtolower(trim($tagAndValue[0], '@'));
		if (count($tagAndValue) > 1) {
			switch (strtolower($tag)) {
				case 'var':
				case 'param':
				case 'return':
					if (strpos($tagAndValue[1], ' ')) {
						list($name, $description) = explode(' ', $tagAndValue[1], 2);
					} else {
						$name = $tagAndValue[1];
						$description = NULL;
					}
					if (substr($name, 0, 1) == '$') {
						$tempDescription = $description;
						$description = $name;
						$name = $tempDescription;
					}
					if (NULL !== $description) {
						$description = array(new \PHPParser_Node_Ignorable_Comment($description));
					} else {
						$description = array();
					}
					$this->tags[$tag][] = $this->setSelfAsSubNodeParent(new \PHPParser_Node_Name($name, -1, $description), 'tags');
					break;
				default:
					$this->tags[$tag][] = trim($tagAndValue[1], ' "');
					break;
			}
		} else {
			$this->tags[$tag] = array();
		}
	}

	/**
	 * Returns a string representation of the ignorable.
	 *
	 * @param bool $singleLineCommentAllowed
	 * @return string String representation
	 */
	public function toString($singleLineCommentAllowed = FALSE) {
		$docComment = array();
		foreach ($this->tags as $tagName => $tags) {
			if (is_array($tags) && !empty($tags)) {
				foreach ($tags as $tagValue) {
					if ($tagValue instanceof \PHPParser_Node_Name) {
						$tagValue = (string) $tagValue . ' ' . implode(' ', $tagValue->getIgnorables());
					}
					$docComment[] = '@' . $tagName . ' ' . trim($tagValue);
				}
			} elseif (is_array($tags) && empty($tags)) {
				$docComment[] = '@' . $tagName;
			} else {
				if ($tags instanceof \PHPParser_Node_Name) {
					$tags = (string) $tags . ' ' . implode(' ', $tags->getIgnorables());
				}
				$docComment[] = '@' . $tagName . ' ' . trim($tags);
			}
		}
		if (!empty($this->description)) {
			if (!empty($docComment)) {
				array_unshift($docComment, PHP_EOL);
			}
			array_unshift($docComment, $this->description);
		}
		if ($singleLineCommentAllowed && count($docComment) === 1) {
			return '/** ' . $docComment[0] . ' */';
		} else {
			$docComment = preg_replace('/\s+$/', '', $docComment);
			$docComment = preg_replace('/^/', ' * ', $docComment);
			return '/**' . PHP_EOL . implode(PHP_EOL, $docComment) . PHP_EOL . ' */';
		}
	}


	/**
	 * @param \PHPParser_Node_Name $argNew
	 * @param \PHPParser_Node_Name $argOld
	 */
	public function replaceTag(\PHPParser_Node_Name $tagNew, \PHPParser_Node_Name $tagOld) {
		if (NULL !== $this->tags) {
			foreach ($this->tags as $key => $existingTags) {
				if (is_array($existingTags)) {
					foreach ($existingTags as $tagKey => $existingTag) {
						if ($tagOld === $existingTag) {
							$this->tags[$key][$tagKey] = $tagNew;
							$existingTag->setParent();
							$this->setSelfAsSubNodeParent($tagNew, 'tags');
							break;
						}
					}
				}
			}
		}
	}

}
