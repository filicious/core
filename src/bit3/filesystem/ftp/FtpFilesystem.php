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

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\BasicFileImpl;
use bit3\filesystem\FilesystemException;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FtpFilesystem
    implements Filesystem
{
    /**
     * @var FtpConfig
     */
    protected $config;

    /**
     * @var resource
     */
    protected $connection;

    /**
     * @var array
     */
    protected $cacheListing = array();

    /**
     * @param FtpConfig $config
     */
    public function __construct(FtpConfig $config)
    {
        $this->config = clone $config;

        if ($this->config->getSsl()) {
            $this->connection = ftp_ssl_connect(
                $this->config->getHost(),
                $this->config->getPort(),
                $this->config->getTimeout());
        }
        else {
            $this->connection = ftp_connect(
                $this->config->getHost(),
                $this->config->getPort(),
                $this->config->getTimeout());
        }

        if ($this->connection === false) {
            throw new FtpFilesystemConnectionException('Could not connect to ' . $this->config->getHost());
        }

        if ($this->config->getUsername()) {
            if (!ftp_login($this->connection,
                      $this->config->getUsername(),
                      $this->config->getPassword())) {
                throw new FtpFilesystemAuthenticationException('Could not login to ' . $this->config->getHost() . ' with username ' . $this->config->getUsername() . ':' . ($this->config->getPassword() ? '*****' : 'NOPASS'));
            }
        }

        ftp_pasv($this->connection, $this->config->getPassiveMode());

        if ($this->config->getPath()) {
            if (!ftp_chdir($this->connection, $this->config->getPath())) {
                throw new FtpFilesystemException('Could not change into directory ' . $this->config->getPath() . ' on ' . $this->config->getHost());
            }
        }
    }

    public function __destruct()
    {
        ftp_close($this->connection);
    }

    /**
     * Get the root (/) file node.
     *
     * @return File
     */
    public function getRoot()
    {
        return new FtpFile('/', $this);
    }

    /**
     * Get a file object for the specific file.
     *
     * @param string $path
     *
     * @return File
     */
    public function getFile($path)
    {
        return new FtpFile($path, $this);
    }

    /**
     * Returns available space on filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function diskFreeSpace(File $path = null)
    {
        return -1;
    }

    /**
     * Returns the total size of a filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function diskTotalSpace(File $path = null)
    {
        return -1;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getBasePath()
    {
        return $this->config->getPath();
    }

    public function ftpList(FtpFile $file)
    {
        $real = $this->getBasePath() . $file->getPathname();

        if (!isset($this->cacheListing[$real])) {
            $list = ftp_rawlist($this->connection, '-lA ' . escapeshellarg($real));

            var_dump($list);
            exit;
        }

        return $this->cacheListing[$real];
    }
}
