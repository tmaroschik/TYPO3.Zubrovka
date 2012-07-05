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
class DocCommentContainingTags extends \PHPParser_Node_Ignorable_DocComment {

	/**
	 * These tagnames will no be converted to name nodes
	 *
	 * @var array
	 */
	protected $ignoredNames = array(
		'access' => FALSE,
		'author' => FALSE,
		'copyright' => FALSE,
		'deprecated' => FALSE,
		'example' => FALSE,
		'ignore' => FALSE,
		'internal' => FALSE,
		'link' => FALSE,
		'see' => FALSE,
		'since' => FALSE,
		'tutorial' => FALSE,
		'version' => FALSE,
		'package' => FALSE,
		'subpackage' => FALSE,
		'name' => FALSE,
		'global' => FALSE,
		'param' => TRUE,
		'return' => FALSE,
		'staticvar' => TRUE,
		'category' => FALSE,
		'staticVar' => TRUE,
		'static' => FALSE,
		'var' => TRUE,
		'throws' => TRUE,
		'inheritdoc' => FALSE,
		'inheritDoc' => FALSE,
		'license' => FALSE,
		'todo' => FALSE,
		'deprec' => FALSE,
		'property' => TRUE,
		'method' => FALSE,
		'abstract' => FALSE,
		'exception' => FALSE,
		'magic' => FALSE,
		'api' => FALSE,
		'final' => FALSE,
		'filesource' => FALSE,
		'throw' => TRUE,
		'uses' => TRUE,
		'usedby' => TRUE,
		'private' => FALSE,
		'Annotation' => FALSE,
		'override' => FALSE,
		'codeCoverageIgnore' => FALSE,
		'codeCoverageIgnoreStart' => FALSE,
		'codeCoverageIgnoreEnd' => FALSE,
		'Required' => FALSE,
		'Attribute' => FALSE,
		'Attributes' => FALSE,
		'Target' => FALSE,
		'SuppressWarnings' => FALSE,
	);

	/**
	 * @var null|\PHPParser_Node_Stmt_Namespace Current namespace
	 */
	protected $namespace;

	/**
	 * @var array Currently defined namespace and class aliases
	 */
	protected $aliases;

	/**
	 * Constructs a doc comment
	 *
	 * @param string $value Value
	 * @param int $line Line
	 */
	public function __construct($value, $line = -1, \PHPParser_Node_Stmt_Namespace $namespace = NULL, $aliases = array()) {
		$this->namespace = $namespace;
		$this->aliases = $aliases;
		parent::__construct($value, $line);
	}

	/**
	 * Parses a line of a doc comment for a tag and its value.
	 * The result is stored in the internal tags array.
	 *
	 * @param string $line A line of a doc comment which starts with an @-sign
	 * @return void
	 */
	protected function parseTag($line) {
		$tagAndValue = array();
		if (preg_match('/@([A-Za-z0-9\\\-]+)(\(.*\))? ?(.*)/', $line, $tagAndValue) === 0) {
			$tagAndValue = preg_split('/\s/', $line, 2);
		} else {
			array_shift($tagAndValue);
		}
		list($tag, $options, $value) = $tagAndValue;
		$tag = trim($tag, '@');
		$type = NULL;
		$typeAndValueFlipped = FALSE;
		if ($this->valueShouldBeParsed($tag)) {
			if (FALSE !== strpos($value, ' ')) {
				list($type, $value) = explode(' ', $value, 2);
			} else {
				$type = $value;
				$value = NULL;
			}
			if (substr($type, 0, 1) == '$') {
				list ($value, $type) = array($type, $value);
				$typeAndValueFlipped = TRUE;
			}
		}
		$tagName = $this->resolveTagName($tag);
		$typeName = $this->resolveTypeName($type);
		$this->tags[] = $this->setSelfAsSubNodeParent(new DocCommentTag($tagName, $options, $typeName, $value, $typeAndValueFlipped), 'tags');
	}

	protected function valueShouldBeParsed($tag) {
		return !isset($this->ignoredNames[$tag]) || (isset($this->ignoredNames[$tag]) && $this->ignoredNames[$tag] == TRUE);
	}

	protected function resolveTagName($tag) {
		if (isset($this->ignoredNames[$tag])) {
			return $tag;
		}
		return $this->resolveName($tag);
	}

	protected function resolveTypeName($type) {
		return $this->resolveName($type);
	}

	protected function resolveName($name) {
		if (empty($name)) {
			return NULL;
		}
		$alias = (FALSE === $pos = strpos($name, '\\'))? $name : substr($name, 0, $pos);
		if ('\\' == substr($name, 0, 1)) {
			return new \PHPParser_Node_Name_FullyQualified($name);
		} elseif (isset($this->aliases[$alias])) {
			return new \PHPParser_Node_Name($name);
		} elseif (!\TYPO3\Zubrovka\Utility\TypeHandling::isBuiltin($name)) {
			return new \PHPParser_Node_Name($name);
		}
		return $name;
	}

	/**
	 * Returns a string representation of the ignorable.
	 *
	 * @param bool $singleLineCommentAllowed
	 * @return string String representation
	 */
	public function toString($singleLineCommentAllowed = FALSE) {
		$docComment = array();
		foreach ($this->tags as $tag) {
			$docComment[] = (string) $tag;
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
			foreach ($this->tags as $key => $existingTag) {
				if ($tagOld === $existingTag) {
					$this->tags[$key] = $tagNew;
					$existingTag->setParent();
					$this->setSelfAsSubNodeParent($tagNew, 'tags');
					break;
				}
			}
		}
	}

}
