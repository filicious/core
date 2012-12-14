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

namespace Filicious\Temp;

use Filicious\File;
use Filicious\TemporaryFilesystem;
use Filicious\Local\LocalFilesystem;
use Filicious\Local\LocalFile;

/**
 * Temporary filesystem adapter.
 *
 * The temporary filesystem is a special version of a local filesystem, that can be used to handle temporary files.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalTemporaryFilesystem
	extends LocalFilesystem
	implements TemporaryFilesystem
{
	/**
	 * @var array
	 */
	protected $temporaryFiles = array();

	public function __destruct()
	{
		/** @var File $file */
		foreach ($this->temporaryFiles as $file) {
			if ($file->exists()) {
				$file->delete(true, true);
			}
		}
	}

	public function getFile($path)
	{
		$file = parent::getFile($path);

		if (!$file->exists()) {
			$this->temporaryFiles[] = $file;
		}

		return $file;
	}

	public function createTempFile($prefix)
	{
		// create a temporary file
		$pathname = tempnam($this->getBasePath(), $prefix);

		// remove the base path from pathname
		$file = substr($pathname, strlen($this->getBasePath()));

		// create new local file object
		$file = $this->getFile($file);

		return $file;
	}

	public function createTempDirectory($prefix)
	{
		// create a temporary file
		$file = $this->createTempFile($prefix);

		// delete the file and...
		$file->delete();

		// finally create a directory
		$file->createDirectory();

		// return the local file object
		return $file;
	}
}