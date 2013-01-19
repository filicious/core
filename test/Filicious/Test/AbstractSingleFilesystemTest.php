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

use Filicious\FilesystemConfig;
use Filicious\Iterator\FilesystemIterator;
use Filicious\Iterator\RecursiveFilesystemIterator;
use Filicious\Exception\FileNotFoundException;
use PHPUnit_Framework_TestCase;

/**
 * @outputBuffering disabled
 */
abstract class AbstractSingleFilesystemTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var SingleFilesystemTestEnvironment
	 */
	protected $environment;

	/**
	 * @var TestAdapter
	 */
	protected $adapter;

	/**
	 * @var \Filicious\Filesystem
	 */
	protected $fs;

	protected $files = array(
		'example.txt',
		'zap/file.txt',
	);

	protected $dirs = array(
		'foo',
		'foo/bar',
		'zap',
	);

	protected $links = array(
		'foo/file.lnk' => 'file',
		'zap/bar.lnk'  => 'dir',
	);

	protected $notExists = array(
		'does_not_exists.missing',
		'foo/does_not_exists.missing',
		'foo/bar/does_not_exists.missing',
		'zap/does_not_exists.missing',
	);

	/**
	 * @return SingleFilesystemTestEnvironment
	 */
	abstract protected function setUpEnvironment();

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->environment = $this->setUpEnvironment();
		$this->adapter     = $this->environment->getAdapter();
		$this->fs          = $this->environment->getFilesystem();

		// create directory <path>/foo/bar/
		$this->adapter->createDirectory('/foo');
		$this->adapter->createDirectory('/foo/bar');

		// create directory <path>/zap
		$this->adapter->createDirectory('/zap');

		// create file <path>/example.txt
		$this->adapter->putContents('/example.txt', 'The world is like a pizza!');

		// create file <path>/zap/file.txt
		$this->adapter->putContents('/zap/file.txt', 'Hello World!');

		if ($this->adapter->isSymlinkSupported()) {
			// create link <path>/foo/zap.lnk -> ../zap/file.txt
			$this->adapter->symlink('../zap/file.txt', '/foo/file.lnk');

			// create link <path>/zap/bar.lnk -> ../foo/bar/
			$this->adapter->symlink('../foo/bar/', '/zap/bar.lnk');
		}
	}

	protected function tearDown()
	{
		$this->environment->cleanup();
	}

	/**
	 * @covers Bit3\Filesystem\Local\LocalFilesystem::getRoot
	 */
	public function testGetRoot()
	{
		$actual = $this->fs
			->getRoot()
			->getPathname();

		$this->assertEquals('/', $actual);
	}

	/**
	 * @covers Bit3\Filesystem\Local\LocalFilesystem::getFile
	 */
	public function testGetFile()
	{
		// TODO this is the same test as testGetPathname

		// test files without leading '/'
		foreach ($this->files as $pathname) {
			$actual = $this->fs
				->getFile($pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test files with leading '/'
		foreach ($this->files as $pathname) {
			$actual = $this->fs
				->getFile('/' . $pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test directories without leading '/'
		foreach ($this->dirs as $pathname) {
			$actual = $this->fs
				->getFile($pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test directories with leading '/'
		foreach ($this->dirs as $pathname) {
			$actual = $this->fs
				->getFile('/' . $pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test links without leading '/'
		foreach ($this->links as $pathname => $type) {
			$actual = $this->fs
				->getFile($pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test links with leading '/'
		foreach ($this->links as $pathname => $type) {
			$actual = $this->fs
				->getFile('/' . $pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test non existing files without leading '/'
		foreach ($this->notExists as $pathname) {
			$actual = $this->fs
				->getFile($pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}

		// test non existing files with leading '/'
		foreach ($this->notExists as $pathname) {
			$actual = $this->fs
				->getFile('/' . $pathname)
				->getPathname();
			$this->assertEquals('/' . $pathname, $actual);
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isLink
	 */
	public function testIsLink()
	{
		// TODO check fs ability: symlinks supported

		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isLink());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isLink());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertTrue($file->isLink());
			}

			// test non existing files
			foreach ($this->notExists as $pathname) {
				$file = $this->fs->getFile($pathname);
				$this->assertFalse($file->isLink());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isLink() test, symlinks not supported.');
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isFile
	 */
	public function testIsFile()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isFile());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isFile());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				switch ($type) {
					case 'file':
						$this->assertTrue($file->isFile());
						break;
					case 'dir':
						$this->assertFalse($file->isFile());
						break;
				}
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isFile() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isFile());
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isDirectory
	 */
	public function testIsDirectory()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isDirectory());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isDirectory());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				switch ($type) {
					case 'file':
						$this->assertFalse($file->isDirectory());
						break;
					case 'dir':
						$this->assertTrue($file->isDirectory());
						break;
				}
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isDirectory() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isDirectory());
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::exists
	 */
	public function testExists()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->exists());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->exists());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertTrue($file->exists());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::exists() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->exists());
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getPathname
	 */
	public function testGetPathname()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals('/' . $pathname, $file->getPathname());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals('/' . $pathname, $file->getPathname());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals('/' . $pathname, $file->getPathname());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getPathname() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals('/' . $pathname, $file->getPathname());
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getBasename
	 */
	public function testGetBasename()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(basename($pathname), $file->getBasename());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(basename($pathname), $file->getBasename());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(basename($pathname), $file->getBasename());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getBasename() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(basename($pathname), $file->getBasename());
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getExtension
	 */
	public function testGetExtension()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				preg_match('#^.*\.(\w+)$#', $pathname, $match) ? $match[1] : null,
				$file->getExtension()
			);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				preg_match('#^.*\.(\w+)$#', $pathname, $match) ? $match[1] : null,
				$file->getExtension()
			);
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					preg_match('#^.*\.(\w+)$#', $pathname, $match) ? $match[1] : null,
					$file->getExtension()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getExtension() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				preg_match('#^.*\.(\w+)$#', $pathname, $match) ? $match[1] : null,
				$file->getExtension()
			);
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getParent
	 */
	public function testGetParent()
	{
		// test root parent
		$this->assertEquals(
			null,
			$this->fs
				->getRoot()
				->getParent()
		);

		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				dirname($pathname) != '.' ? $this->fs->getFile(dirname($pathname)) : $this->fs->getRoot(),
				$file->getParent()
			);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				dirname($pathname) != '.' ? $this->fs->getFile(dirname($pathname)) : $this->fs->getRoot(),
				$file->getParent()
			);
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					dirname($pathname) != '.' ? $this->fs->getFile(dirname($pathname)) : $this->fs->getRoot(),
					$file->getParent()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getParent() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				dirname($pathname) != '.' ? $this->fs->getFile(dirname($pathname)) : $this->fs->getRoot(),
				$file->getParent()
			);
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getAccessTime
	 */
	public function testGetAccessTime()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getATime('/' . $pathname),
				$file->getAccessTime()
			);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getATime('/' . $pathname),
				$file->getAccessTime()
			);
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					$this->adapter->getATime('/' . $pathname),
					$file->getAccessTime()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getAccessTime() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getAccessTime());
				$this->fail('File::getAccessTime() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getAccessTime() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getCreationTime
	 */
	public function testGetCreationTime()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getCTime('/' . $pathname),
				$file->getCreationTime()
			);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getCTime('/' . $pathname),
				$file->getCreationTime()
			);
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					$this->adapter->getCTime('/' . $pathname),
					$file->getCreationTime()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getCreationTime() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getCreationTime());
				$this->fail('File::getCreationTime() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getCreationTime() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getLastModified
	 */
	public function testGetLastModified()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getMTime('/' . $pathname),
				$file->getModifyTime()
			);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(
				$this->adapter->getMTime('/' . $pathname),
				$file->getModifyTime()
			);
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					$this->adapter->getMTime('/' . $pathname),
					$file->getModifyTime()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getModifyTime() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getModifyTime());
				$this->fail('File::getLastModified() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getLastModified() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getSize
	 */
	public function testGetSize()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getFileSize('/' . $pathname), $file->getSize());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals(0, $file->getSize());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				switch ($type) {
					case 'file':
						$this->assertEquals(
							$this->adapter->getFileSize('/' . $pathname),
							$file->getSize()
						);
						break;

					case 'dir':
						$this->assertEquals(
							0,
							$file->getSize()
						);
						break;
				}
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getSize() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getSize());
				$this->fail('File::getSize() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getSize() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getOwner
	 */
	public function testGetOwner()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getOwner('/' . $pathname), $file->getOwner());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getOwner('/' . $pathname), $file->getOwner());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					$this->adapter->getOwner('/' . $pathname),
					$file->getOwner()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getOwner() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getOwner());
				$this->fail('File::getOwner() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getOwner() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getGroup
	 */
	public function testGetGroup()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getGroup('/' . $pathname), $file->getGroup());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getGroup('/' . $pathname), $file->getGroup());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals(
					$this->adapter->getGroup('/' . $pathname),
					$file->getGroup()
				);
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getGroup() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getGroup());
				$this->fail('File::getGroup() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getGroup() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::getMode
	 */
	public function testGetMode()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getMode('/' . $pathname), $file->getMode());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertEquals($this->adapter->getMode('/' . $pathname), $file->getMode());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertEquals($this->adapter->getMode('/' . $pathname), $file->getMode());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::getMode() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertEquals(null, $file->getMode());
				$this->fail('File::getMode() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::getMode() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isReadable
	 */
	public function testIsReadable()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isReadable());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isReadable());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertTrue($file->isReadable());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isReadable() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertFalse($file->isReadable());
				$this->fail('File::isReadable() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::isReadable() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isWritable
	 */
	public function testIsWritable()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isWritable());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isWritable());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				$this->assertTrue($file->isWritable());
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isWriteable() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertTrue($file->isWritable());
				$this->fail('File::isWriteable() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::isWriteable() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	/**
	 * @covers Filicious\Local\LocalFile::isExecutable
	 */
	public function testIsExecutable()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertFalse($file->isExecutable());
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$this->assertTrue($file->isExecutable());
		}

		if ($this->adapter->isSymlinkSupported()) {
			// test links
			foreach ($this->links as $pathname => $type) {
				$file = $this->fs->getFile($pathname);
				switch ($type) {
					case 'file':
						$this->assertFalse($file->isExecutable());
						break;
					case 'dir':
						$this->assertTrue($file->isExecutable());
						break;
				}
			}
		}
		else {
			$this->markTestSkipped('Skip Symlink::isExecutable() test, symlinks not supported.');
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$this->assertFalse($file->isExecutable());
				$this->fail('File::isExecutable() on a non existing file does NOT throw a FileNotFoundException!');
			}
			catch (FileNotFoundException $e) {
				// hide
			} catch (\Exception $e) {
				$this->fail('File::isExecutable() on a non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e));
			}
		}
	}

	public function testTree()
	{
		$this->markTestIncomplete();
		return;

		$filesystemIterator = new RecursiveFilesystemIterator($this->fs->getRoot(
		                                                      ), FilesystemIterator::CURRENT_AS_BASENAME);
		$treeIterator       = new \RecursiveTreeIterator($filesystemIterator);

		foreach ($treeIterator as $path) {
			echo $path . "\n";
		}
	}
}
