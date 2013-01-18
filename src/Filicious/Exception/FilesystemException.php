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

namespace Filicious\Exception;


/**
 * Filesystem exception
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FilesystemException
	extends \Exception
{
	const FILESYSTEM_EXCEPTION = 1;

	const OPERATION_EXCEPTION = 2;

	const FILE_NOT_FOUND = 3;

	const NOT_A_DIRECTORY = 4;

	const NOT_A_FILE = 5;

	function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		if ($code === 0) {
			$code = static::FILESYSTEM_EXCEPTION;
		}
		parent::__construct($message, $code, $previous);
	}
}