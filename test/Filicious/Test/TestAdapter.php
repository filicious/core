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

namespace Filicious\Test;

/**
 * A test adapter allow direct access to a filesystem.
 * This adapter is an alternative, low-level implementation to the filesystem.
 */
interface TestAdapter
{
	public function createDirectory($path);

	public function putContents($path, $content);

	public function getContents($path);

	public function deleteFile($path);

	public function deleteDirectory($path);

	public function isFile($path);

	public function isDirectory($path);

	public function isLink($path);

	public function exists($path);

	public function getAccessTime($path);

	public function getCreationTime($path);

	public function getModifyTime($path);

	public function getFileSize($path);

	public function getOwner($path);

	public function getGroup($path);

	public function getMode($path);

	public function stat($path);

	public function scandir($path);
}
