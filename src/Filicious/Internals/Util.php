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

namespace Filicious\Internals;

use Filicious\File;
use Filicious\Stream\StreamMode;
use Filicious\Internals\Pathname;
use \Exception;

/**
 * Utility class
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class Util
{
	/**
	 * Normalizes the given path
	 *
	 * @author  Antoine Hérault <antoine.herault@gmail.com>
	 * @see     Gaufrette\Util\Path
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	static public function normalizePath($path)
	{
		$path = str_replace('\\', '/', strval($path));
		preg_match('@^((?>[a-zA-Z]:)?/)?@', $path, $match);
		$abs = $match[1];
		$abs && $path = substr($path, strlen($abs));
		$path = preg_replace('@^[/\s]+|[/\s]+$@', '', $path);
		$path = preg_replace('@/+@', '/', $path);
		$parts = array();

		foreach (explode('/', $path) as $part) {
			if($part === '.' || $part === '..' && array_pop($parts) || $part == $abs) {
				continue;
			}
			$parts[] = $part;
		}

		return $abs . implode('/', $parts);
	}

	/**
	 * Indicates whether the given path is absolute or not
	 *
	 * @author  Antoine Hérault <antoine.herault@gmail.com>
	 * @see     Gaufrette\Util\Path
	 *
	 * @param  string $path A normalized path
	 *
	 * @return boolean
	 */
	static public function isAbsolute($path)
	{
		return '' !== static::getAbsolutePrefix($path);
	}

	/**
	 * Returns the absolute prefix of the given path
	 *
	 * @author  Antoine Hérault <antoine.herault@gmail.com>
	 * @see     Gaufrette\Util\Path
	 *
	 * @param  string $path A normalized path
	 *
	 * @return string
	 */
	static public function getAbsolutePrefix($path)
	{
		preg_match('|^(?P<prefix>([a-zA-Z]:)?/)|', $path, $matches);

		if (empty($matches['prefix'])) {
			return '';
		}

		return strtolower($matches['prefix']);
	}

	/**
	 * Strip a glob pattern from path.
	 *
	 * @param $pattern
	 * @param $path
	 */
	static public function stripPattern($pattern, $path)
	{
		$regexp = static::compilePatternToRegexp($pattern);

		var_dump('regexp: ' . $regexp);

		return preg_replace($regexp, '', $path);
	}

	/**
	 * @author  Antoine Hérault <antoine.herault@gmail.com>
	 * @see     Gaufrette\Glob::compile
	 *
	 * @param $pattern
	 */
	static public function compilePatternToRegexp($pattern, $strictLeadingDot = true, $strictWildcartSlash = true)
	{
		$firstByte   = true;
		$escaping    = false;
		$inCurlies   = 0;
		$patternSize = strlen($pattern);
		$regex       = '';

		for ($i = 0; $i < $patternSize; $i++) {
			$car = $pattern[$i];
			if ($firstByte) {
				if ($strictLeadingDot && '.' !== $car) {
					$regex .= '(?=[^\.])';
				}

				$firstByte = false;
			}

			switch ($car) {
				case '/':
					$firstByte = true;
				case '.':
				case '(':
				case ')':
				case '|':
				case '+':
				case '^':
				case '$':
					$regex .= '\\' . $car;
					break;
				case '[':
				case ']':
					$regex .= $escaping
						? '\\' . $car
						: $car;
					break;
				case '*':
					$regex .= $escaping
						? '\\*'
						: $strictWildcartSlash
							? '[^/]*'
							: '.*';
					break;
				case '?':
					$regex .= $escaping
						? '\\?'
						: $strictWildcartSlash
							? '[^/]'
							: '.';
					break;
				case '{':
					$regex .= !$escaping && ++$inCurlies
						? '('
						: '\\{';
					break;
				case '}':
					$regex .= !$escaping && $inCurlies && $inCurlies--
						? ')'
						: '}';
					break;
				case ',':
					$regex .= !$escaping && $inCurlies
						? '|'
						: ',';
					break;
				case '\\':
					$regex .= $escaping
						? '\\\\'
						: '';
					$escaping = !$escaping;
					continue;
				default:
					$regex .= $car;
			}

			$escaping = false;
		}

		return '#^(' . $regex . ')#';
	}

	static public function streamCopy(File $source, File $target)
	{
		$sourceStream = $source->getStream();
		$sourceStream->open(new StreamMode('rb'));
		$targetStream = $target->getStream();
		$targetStream->open(new StreamMode('wb'));

		return (bool) stream_copy_to_stream(
			$sourceStream->getRessource(),
			$targetStream->getRessource()
		);
	}

	static public function string2bitMode($string)
	{
		if (strlen($string) == 3) {
			return ($string[0] ? 4 : 0) | ($string[1] ? 2 : 0) | ($string[2] ? 1 : 0);
		}
		else if (strlen($string) == 9) {
			return '0' .
				static::string2bitMode(substr($string, 0, 3)) .
				static::string2bitMode(substr($string, 3, 3)) .
				static::string2bitMode(substr($string, 6, 3));
		}
		else if (strlen($string) == 10) {
			return static::string2bitMode(substr($string, 1));
		}
		return null;
	}

	static public function applyFilters(array $files, $bitmask, array $globs, array $callables)
	{
		/** @var File $file */
		foreach ($files as $index => $file) {
			if (!static::applyBitmaskFilters($file, $bitmask) ||
				!static::applyGlobFilters($file, $globs) ||
				!static::applyCallablesFilters($file, $callables)
			) {
				unset($files[$index]);
			}
		}

		return array_values($files);
	}

	static public function applyBitmaskFilters(File $file, $bitmask)
	{
		$basename = $file->getBasename();

		if (!($bitmask & File::LIST_ALL) &&
			($basename == '.' || $basename == '..') ||
			!($bitmask & File::LIST_HIDDEN) &&
				$basename[0] == '.' ||
			!($bitmask & File::LIST_VISIBLE) &&
				$basename[0] != '.' ||
			!($bitmask & File::LIST_FILES) &&
				$file->isFile() ||
			!($bitmask & File::LIST_DIRECTORIES) &&
				$file->isDirectory() ||
			!($bitmask & File::LIST_LINKS) &&
				$file->isLink() ||
			!($bitmask & File::LIST_OPAQUE) &&
				!$file->isLink()
		) {
			return false;
		}

		return true;
	}

	static public function applyGlobFilters(File $file, array $globs)
	{
		foreach ($globs as $glob) {
			if (!fnmatch($glob, $file->getPathname())) {
				return false;
			}
		}

		return true;
	}

	static public function applyCallablesFilters(File $file, array $callables)
	{
		foreach ($callables as $callable) {
			if (!$callable($file->getPathname(), $file)) {
				return false;
			}
		}

		return true;
	}

	static public function buildFilters(
		Pathname $parent,
		array $args,
		&$recursive = false,
		&$bitmask = null,
		array &$globs = array(),
		array &$callables = array(),
		array &$globSearchPatterns = array(),
		$deep = false
	) {
		// search for File::LIST_RECURSIVE
		foreach ($args as $arg) {
			if (is_int($arg)) {
				if ($arg & File::LIST_RECURSIVE) {
					$recursive = true;
				}
				if ($bitmask == null) {
					$bitmask = $arg;
				}
				else {
					$bitmask |= $arg;
				}
			}
			else if (is_string($arg)) {
				$globs[] = Util::normalizePath($arg);
			}
			else if (is_callable($arg)) {
				$callables[] = $arg;
			}
			else if (is_array($arg)) {
				static::buildFilters(
					$parent,
					$arg,
					$recursive,
					$bitmask,
					$globs,
					$callables,
					$globSearchPatterns,
					true
				);
			}
			else {
				if (is_object($arg)) {
					$type = get_class($arg);
				}
				else {
					ob_start();
					var_dump($arg);
					$type = ob_get_contents();
					ob_end_clean();
				}

				throw new Exception(
					sprintf(
						'Can not use %s as list filter.',
						$type
					)
				);
			}
		}

		if (!$deep) {
			if ($bitmask === null) {
				$bitmask = File::LIST_HIDDEN
					| File::LIST_VISIBLE
					| File::LIST_FILES
					| File::LIST_DIRECTORIES
					| File::LIST_LINKS
					| File::LIST_OPAQUE;
			}
			foreach ($globs as $index => $glob) {
				$parts = explode('/', $glob);

				if (count($parts) > 1) {
					$max  = count($parts) - 2;
					$path = '';
					for ($i = 0; $i < $max; $i++) {
						$path .= ($path ? '/' : '') . $parts[$i];

						$globSearchPatterns[] = static::normalizePath('*/' . $parent->full() . '/' . $path);
					}
				}

				$globs[$index] = static::normalizePath('*/' . $parent->full() . '/' . $glob);
			}
		}

		return array(
			$recursive,
			$bitmask,
			$globs,
			$callables,
			$globSearchPatterns
		);
	}
	
	/**
	 * @var resource
	 */
	protected static $finfo = null;
	
	/**
	 * Get the FileInfo resource identifier.
	 *
	 * @return resource
	 */
	public static function getFileInfo()
	{
		if (static::$finfo === null) {
			static::$finfo = finfo_open();
		}
	
		return static::$finfo;
	}
	
}