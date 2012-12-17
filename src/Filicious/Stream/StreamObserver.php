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
 * A file stream object.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class StreamObserver
{
	/**
	 * Opens file or URL
	 */
	public function opened(StreamMode $mode)
	{
	}

	/**
	 * Close an resource
	 */
	public function closed()
	{
	}

	/**
	 * File is locked
	 *
	 * @param mode $operation
	 *
	 * @return bool
	 */
	public function locked($operation)
	{
	}

	/**
	 * Seeks to specific location in a stream
	 *
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return mixed
	 */
	public function positionChanged($offset, $whence = SEEK_SET)
	{
	}

	/**
	 * Truncate stream
	 *
	 * @param int $size
	 *
	 * @return boolean
	 */
	public function truncated($size = 0)
	{
	}

	/**
	 * Read from stream
	 *
	 * @param int $count
	 *
	 * @return string
	 */
	public function read($count, $data)
	{
	}

	/**
	 * Write to stream
	 *
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function written($data)
	{
	}

	/**
	 * Flushes the output
	 *
	 * @return boolean
	 */
	public function flushed()
	{
	}
}
