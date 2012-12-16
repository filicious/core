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

namespace Filicious\Stream;

/**
 * Universal stream wrapper implementation.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class StreamWrapper
{
	/**
	 * @var null
	 */
	public $context = null;

	/**
	 * @var object
	 */
	protected $url = null;

	/**
	 * @var \Filicious\Filesystem
	 */
	protected $fs = null;

	/**
	 * @var \Filicious\File
	 */
	protected $file = null;

	/**
	 * @var \Filicious\Iterator\FilesystemIterator
	 */
	protected $directoryIterator = null;

	/**
	 * @var \Filicious\Stream
	 */
	protected $stream = null;

	/**
	 * Search for the filesystem and open a file from stream url.
	 *
	 * @param string $url
	 *
	 * @return \Filicious\File
	 */
	protected function openFile($url)
	{
		$this->url = (object) array_merge(
			array(
			     'scheme'   => 'filicious', // e.g. http
			     'host'     => '',
			     'port'     => '',
			     'user'     => '',
			     'pass'     => '',
			     'path'     => '',
			     'query'    => '', // after the question mark ?
			     'fragment' => '', // after the hashmark #

			),
			parse_url($url)
		);

		$host = $this->url['host'];
		if (strlen($this->url['port'])) {
			$host .= ':' . $this->url['port'];
		}

		// search the filesystem bound to the scheme+host
		$this->fs = StreamManager::searchFilesystem($host, $this->url['scheme']);

		// get the file from the filesystem
		$this->file = $this->fs->getFile($this->url['path']);

		return $this->file;
	}

	/**
	 * Create a directory
	 *
	 * @param string $path
	 * @param int    $mode
	 * @param int    $options
	 *
	 * @return boolean
	 */
	public function mkdir($path, $mode, $options)
	{
		$this->openFile($path);

		// TODO handle $mode
		return $this->file->createDirectory($options & STREAM_MKDIR_RECURSIVE);
	}

	/**
	 * Renames a file or directory
	 *
	 * @param string $path_from
	 * @param string $path_to
	 *
	 * @return boolean
	 */
	public function rename($path_from, $path_to)
	{
		$source = $this->openFile($path_from);
		$target = $this->openFile($path_to);

		return $source->moveTo($target);
	}

	/**
	 * Removes a directory
	 *
	 * @param string $path
	 * @param int    $options
	 *
	 * @return bool
	 */
	public function rmdir($path, $options)
	{
		$this->openFile($path);

		return $this->file->delete($options & STREAM_MKDIR_RECURSIVE);
	}

	/**
	 * Delete a file
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	public function unlink($path)
	{
		$this->openFile($path);

		return $this->file->delete();
	}

	/**
	 * Open directory handle
	 *
	 * @param string $path
	 * @param int    $options
	 *
	 * @return boolean
	 */
	public function dir_opendir($path, $options)
	{
		$this->openFile($path);

		if ($this->file->isDirectory()) {
			$this->directoryIterator = $this->file->getIterator();
			return true;
		}

		return false;
	}

	/**
	 * Close directory handle.
	 *
	 * @return boolean
	 */
	public function dir_closedir()
	{
		unset($this->directoryIterator);
		return true;
	}

	/**
	 * Read entry from directory handle.
	 *
	 * @return string|boolean
	 */
	public function dir_readdir()
	{
		$this->directoryIterator->next();

		if ($this->directoryIterator->valid()) {
			return $this->directoryIterator->current()->getURL();
		}

		return false;
	}

	/**
	 * Rewind directory handle.
	 *
	 * @return boolean
	 */
	public function dir_rewinddir()
	{
		$this->directoryIterator->rewind();
		return true;
	}

	/**
	 * Opens file or URL
	 *
	 * @param string $path
	 * @param string $mode
	 * @param int    $options
	 * @param string $opened_path
	 *
	 * @return bool
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->openFile($path);

		$this->stream = $this->file->getStream();

		$mode = new StreamMode($mode);

		if ($this->stream->open($mode)) {
			if ($options & STREAM_USE_PATH) {
				$opened_path = $path;
			}

			return true;
		}

		return false;
	}

	/**
	 * Close an resource
	 */
	public function stream_close()
	{
		$this->stream->close();

		unset($this->stream);
	}

	/**
	 * Retrieve the underlaying resource
	 *
	 * @param int $cast_as
	 *
	 * @return resource
	 */
	public function stream_cast($cast_as)
	{
		return $this->stream->cast($cast_as);
	}

	/**
	 * Retrieve information about a file resource
	 *
	 * @return array
	 */
	public function stream_stat()
	{
		return $this->stream->stat();
	}

	/**
	 * Retrieve information about a file
	 *
	 * @param string $path
	 * @param int    $flags
	 */
	public function url_stat($path, $flags)
	{
		// TODO implement
	}

	/**
	 * Advisory file locking
	 *
	 * @param mode $operation
	 *
	 * @return boolean
	 */
	public function stream_lock($operation)
	{
		return $this->stream->lock($operation);
	}

	/**
	 * Seeks to specific location in a stream
	 *
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return boolean
	 */
	public function stream_seek($offset, $whence = SEEK_SET)
	{
		return $this->stream->seek($offset, $whence);
	}

	/**
	 * Retrieve the current position of a stream
	 *
	 * @return int
	 */
	public function stream_tell()
	{
		return $this->stream->tell();
	}

	/**
	 * Tests for end-of-file on a file pointer
	 *
	 * @return boolean
	 */
	public function stream_eof()
	{
		return $this->stream->eof();
	}

	/**
	 * Truncate stream
	 *
	 * @param int $new_size
	 *
	 * @return boolean
	 */
	public function stream_truncate($new_size)
	{
		return $this->stream->truncate($new_size);
	}

	/**
	 * Read from stream
	 *
	 * @param int $count
	 *
	 * @return string
	 */
	public function stream_read($count)
	{
		return $this->stream->read($count);
	}

	/**
	 * Write to stream
	 *
	 * @param string $data
	 *
	 * @return int
	 */
	public function stream_write($data)
	{
		return $this->stream->write($data);
	}

	/**
	 * Flushes the output
	 *
	 * @return boolean
	 */
	public function stream_flush()
	{
		return $this->stream->flush();
	}
}
