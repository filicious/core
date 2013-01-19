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


/**
 * An adapter that aggregate other adapters.
 * The status objects are retrieved from the inner adapters according to the
 * aggregation rules which are implementation specific.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AggregateAdapter
	extends AbstractAdapter
{
	
	protected $default;
	
	protected $registry;
	
	protected $map;
	
	protected function __construct() {
		parent::__construct();
		$entry = new stdClass();
		$entry->adapter = new VirtualAdapter();
		$entry->pathname = '';
		$this->default = array(spl_object_hash($entry) => $entry);
		$this->registry = array();
		$this->map = array();
	}
	
	public abstract function selectAdapter($pathname, array $entries);
	
	public function addAdapter($pathname, Adapter $adapter) {
		$entry = new stdClass();
		$entry->pathname = $pathname;
		$entry->adapter = $adapter;
		$hash = spl_object_hash($entry);
		$this->registry[$pathname][$hash] = $adapter;
		// update mapping for subpaths
		if(!isset($this->map[$pathname])) {
			$entries = $this->resolvePathname($pathname);
			$this->map[$pathname] = $entries === $this->default ? array() : $entries;
		}
		$n = strlen($pathname);
		foreach($this->map as $key => &$entries) if(strncmp($pathname, $key, $n) === 0) {
			$entries[$hash] = $entry;
		}
		return $hash;
	}
	
	public function removeAdapter($pathname, $hash = null) {
		$reg = &$this->registry[$pathname];
		if(!isset($reg)) {
			return array();
		} elseif($hash === null) {
			$removed = $reg;
			unset($this->registry[$pathname]);
			unset($this->map[$pathname]);
		} elseif(isset($reg[$hash])) {
			$removed = array($hash => $reg[$hash]);
			if(count($reg) > 1) {
				unset($reg[$hash]);
			} else {
				unset($this->registry[$pathname]);
				unset($this->map[$pathname]);
			}
		} else {
			return array();
		}
		$n = strlen($pathname);
		foreach($this->map as $key => &$entries) if(strncmp($pathname, $key, $n) === 0) {
			$entries = array_diff_key($entries, $removed);
		}
		return $removed;
	}
	
	public function getAdapter($pathname, &$local = null) {
		$entry = $this->selectAdapter($pathname, $this->resolvePathname($pathname));
		$local = substr($pathname, strlen($entry->pathname) + 1);
		return $entry->adapter;
	}
	
	public function resolvePathname($pathname) {
		do if(isset($this->map[$pathname])) {
			return $this->map[$pathname];
		} while('' !== $pathname = dirname($pathname));
		return $this->default;
	}
	
	public function getStreamURL($pathname) {
		return $this->getAdapter($pathname, $local)->getStreamURL($local);
	}

	public function getType($pathname) {
		return $this->getAdapter($pathname, $local)->getType($local);
	}

	public function getLinkTarget($pathname) {
		return $this->getAdapter($pathname, $local)->getLinkTarget($local);
	}

	public function getAccessTime($pathname) {
		return $this->getAdapter($pathname, $local)->getAccessTime($local);
	}
	
	public function setAccessTime($pathname, $time) {
		return $this->getAdapter($pathname, $local)->setAccessTime($local, $time);
	}
	
	public function getCreationTime($pathname) {
		return $this->getAdapter($pathname, $local)->getCreationTime($local);
	}
	
	public function getModifyTime($pathname) {
		return $this->getAdapter($pathname, $local)->getModifyTime($local);
	}
	
	public function setModifyTime($pathname, $time) {
		return $this->getAdapter($pathname, $local)->setModifyTime($local, $time);
	}
	
	public function touch($pathname, $time, $atime, $create) {
		return $this->getAdapter($pathname, $local)->touch($local, $time, $atime, $create);
	}
	
	public function getSize($pathname) {
		return $this->getAdapter($pathname, $local)->getSize($local);
	}
	
	public function getOwner($pathname) {
		return $this->getAdapter($pathname, $local)->getOwner($local);
	}
	
	public function setOwner($pathname, $user) {
		return $this->getAdapter($pathname, $local)->setOwner($local, $user);
	}
	
	public function getGroup($pathname) {
		return $this->getAdapter($pathname, $local)->getGroup($local);
	}
	
	public function setGroup($pathname, $group) {
		return $this->getAdapter($pathname, $local)->setGroup($local, $group);
	}
	
	public function getMode($pathname) {
		return $this->getAdapter($pathname, $local)->getMode($local);
	}
	
	public function setMode($pathname, $mode) {
		return $this->getAdapter($pathname, $local)->setMode($local, $mode);
	}
	
	public function isReadable($pathname) {
		return $this->getAdapter($pathname, $local)->isReadable($local);
	}
	
	public function isWritable($pathname) {
		return $this->getAdapter($pathname, $local)->isWritable($local);
	}
	
	public function isExecutable($pathname) {
		return $this->getAdapter($pathname, $local)->isExecutable($local);
	}
	
	public function exists($pathname) {
		return $this->getAdapter($pathname, $local)->exists($local);
	}
	
	public function delete($pathname, $recursive, $force) {
		return $this->getAdapter($pathname, $local)->delete($local, $recursive, $force);
	}
	
	public function copyTo($pathname, $destination, $parents) {
		return $this->getAdapter($pathname, $local)->copyTo($local, $destination, $parents);
	}
	
	public function moveTo($pathname, $destination, $parents) {
		return $this->getAdapter($pathname, $local)->moveTo($local, $destination, $parents);
	}
	
	public function createDirectory($pathname, $parents) {
		return $this->getAdapter($pathname, $local)->createDirectory($local, $parents);
	}
	
	public function createFile($pathname, $parents) {
		return $this->getAdapter($pathname, $local)->createFile($local, $parents);
	}
	
	public function getContents($pathname) {
		return $this->getAdapter($pathname, $local)->getContents($local);
	}
	
	public function setContents($pathname, $content) {
		return $this->getAdapter($pathname, $local)->setContents($local, $content);
	}
	
	public function appendContents($pathname, $content) {
		return $this->getAdapter($pathname, $local)->appendContents($local, $content);
	}
	
	public function truncate($pathname, $size) {
		return $this->getAdapter($pathname, $local)->truncate($local, $size);
	}
	
	public function getStream($pathname) {
		return $this->getAdapter($pathname, $local)->getStream($local);
	}
	
	public function getMIMEName($pathname) {
		return $this->getAdapter($pathname, $local)->getMIMEName($local);
	}
	
	public function getMIMEType($pathname) {
		return $this->getAdapter($pathname, $local)->getMIMEType($local);
	}
	
	public function getMIMEEncoding($pathname) {
		return $this->getAdapter($pathname, $local)->getMIMEEncoding($local);
	}
	
	public function getMD5($pathname, $binary) {
		return $this->getAdapter($pathname, $local)->getMD5($local, $binary);
	}
	
	public function getSHA1($pathname, $binary) {
		return $this->getAdapter($pathname, $local)->getSHA1($local, $binary);
	}
	
	public function ls($pathname, array $filter) {
		return $this->getAdapter($pathname, $local)->ls($local, $filter);
	}
	
	public function getFreeSpace($pathname) {
		return $this->getAdapter($pathname, $local)->getFreeSpace($local);
	}
	
	public function getTotalSpace($pathname) {
		return $this->getAdapter($pathname, $local)->getTotalSpace($local);
	}
	
}
