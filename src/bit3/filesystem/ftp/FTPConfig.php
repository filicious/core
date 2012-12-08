<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\ftp;

use bit3\filesystem\cache\Cache;
use bit3\filesystem\cache\ArrayCache;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPConfig
{
    /**
     * Connection host.
     *
     * @var string
     */
    protected $host;

    /**
     * Connection port.
     *
     * @var string
     */
    protected $port = 21;

    /**
     * Connection timeout.
     *
     * @var string
     */
    protected $timeout = 90;

    /**
     * Use passive mode.
     *
     * @var bool
     */
    protected $passiveMode = true;

    /**
     * Use SSL Connection.
     *
     * @var bool
     */
    protected $ssl = false;

    /**
     * Username to use for login.
     *
     * @var string
     */
    protected $username = 'anonymous';

    /**
     * Password to use for login.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Relative path on the server.
     *
     * @var string
     */
    protected $path = '';

    /**
     * Lazy connect to ftp server.
     *
     * @var bool
     */
    protected $lazyConnect = true;

    /**
     * Show the password in public urls.
     *
     * @var bool
     */
    protected $visiblePassword = false;

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct($host)
    {
        $this->host = (string) $host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = (string) $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
    }

    /**
     * @return string
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param bool $passiveMode
     */
    public function setPassiveMode($passiveMode)
    {
        $this->passiveMode = (bool) $passiveMode;
    }

    /**
     * @return bool
     */
    public function getPassiveMode()
    {
        return $this->passiveMode;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl($ssl)
    {
        $this->ssl = (bool) $ssl;
    }

    /**
     * @return bool
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param boolean $lazyConnect
     */
    public function setLazyConnect($lazyConnect)
    {
        $this->lazyConnect = (bool) $lazyConnect;
    }

    /**
     * @return boolean
     */
    public function getLazyConnect()
    {
        return $this->lazyConnect;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = (string) $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param \bit3\filesystem\cache\Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \bit3\filesystem\cache\Cache
     */
    public function getCache()
    {
        if ($this->cache === null) {
            $this->cache = new ArrayCache();
        }
        return $this->cache;
    }

    /**
     * @param boolean $visiblePassword
     */
    public function setVisiblePassword($visiblePassword)
    {
        $this->visiblePassword = (bool) $visiblePassword;
    }

    /**
     * @return boolean
     */
    public function getVisiblePassword()
    {
        return $this->visiblePassword;
    }
}
