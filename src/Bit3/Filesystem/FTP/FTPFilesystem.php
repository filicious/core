<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\FTP;

use Bit3\Filesystem\AbstractSimpleFilesystem;
use Bit3\Filesystem\SimpleFile;
use Bit3\Filesystem\File;
use Bit3\Filesystem\PublicURLProvider;
use Bit3\Filesystem\Util;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFilesystem
	extends AbstractSimpleFilesystem
{
	const CONFIG_CLASS = 'Bit3\Filesystem\FTP\FTPFilesystemConfig';

	/**
	 * @var resource
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $cacheKey;

	/**
	 * @param FTPFilesystemConfig $config
	 */
	public function __construct(FTPFilesystemConfig $config, PublicURLProvider $provider = null)
	{
		parent::__construct($config, $provider);

		// TODO OH: since the cache comes from the config, this base key should
		// be generated there, too
		$this->cacheKey = 'ftpfs:' . ($this->config->getSSL() ? 'ssl:' : '') . $this->config->getUsername(
		) . '@' . $this->config->getHost() . ':' . $this->config->getPort() . ($this->config->getBasePath() ? : '/');

		if (!$this->config->getLazyConnect()) {
			$this->getConnection();
		}
	}

	public function __destruct()
	{
		if ($this->connection) {
			ftp_close($this->connection);
		}
	}

	/**
	 * Prepend a file path with the baseroot and normalize it.
	 *
	 * @param $file the file that shall get rebased
	 *
	 * @return string
	 */
	protected function realPath(File $file)
	{
		return Util::normalizePath(
			$this
				->getConfig()
				->getBasePath() . '/' . $file->getPathname()
		);
	}

	/**
	 * connect to the server.
	 */
	public function connect()
	{
		if ($this->connection !== null) {
			return;
		}

		if ($this->config->getSSL()) {
			$this->connection = ftp_ssl_connect(
				$this->config->getHost(),
				$this->config->getPort(),
				$this->config->getTimeout()
			);
		}
		else {
			$this->connection = ftp_connect(
				$this->config->getHost(),
				$this->config->getPort(),
				$this->config->getTimeout()
			);
		}

		if ($this->connection === false) {
			throw new FTPFilesystemConnectionException('Could not connect to ' . $this->config->getHost());
		}

		if ($this->config->getUsername()) {
			if (!ftp_login(
				$this->connection,
				$this->config->getUsername(),
				$this->config->getPassword()
			)
			) {
				throw new FTPFilesystemAuthenticationException('Could not login to ' . $this->config->getHost(
				) . ' with username ' . $this->config->getUsername() . ':' . ($this->config->getPassword() ? '*****'
					: 'NOPASS'));
			}
		}

		ftp_pasv($this->connection, $this->config->getPassiveMode());

		if ($this->config->getBasePath()) {
			if (!ftp_chdir($this->connection, $this->config->getBasePath())) {
				throw new FTPFilesystemException('Could not change into directory ' . $this->config->getBasePath(
				) . ' on ' . $this->config->getHost());
			}
		}
	}

	public function getConnection()
	{
		if (!$this->connection) {
			$this->connect();
		}
		return $this->connection;
	}

	/**
	 * Try to query the cache, if existant, for a value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function queryCache($key)
	{
		$cache = $this->config->getCache();
		if (!$cache) {
			return null;
		}
		return $cache->fetch($key);
	}

	/**
	 * Try to query the cache, if existant, for a value.
	 *
	 * @param string $key
	 *
	 */
	protected function setCache($key, $value)
	{
		$cache = $this->config->getCache();
		if ($cache) {
			$cache->store($key, $value);
		}
	}


	public function ftpStat(File $file)
	{
		$real     = $this->realPath($file);
		$cacheKey = $this->cacheKey . ':stat:' . $real;

		$cached = $this->queryCache($cacheKey);

		if ($cached === null) {
			$this->ftpList($file);

			$cached = $this->config
				->getCache()
				->fetch($cacheKey);
		}

		return $cached;
	}

	public function ftpList(File $file)
	{
		$real     = $this->realPath($file);
		$cacheKey = $this->cacheKey . ':list:' . $real;

		$cached = $this->queryCache($cacheKey);

		if ($cached === null) {
			$cached = array();
			$list   = ftp_rawlist($this->getConnection(), '-la ' . $real);

			$isSingleFile = true;

			foreach ($list as $item) {
				if (preg_match(
					'#^([\-ldrwxsSt]{10})\s+(\d+)\s+([\w\d]+)\s+([\w\d]+)\s+(\d+)\s+(\w{3}\s+\d{1,2}\s+(?:\d{2}:\d{2}|\d{4}))\s+(.*?)(\s+->\s+(.*))?$#s',
					$item,
					$match
				)
				) {
					$stat = (object) array(
						'perms'       => $match[1],
						'mode'        => Util::string2bitMode($match[1]),
						'type'        => (int) $match[2],
						'isDirectory' => $match[1][0] == 'd',
						'isFile'      => $match[1][0] != 'd',
						'isLink'      => $match[1][0] == 'l',
						'user'        => (int) $match[3],
						'group'       => (int) $match[4],
						'size'        => (int) $match[5],
						'modified'    => strtotime($match[6]),
						'name'        => $match[7],
						'target'      => isset($match[9]) ? $match[9] : null
					);

					if ($stat->name == '.') {
						$isSingleFile      = false;
						$directoryCacheKey = $this->cacheKey . ':stat:' . $real;
						$this->setCache($directoryCacheKey, $stat);
					}
					else if ($stat->name == '..') {
						if (dirname($real) != $real) {
							$directoryCacheKey = $this->cacheKey . ':stat:' . dirname($real);
							$this->setCache($directoryCacheKey, $stat);
						}
					}
					else {
						$fileCacheKey = $this->cacheKey . ':stat:' . $real . ($isSingleFile ? '' : '/' . $match[7]);
						$this->setCache($fileCacheKey, $stat);
						$cached[] = $stat;
					}
				}
				else {
					throw new FTPFilesystemException('Implementation error: Could not parse list item ' . $item);
				}
			}

			if ($isSingleFile) {
				$cached = false;
			}

			$this->setCache($cacheKey, $cached);
		}

		return $cached;
	}

	public function ftpChmod(File $file, $mode)
	{
		$stat = $this->ftpStat($file);

		if ($stat) {
			$real = $this->realPath($file);
			return ftp_chmod($this->getConnection(), $mode, $real);
		}

		return false;
	}

	public function ftpDelete(File $file)
	{
		$stat = $this->ftpStat($file);

		if ($stat) {
			$real = $this->realPath($file);

			if ($stat->isDirectory) {
				if (ftp_rmdir($this->getConnection(), $real)) {
					$this->config
						->getCache()
						->store($this->cacheKey . ':stat:' . $real, null);
					$this->config
						->getCache()
						->store($this->cacheKey . ':list:' . $real, null);
					$this->config
						->getCache()
						->store($this->cacheKey . ':list:' . dirname($real), null);
					return true;
				}
			}
			else {
				if (ftp_delete($this->getConnection(), $real)) {
					$this->config
						->getCache()
						->store($this->cacheKey . ':stat:' . $real, null);
					$this->config
						->getCache()
						->store($this->cacheKey . ':list:' . dirname($real), null);
					return true;
				}
			}
		}

		return false;
	}

	public function ftpStreamGet(File $source, $targetStream)
	{
		$stat = $this->ftpStat($source);

		if ($stat and !$stat->isDirectory) {
			$real = $this->realPath($source);

			return ftp_fget($this->getConnection(), $targetStream, $real, FTP_BINARY);
		}

		return false;
	}

	public function ftpStreamPut(File $target, $sourceStream)
	{
		$stat = $this->ftpStat($target);

		if (!$stat or !$stat->isDirectory) {
			$real = $this->realPath($target);

			return ftp_fput($this->getConnection(), $real, $sourceStream, FTP_BINARY);
		}

		return false;
	}

	public function ftpGet(File $source, File $target)
	{
		$stat = $this->ftpStat($source);

		if ($stat and !$stat->isDirectory) {
			$realSource = $this->realPath($source);
			// TODO: watch out, is not neccessary a local file.
			$realTarget = $target->getRealURL();

			return ftp_get($this->getConnection(), $realTarget, $realSource, FTP_BINARY);
		}

		return false;
	}

	public function ftpPut(File $target, File $source)
	{
		$stat = $this->ftpStat($target);

		if (!$stat or !$stat->isDirectory) {
			$realSource = $source->getRealURL();
			$realTarget = realPath($target);

			return ftp_put($this->getConnection(), $realTarget, $realSource, FTP_BINARY);
		}

		return false;
	}

	public function ftpMkdir(File $file)
	{
		$stat = $this->ftpStat($file);

		if (!$stat) {
			$real = $this->realPath($file);

			return ftp_mkdir($this->getConnection(), $real);
		}

		return false;
	}

	public function ftpRename(File $source, File $target)
	{
		$sourceStat = $this->ftpStat($source);
		$targetStat = $this->ftpStat($target);

		if ($sourceStat and (!$targetStat or (!$sourceStat['isDirectory'] and !$targetStat['isDirectory']))) {
			$realSource = $this->realPath($source);
			$realTarget = $this->realPath($target);

			return ftp_rename($this->getConnection(), $realSource, $realTarget);
		}

		return false;
	}

	public function ftpRmdir(File $file)
	{
		$stat = $this->ftpStat($file);

		if ($stat && $stat->isDirectory) {
			$real = $this->realPath($file);

			return ftp_rmdir($this->getConnection(), $real);
		}

		return false;
	}

	/**************************************************************************
	 * Interface Filesystem
	 *************************************************************************/

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return new SimpleFile('/', $this);
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path)
	{
		return new SimpleFile($path, $this);
	}

	/**
	 * Returns available space on filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getFreeSpace(File $path = null)
	{
		return -1;
	}

	/**
	 * Returns the total size of a filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getTotalSpace(File $path = null)
	{
		return -1;
	}

	/**************************************************************************
	 * Interface SimpleFilesystem
	 *************************************************************************/

	/**
	 * Get the type of this file.
	 *
	 * @return int Type bitmask
	 */
	public function getTypeOf($file)
	{
		$type = 0;
		$stat = $this->ftpStat($file);
		if ($stat) {
			$stat->isFile && $type |= File::TYPE_FILE;
			$stat->isLink && $type |= File::TYPE_LINK;
			$stat->isDirectory && $type |= File::TYPE_DIRECTORY;
		}
		return $type;
	}

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($file)
	{
		$stat = $this->fs->ftpStat($this);

		return $stat && $stat->isLink ? $stat->target : false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($file)
	{
		return $this->getModifyTimeOf($file);
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($file, $time)
	{
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($file)
	{
		return $this->getModifyTimeOf($file);
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? $stat->modified : false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($file, $time)
	{
		return false;
	}

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $file the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($file, $time = null, $atime = null, $doNotCreate = false)
	{
		return false;
	}

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? $stat->size : false;
	}

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? $stat->user : false;
	}

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($file, $user)
	{
		return false;
	}

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? $stat->group : false;
	}

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($file, $group)
	{
		return false;
	}

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? $stat->mode : false;
	}

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($file, $mode)
	{
		return $this->ftpChmod($file, $mode);
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($file)
	{
		$stat = $this->ftpStat($file);

		return $stat ? true : false;
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param File $file the file
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($file, $recursive = false, $force = false)
	{
		// TODO: invalidate cache after delete?
		if ($file->isDirectory()) {
			if ($recursive) {
				/** @var File $file */
				foreach ($file->ls() as $file) {
					if (!$file->delete(true, $force)) {
						return false;
					}
				}
			}
			else if ($file->count() > 0) {
				return false;
			}
			return $this->ftpDelete($file);
		}
		else {
			if (!$file->isWritable()) {
				if ($force) {
					$file->setMode(0666);
				}
				else {
					return false;
				}
			}
			return $this->ftpDelete($file);
		}
	}

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo($file, File $destination, $parents = false)
	{
		if ($file->isDirectory()) {
			// TODO: recursive directory copy.
		}
		else if ($file->isFile()) {
			Util::streamCopy($file, $destination);
		}
	}

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo($file, File $destination)
	{
		if ($destination->getFilesystem() == $this) {
			// TODO: invalidate cache?
			return $this->ftpRename($file, $destination);
		}
		else {
			return Util::streamCopy($file, $destination) && $file->delete();
		}
	}

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($file, $parents = false)
	{
		if ($file->exists()) {
			return $file->isDirectory();
		}
		else if ($parents) {
			$parent = $file->getParent();
			if (!$parent->createDirectory(true)) {
				return false;
			}
		}
		return $this->ftpMkdir($file);
	}

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($file, $parents = false)
	{
		$parent = $file->getParent();
		if ($parents) {
			if (!($parent && $parent->createDirectory(true))) {
				return false;
			}
		}
		else if (!($parent && $parent->isDirectory())) {
			return false;
		}

		$stream = fopen('php://memory', 'w+');

		// write empty string to initialize the stream,
		// otherwise something unexpected may happen
		fwrite($stream, '');

		return $this->ftpStreamPut($file, $stream);
	}

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($file)
	{
		$stat = $this->ftpStat($file);

		if ($stat) {
			// TODO: get rid of system temporary call here.
			$tempFS   = FS::getSystemTemporaryFilesystem();
			$tempFile = $tempFS->createTempFile('ftp_');

			if ($this->ftpGet($file, $tempFile)) {
				return $tempFile->getContents();
			}

			return false;
		}

		return null;
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($file, $content)
	{
		if ($file->exists() && !$file->isFile()) {
			return false;
		}

		$tempFS   = FS::getSystemTemporaryFilesystem();
		$tempFile = $tempFS->createTempFile('ftp_');
		$tempFile->setContents($content);

		return $this->ftpPut($this, $tempFile);
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($file, $content)
	{
		$previous = $file->getContents();
		return $this->ftpPut($file, $previous . $content);
	}

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($file, $size = 0)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}

		$content = '';

		// TODO: implement expanding

		if ($size > 0) {
			$content = $file->getContents();
			$content = substr($content, 0, $size);
		}
		return $this->fs->ftpPut($file, $content);
	}

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($file, $mode = 'rb')
	{
		$cfg = $this->getConfig();
		$url = $cfg->toURL(false, true) . $file->getPathname();

		$stream_options = array($cfg->getProtocol() => array('overwrite' => true));
		$stream_context = stream_context_create($stream_options);

		$fp = fopen($url, $mode, null, $stream_context);

		if (!$fp) {
			throw new Exception('FTP connection error'); // TODO
		}

		stream_set_timeout(
			$fp,
			$cfg->getTimeoutSeconds(),
			$cfg->getTimeoutMilliseconds()
		);

		return $fp;
	}

	/**
	 * List files.
	 *
	 * @param int|string|callable Multiple list of LIST_* bitmask, glob pattern and callables to filter the list.
	 *
	 * @return array<File>
	 */
	public function lsFile()
	{
		$args = func_get_args();
		$file = array_shift($args);

		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($file, $args);

		$pathname = $file->getPathname();

		$files = array();

		$currentFiles = $this->ftpList($file);

		foreach ($currentFiles as $stat) {
			$file = new SimpleFile($pathname . '/' . $stat->name, $this);

			$files[] = $file;

			if ($recursive &&
				basename($stat->name) != '.' &&
				basename($stat->name) != '..' &&
				$stat->isDirectory ||
				count($globSearchPatterns) &&
					Util::applyGlobFilters($file, $globSearchPatterns)
			) {
				$recursiveFiles = $file->ls();

				$files = array_merge(
					$files,
					$recursiveFiles
				);
			}
		}

		$files = Util::applyFilters($files, $bitmask, $globs, $callables);

		return $files;
	}

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @return string
	 */
	public function getRealURLOf($file)
	{
		return $this
			->getConfig()
			->toURL() . $this->pathname;
	}
}
