<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author	Oliver Hoff <oliver@hofff.com>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

/**
 * Static class to handle file flags
 * 
 * @package	php-filesystem
 * @author	Oliver Hoff <oliver@hofff.com>
 */
final class FileFlags {
	
	public function isFile($flags) {
		return (bool) ($flags & File::TYPE_FILE);
	}
	
	public function isLink($flags) {
		return (bool) ($flags & File::TYPE_LINK);
	}
	
	public function isDirectory($flags) {
		return (bool) ($flags & File::TYPE_DIRECTORY);
	}

	private function __construct() {}
}
