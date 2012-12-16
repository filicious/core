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
	protected static $map = array();

	/**
	 * Index for autoregistered filesystems.
	 *
	 * @var int
	 */
	protected static $autoIndex = 0;

	/**
	 * Automatically register a filesystem.
	 *
	 * @param \Filicious\Filesystem $filesystem
	 *
	 * @return array($host, $scheme)
	 */
	public static function autoregister(Filesystem $filesystem)
	{
		$scheme = 'filicious';
		$host   = 'automount:' . ++static::$autoIndex;
		static::register($filesystem, $host, $scheme);
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
	public static function register(Filesystem $filesystem, $host, $scheme = null)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		$registeredWrappers = stream_get_wrappers();

		// protect existing build in and third party wrappers
		if (isset(static::$map[$scheme]) && isset(static::$map[$scheme][$host]) ||
			!isset(static::$map[$scheme]) && in_array($scheme, $registeredWrappers)
		) {
			throw new StreamWrapperAlreadyRegisteredException(
				$scheme,
				$host
			);
		}

		// register a stream wrapper if not already done
		if (!isset(static::$map[$scheme])) {
			static::$map[$scheme] = array();
			stream_register_wrapper($scheme, 'Filicious\Stream\StreamWrapper', STREAM_IS_URL);
		}

		// register the filesystem in the map
		static::$map[$scheme][$host] = $filesystem;
	}

	public static function unregister($host, $scheme)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		// throw exception, if no wrapper/filesystem is registered
		if (!isset(static::$map[$scheme]) || !isset(static::$map[$scheme][$host])) {
			throw new StreamWrapperAlreadyRegisteredException(
				$scheme,
				$host
			);
		}

		// remove the filesystem from the map
		unset(static::$map[$scheme][$host]);

		// if no filesystem is mapped to the scheme, remove the wrapper
		if (empty(static::$map[$scheme])) {
			stream_wrapper_unregister($scheme);
		}
	}

	public static function searchFilesystem($host, $scheme = null)
	{
		if (!$scheme) {
			$scheme = 'filicious';
		}

		// throw exception, if no wrapper/filesystem is registered
		if (!isset(static::$map[$scheme]) || !isset(static::$map[$scheme][$host])) {
			throw new StreamWrapperAlreadyRegisteredException(
				$scheme,
				$host
			);
		}

		return static::$map[$scheme][$host];
	}

	private function __construct()
	{
	}
}
