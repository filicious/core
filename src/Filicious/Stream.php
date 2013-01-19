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

namespace Filicious;

use Filicious\Stream\StreamMode;

/**
 * A file stream object.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface Stream
{
	/**
	 * @return resource
	 */
	public function getResource();

	/**
	 * Opens file or URL
	 *
	 * @return bool
	 */
	public function open(StreamMode $mode);

	/**
	 * Close an resource
	 *
	 * @return bool
	 */
	public function close();

	/**
	 * Retrieve the underlaying resource
	 *
	 * @param int $as
	 *
	 * @return resource|boolean
	 */
	public function cast($as);

	/**
	 * Retrieve information about a file resource
	 *
	 * @return array
	 */
	public function stat();

	/**
	 * Advisory file locking
	 *
	 * @param mode $operation
	 *
	 * @return bool
	 */
	public function lock($operation);

	/**
	 * Seeks to specific location in a stream
	 *
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return mixed
	 */
	public function seek($offset, $whence = SEEK_SET);

	/**
	 * Retrieve the current position of a stream
	 *
	 * @return int
	 */
	public function tell();

	/**
	 * Tests for end-of-file on a file pointer
	 *
	 * @return boolean
	 */
	public function eof();

	/**
	 * Truncate stream
	 *
	 * @param int $size
	 *
	 * @return boolean
	 */
	public function truncate($size = 0);

	/**
	 * Read from stream
	 *
	 * @param int $count
	 *
	 * @return string
	 */
	public function read($count);

	/**
	 * Write to stream
	 *
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function write($data);

	/**
	 * Flushes the output
	 *
	 * @return boolean
	 */
	public function flush();
}
