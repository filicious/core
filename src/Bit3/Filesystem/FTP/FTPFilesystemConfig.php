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
use Bit3\Filesystem\AbstractFilesystemConfig;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFilesystemConfig
	extends AbstractFilesystemConfig
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
		$this->checkImmutable()->host = (string) $host;
        return $this;
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
		$this->checkImmutable()->port = (int) $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param numeric $timeout
     */
    public function setTimeout($timeout)
    {
		$this->checkImmutable()->timeout = (float) $timeout;
        return $this;
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
		$this->checkImmutable()->passiveMode = (bool) $passiveMode;
        return $this;
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
		$this->checkImmutable()->ssl = (bool) $ssl;
        return $this;
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
		$this->checkImmutable()->username = (string) $username;
        return $this;
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
		$this->checkImmutable()->password = (string) $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * determines if an ftp connection shall be lazy connecting or not.
     * lazy hereby means, the connection will only established, when the first access to
     * the filesystem has been made, this may be read, write or list access.
	 *
     * @param boolean $lazyConnect
     */
    public function setLazyConnect($lazyConnect)
    {
		$this->checkImmutable()->lazyConnect = (bool) $lazyConnect;
        return $this;
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
		$this->checkImmutable()->cache = $cache;
        return $this;
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
		$this->checkImmutable()->visiblePassword = (bool) $visiblePassword;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getVisiblePassword()
    {
        return $this->visiblePassword;
    }
    
    public function getProtocol() {
    	return $this->getSSL() ? 'ftps' : 'ftp';
    }
    
    public function getTimeoutSeconds() {
    	return floor($this->timeout);
    }
    
    public function getTimeoutMilliseconds() {
    	return floor(($this->timeout - $this->getTimeoutSeconds()) * 1000);
    }
    
    public function toURL($params = false, $pw = false) {
    	// protocol
	    $url = $config->getProtocol() . '://';
	    
	    // user
	    $url .= $config->getUsername();
	    if ($this->getPassword()) {
	    	if($pw || $this->getVisiblePassword()) {
	    		$url .= ':' . $config->getPassword();
	    	} else {
	    		$url .= ':***';
	    	}
	    }
	    $url .= '@';
	    
	    // host
	    $url .= $this->getHost() . ':' . $this->getPort();
	    $url .= $this->getBasePath();
	    
	    // additional config
	    if($params) {
	    	$params = array();
	    	$params['timeout']	= $this->getTimeout();
	    	$params['passive']	= $this->getPassiveMode();
	    	$params['lazy']		= $this->getLazyConnect();
	    	$params['pwVisible']= $this->getVisiblePassword();
	    	$url .= '#' . http_build_query($params);
	    }
	    
	    return $url;
    }
    
}
