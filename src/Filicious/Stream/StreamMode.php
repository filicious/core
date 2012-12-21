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
 * Streaming access mode.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class StreamMode
{
	/**
	 * The mode.
	 *
	 * @var string
	 */
	protected $mode;

	/**
	 * The mode keys, e.g. ['r', 'b', '+']
	 *
	 * @var array
	 */
	protected $keys;

	/**
	 * Read mode
	 *
	 * @var bool
	 */
	protected $read;

	/**
	 * Write mode
	 *
	 * @var bool
	 */
	protected $write;

	/**
	 * Append mode
	 *
	 * @var bool
	 */
	protected $append;

	/**
	 * Not override mode
	 *
	 * @var bool
	 */
	protected $createOnly;

	/**
	 * Write without truncate
	 *
	 * @var bool
	 */
	protected $noTruncateWrite;

	/**
	 * Binary mode
	 *
	 * @var bool
	 */
	protected $binary;

	public function __construct($mode)
	{
		$this->mode = $mode;
		$this->keys = str_split($mode);

		$this->read       = in_array('r', $this->keys) || in_array('+', $this->keys);
		$this->write      = in_array('w', $this->keys) || in_array('+', $this->keys);
		$this->append     = in_array('a', $this->keys);
		$this->createOnly = in_array('x', $this->keys);
		$this->noTruncateWrite = in_array('c', $this->keys);
		$this->binary     = in_array('b', $this->keys);
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * Get the mode keys, e.g. ['r', 'b', '+']
	 *
	 * @return array
	 */
	public function getKeys()
	{
		return $this->keys;
	}

	/**
	 * Test if read mode is enabled
	 *
	 * @return bool
	 */
	public function isRead()
	{
		return $this->read;
	}

	/**
	 * Test if write mode is enabled
	 *
	 * @return bool
	 */
	public function isWrite()
	{
		return $this->write;
	}

	/**
	 * Test if append mode is enabled
	 *
	 * @return bool
	 */
	public function isAppend()
	{
		return $this->append;
	}

	/**
	 * Test if not override mode is enabled
	 *
	 * @return bool
	 */
	public function isCreateOnly()
	{
		return $this->createOnly;
	}

	/**
	 * Test if write without truncate mode is enabled
	 *
	 * @return boolean
	 */
	public function isNoTruncateWrite()
	{
		return $this->noTruncateWrite;
	}

	/**
	 * Test if binary mode is enabled
	 *
	 * @return bool
	 */
	public function isBinary()
	{
		return $this->binary;
	}
}
