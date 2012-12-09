<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\FTP;

use Bit3\Filesystem\Cache\Cache;
use Bit3\Filesystem\Cache\ArrayCache;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFilesystemConfig
	extends FilesystemConfig
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
    	parent::__construct();
        $this->setHost($host);
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
		$this->checkImmutable();
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
		$this->checkImmutable();
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
		$this->checkImmutable();
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
		$this->checkImmutable();
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
    public function setSSL($ssl)
    {
		$this->checkImmutable();
        $this->ssl = (bool) $ssl;
    }

    /**
     * @return bool
     */
    public function getSSL()
    {
        return $this->ssl;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
		$this->checkImmutable();
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
		$this->checkImmutable();
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
		$this->checkImmutable();
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
     * @param \Bit3\Filesystem\Cache\Cache $cache
     */
    public function setCache(Cache $cache)
    {
		$this->checkImmutable();
        $this->cache = $cache;
    }

    /**
     * @return \Bit3\Filesystem\Cache\Cache
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
		$this->checkImmutable();
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
