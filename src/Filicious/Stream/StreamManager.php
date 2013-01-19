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

use Filicious\Filesystem;
use Filicious\Exception\StreamWrapperAlreadyRegisteredException;
use Filicious\Exception\StreamWrapperNotRegisteredException;

/**
 * Stream wrapper manager and organizer.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
final class StreamManager
{
	/**
	 * Map of registered wrappers and filesystems.
	 *
	 * @var array
	 */
	protected static $filesystems = array();

	/**
	 * Index for autoregistered filesystems.
	 *
	 * @var int
	 */
	protected static $autoIndex = 0;

	/**
	 * Map of registered streams.
	 *
	 * @var array
	 */
	protected static $streams = array();

	/**
	 * Index for autoregistered streams.
	 *
	 * @var int
	 */
	protected static $streamIndex = 0;

	/**
	 * Automatically register a filesystem.
	 *
	 * @param \Filicious\Filesystem $filesystem
	 *
	 * @return array($host, $scheme)
	 */
	public static function autoregisterFilesystem(Filesystem $filesystem)
	{
		$scheme = 'filicious';
		$host   = 'automount:' . ++static::$autoIndex;
		static::registerFilesystem($filesystem, $host, $scheme);
		return array($host, $scheme);
	}

	/**
	 * Register a new filesystem wrapper.
	 *
	 * @param \Filicious\Filesystem $filesystem
	 * @param string                $host
	 * @param string                $scheme
	 *
	 * @throws StreamWrapperAlreadyRegisteredException
	 */
	public static function registerFilesystem(Filesystem $filesystem, $host, $scheme = null)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		$registeredWrappers = stream_get_wrappers();

		// protect existing build in and third party wrappers
		if (isset(static::$filesystems[$scheme]) && isset(static::$filesystems[$scheme][$host]) ||
			!isset(static::$filesystems[$scheme]) && in_array($scheme, $registeredWrappers)
		) {
			throw new StreamWrapperAlreadyRegisteredException(
				$scheme,
				$host
			);
		}

		// register a stream wrapper if not already done
		if (!isset(static::$filesystems[$scheme])) {
			static::$filesystems[$scheme] = array();
			stream_register_wrapper($scheme, 'Filicious\Stream\StreamWrapper', STREAM_IS_URL);
		}

		// register the filesystem in the map
		static::$filesystems[$scheme][$host] = $filesystem;
	}

	public static function unregisterFilesystem($host, $scheme, $silent = false)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		// throw exception, if no wrapper/filesystem is registered
		if (!isset(static::$filesystems[$scheme]) || !isset(static::$filesystems[$scheme][$host])) {
			if ($silent) {
				return;
			}

			throw new StreamWrapperNotRegisteredException(
				$scheme,
				$host
			);
		}

		// remove the filesystem from the map
		unset(static::$filesystems[$scheme][$host]);
	}

	public static function searchFilesystem($host, $scheme = null)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		// throw exception, if no wrapper/filesystem is registered
		if (!isset(static::$filesystems[$scheme]) || !isset(static::$filesystems[$scheme][$host])) {
			throw new StreamWrapperAlreadyRegisteredException(
				$scheme,
				$host
			);
		}

		return static::$filesystems[$scheme][$host];
	}

	/**
	 * Register a stream.
	 *
	 * @return int
	 */
	public static function registerStream(Stream $stream)
	{
		if (empty(static::$streams)) {
			stream_register_wrapper('filicious-streams', 'Filicious\Stream\StreamWrapper', STREAM_IS_URL);
		}
		static::$streams[++static::$streamIndex] = $stream;
		return static::$streamIndex;
	}

	/**
	 * Unregister a stream.
	 */
	public static function unregisterStream(Stream $stream)
	{
		foreach (static::$streams as $index => $registeredStream) {
			if ($registeredStream == $stream) {
				unset(static::$streams[$index]);
			}
		}
	}

	public static function searchStream($index)
	{
		return static::$streams[$index];
	}

	/**
	 * Release unused stream wrappers.
	 */
	public static function free()
	{
		foreach (static::$filesystems as $scheme => $map) {
			// if no filesystem is mapped to the scheme, remove the wrapper
			if (empty($map)) {
				stream_wrapper_unregister($scheme);
				unset(static::$filesystems[$scheme]);
			}
		}
	}

	private function __construct()
	{
	}
}
