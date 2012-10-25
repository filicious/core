<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

spl_autoload_register(function($class) {
    $path = __DIR__ . '/../src/' . implode('/', explode('\\', $class)) . '.php';
    if (file_exists($path)) {
        include($path);
    }
});
