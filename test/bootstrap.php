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

spl_autoload_register(
	function ($class) {
		$classPath = implode('/', explode('\\', $class)) . '.php';
		$path      = __DIR__ . '/../src/' . $classPath;
		if (file_exists($path)) {
			include($path);
		}
		$path = __DIR__ . '/../test/' . $classPath;
		if (file_exists($path)) {
			include($path);
		}
	}
);

set_error_handler(
	function ($errno, $errstr, $errfile = null, $errline = -1, array $errcontext = array()) {
		throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	},
	E_RECOVERABLE_ERROR | E_WARNING | E_USER_ERROR | E_USER_WARNING
);