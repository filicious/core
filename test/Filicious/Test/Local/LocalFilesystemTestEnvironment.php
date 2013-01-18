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

namespace Filicious\Test\Local;

use Filicious\Filesystem;
use Filicious\Local\LocalAdapter;
use Filicious\Test\SingleFilesystemTest;
use Filicious\Test\SingleFilesystemTestEnvironment;
use PHPUnit_Framework_TestSuite;

class LocalFilesystemTestEnvironment
	implements SingleFilesystemTestEnvironment
{
	protected $temporaryPath;

	protected $adapter;

	protected $localAdapter;

	protected $fs;

	function __construct()
	{
		/** create a test structure */
		$this->temporaryPath = tempnam(sys_get_temp_dir(), 'php_filesystem_test_');
		unlink($this->temporaryPath);
		mkdir($this->temporaryPath);

		$this->adapter = new LocalTestAdapter($this->temporaryPath);

		$this->localAdatper = new LocalAdapter($this->temporaryPath);
		$this->fs = new Filesystem($this->localAdatper);
	}

	/**
	 * @return TestAdapter
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * @return \Filicious\Filesystem
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * Cleanup environment.
	 */
	public function cleanup()
	{
		// delete temporary files
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$this->temporaryPath,
				\FilesystemIterator::SKIP_DOTS));

		/** @var \SplFileInfo $path */
		foreach ($iterator as $path) {
			unlink($path->getPathname());
		}

		// delete temporary directories
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$this->temporaryPath),
			\RecursiveIteratorIterator::CHILD_FIRST);

		/** @var \SplFileInfo $path */
		foreach ($iterator as $path) {
			if ($path->getBasename() != '.' and $path->getBasename() != '..') {
				rmdir($path->getPathname());
			}
		}

		rmdir($this->temporaryPath);

		unset($this->fs);

		unset($this->adapter);
	}
}
