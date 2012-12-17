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

spl_autoload_register(function($class) {
    $path = __DIR__ . '/../src/' . implode('/', explode('\\', $class)) . '.php';
    if (file_exists($path)) {
        include($path);
    }
});

