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

use Filicious\Exception\AdapterException;
use Filicious\File;
use Filicious\Stream\StreamMode;

/**
 * Utility class
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
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

		if (empty($match[1])) {
			$abs = '';
		}
		else {
			$abs  = $match[1];
			$path = substr($path, strlen($abs));
		}
		$path  = preg_replace('@^[/\s]+|[/\s]+$@', '', $path);
		$path  = preg_replace('@/+@', '/', $path);
		$parts = array();

		foreach (explode('/', $path) as $part) {
			if ($part === '.' || $part === '..' && array_pop($parts) || $part == $abs) {
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

	static public function hasBit($haystack, $bit)
	{
		if (static::isTraversable($haystack)) {
			foreach ($haystack as $temp) {
				if (static::hasBit($temp, $bit)) {
					return true;
				}
			}
		}
		else if (is_int($haystack) && $haystack & $bit) {
			return true;
		}

		return false;
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

	public static function getPathnameParts($path)
	{
		$path = strval($path);
		if (!strlen($path)) {
			return array();
		}
		$path  = str_replace('\\', '/', $path);
		$path  = preg_replace('@^(?>[a-zA-Z]:)?[/\s]+|[/\s]+$@', '', $path); // TODO how to handle win pathnames?
		$parts = array();

		foreach (explode('/', $path) as $part) {
			if ($part === '..') {
				array_pop($parts);
			}
			elseif ($part !== '.' && strlen($part)) {
				$parts[] = $part;
			}
		}

		return $parts;
	}

	/**
	 * Dirname function that only split on "/", required because we use UNIX path names all the time, even on windows!
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function dirname($path)
	{
		$index = strrpos($path, '/');

		if ($index > 0) {
			return substr($path, 0, $index);
		}

		return '/';
	}

	/**
	 * Determine if the variable is traversable.
	 *
	 * @param mixed $var
	 */
	public static function isTraversable($var)
	{
		return is_array($var) || is_object($var) && $var instanceof \Traversable;
	}

	/**
	 * Execute a php function, throw an AdapterException if the function failed.
	 *
	 * @param $callback
	 * @param $errorCode
	 * @param $errorMessage
	 *
	 * @return mixed
	 * @throws AdapterException
	 */
	public static function executeFunction($callback, $exceptionClass, $errorCode, $errorMessage)
	{
		$error = null;

		try {
			$result = $callback();
		}
		catch (\ErrorException $exception) {
			$result = false;
			$error  = $exception;
		}

		if ($error !== null || $result === false) {
			throw new $exceptionClass(
				vsprintf(
					$errorMessage,
					array_slice(
						func_get_args(),
						3
					)
				),
				$errorCode,
				$error
			);
		}

		return $result;
	}

	/**
	 * Create a date time object.
	 *
	 * @param \DateTime|int|string $time A \DateTime object, a timestamp or a time format string.
	 *
	 * @return \DateTime
	 */
	public static function createDateTime($time)
	{
		if ($time instanceof \DateTime) {
			return $time;
		}

		if (is_int($time) || is_float($time)) {
			$date = new \DateTime();
			$date->setTimestamp($time);
			return $date;
		}

		return new \DateTime($time);
	}

}