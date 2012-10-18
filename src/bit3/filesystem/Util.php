<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem;

/**
 * Utility class
 *
 * @package php-filesystem
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
        $path   = str_replace('\\', '/', $path);
        $prefix = static::getAbsolutePrefix($path);
        $path   = substr($path, strlen($prefix));
        $parts  = array_filter(explode('/', $path), 'strlen');
        $tokens = array();

        foreach ($parts as $part) {
            switch ($part) {
            case '.':
                continue;
            case '..':
                if (0 !== count($tokens)) {
                    array_pop($tokens);
                    continue;
                }
                else if (!empty($prefix)) {
                    continue;
                }
            default:
                $tokens[] = $part;
            }
        }

        return $prefix . implode('/', $tokens);
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
        $sourceStream = $source->openStream('r');
        $targetStream = $target->openStream('w');

        return (bool) stream_copy_to_stream($sourceStream, $targetStream);
    }
}