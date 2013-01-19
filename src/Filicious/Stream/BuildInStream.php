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

use Filicious\File;
use Filicious\Stream;
use Filicious\Stream\StreamMode;
use Filicious\Internals\Pathname;

/**
 * A file stream object.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class BuildInStream extends ObservableStream
{
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var Pathname
	 */
	protected $pathname;

	/**
	 * @var StreamMode
	 */
	protected $mode = null;

	/**
	 * @var resource
	 */
	protected $resource = null;

	/**
	 * @var null|int
	 */
	protected $streamIndex = null;

	/**
	 * @var resource
	 */
	protected $selfResource = null;

	/**
	 * @param string $url
	 */
	public function __construct($url, Pathname $file)
	{
		$this->url  = $url;
		$this->pathname = $file;
	}

	/**
	 * @return \Filicious\File
	 */
	public function getFile()
	{
		return $this->pathname->rootAdapter()->getFilesystem()->getFile($this->pathname);
	}

	/**
	 * @return resource
	 */
	public function getResource()
	{
		if (!$this->mode) {
			throw new \Filicious\Exception\StreamException('Stream not open!'); // TODO
		}
		if ($this->streamIndex === null) {
			$this->streamIndex = StreamManager::registerStream($this);
		}
		return $this->selfResource = fopen('filicious-streams://' . $this->streamIndex, $this->mode->getMode());
	}

	/**
	 * Opens file or URL
	 */
	public function open(StreamMode $mode)
	{
		$this->mode = $mode;
		$this->resource = fopen($this->url, $mode->getMode());

		$this->notifyOpened($mode);

		return (bool) $this->resource;
	}

	/**
	 * Close an resource
	 */
	public function close()
	{
		if ($this->selfResource !== null) {
			fclose($this->selfResource);
		}
		return fclose($this->resource);
	}

	/**
	 * Retrieve the underlaying resource
	 *
	 * @param int $as
	 *
	 * @return resource|boolean
	 */
	public function cast($as)
	{
		return $this->resource;
	}

	/**
	 * Retrieve information about a file resource
	 *
	 * @return array
	 */
	public function stat()
	{
		throw new \Exception('Unimplemented');
		// TODO return $this->pathname->localAdapter()->getStat();
	}

	/**
	 * Advisory file locking
	 *
	 * @param mode $operation
	 *
	 * @return bool
	 */
	public function lock($operation)
	{
		return flock($this->resource, $operation);
	}

	/**
	 * Seeks to specific location in a stream
	 *
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return mixed
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		return 0 === fseek($this->resource, $offset, $whence);
	}

	/**
	 * Retrieve the current position of a stream
	 *
	 * @return int
	 */
	public function tell()
	{
		return ftell($this->resource);
	}

	/**
	 * Tests for end-of-file on a file pointer
	 *
	 * @return boolean
	 */
	public function eof()
	{
		return feof($this->resource);
	}

	/**
	 * Truncate stream
	 *
	 * @param int $size
	 *
	 * @return boolean
	 */
	public function truncate($size = 0)
	{
		return ftruncate($this->resource, $size);
	}

	/**
	 * Read from stream
	 *
	 * @param int $count
	 *
	 * @return string
	 */
	public function read($count)
	{
		return fread($this->resource, $count);
	}

	/**
	 * Write to stream
	 *
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function write($data)
	{
		return fwrite($this->resource, $data);
	}

	/**
	 * Flushes the output
	 *
	 * @return boolean
	 */
	public function flush()
	{
		return fflush($this->resource);
	}
}
