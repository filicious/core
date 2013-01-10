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

use Filicious\Filesystem;


/**
 * A mount aggregator can mount adapters to various paths.
 * Multiple adapters can be mounted to the same path, but only the last mounted
 * adapter can be seen.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractAdapter
	implements Adapter
{
	
	protected $fs;
	
	protected $root;
	
	protected $parent;
	
	protected function __construct(Filesystem $fs, Adapter $root, Adapter $parent) {
		$this->fs = $fs;
		$this->root = $root;
		$this->parent = $parent;
	}
	
	public function getFilesystem() {
		return $this->fs;
	}
	
	public function getRootAdapter() {
		return $this->root;
	}
	
	public function getParentAdapter() {
		return $this->parent;
	}
	
	public function getMD5($pathname, $binary)
	{
		return md5($this->getContents($pathname), $binary);
	}
	
	public function getSHA1($pathname, $binary)
	{
		return sha1($this->getContents($pathname), $binary);
	}
	
	public function count($pathname, array $filter)
	{
		foreach($this->getIterator() as $pathname) $i++;
		return $i;
	}
	
	protected function checkFile($pathname) {
		if(!$this->isFile($pathname)) {
			throw new Exception('Pathname is not a file');
		}
	}
	
	protected function checkDirectory($pathname) {
		if(!$this->isDirectory($pathname)) {
			throw new Exception('Pathname is not a directory');
		}
	}
	
}
