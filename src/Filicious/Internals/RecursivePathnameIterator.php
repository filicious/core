<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Internals;

use Filicious\File;

/**
 * Filesystem iterator
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class RecursivePathnameIterator extends PathnameIterator
	implements \RecursiveIterator
{

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Returns if an iterator can be created for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.haschildren.php
	 * @return bool true if the current entry can be iterated over, otherwise returns false.
	 */
	public function hasChildren()
	{
		return $this->valid() &&
		$this->current()->isDirectory() &&
		$this->applyGlobSearchPattern();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Returns an iterator for the current entry.
	 *
	 * @link http://php.net/manual/en/recursiveiterator.getchildren.php
	 * @return \RecursiveIterator An iterator for the current entry.
	 */
	public function getChildren()
	{
		$iterator = new RecursivePathnameIterator(
			$this->currentFile()->internalPathname(),
			$this->filters
		);
		$iterator->prepareFilters($this);
		return $iterator;
	}

	public function shouldIncludeDirectories() {
		if ($this->bitmask === null) {
			$this->prepareFilters();
		}
		return File::LIST_DIRECTORIES === ($this->bitmask & File::LIST_DIRECTORIES);
	}
}
