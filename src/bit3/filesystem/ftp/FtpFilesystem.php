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
use bit3\filesystem\Util;

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
     * @var string
     */
    protected $cacheKey;

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

        $this->cacheKey = 'ftpfs:' . ($this->config->getSsl() ? 'ssl:' : '') . $this->config->getUsername() . '@' . $this->config->getHost() . ':' . $this->config->getPort() . ($this->config->getPath() ?: '/');
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

    /**
     * @return \bit3\filesystem\ftp\FtpConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getBasePath()
    {
        return $this->config->getPath();
    }

    public function ftpStat(FtpFile $file)
    {
        $real = $this->getBasePath() . $file->getPathname();
        $cacheKey = $this->cacheKey . ':stat:' . $real;

        $cached = $this->config->getCache()->fetch($cacheKey);

        if (!$cached) {
            $this->ftpList($file->getParent());

            $cached = $this->config->getCache()->fetch($cacheKey);
        }

        return $cached;
    }

    public function ftpList(FtpFile $file)
    {
        $real = $this->getBasePath() . $file->getPathname();
        $cacheKey = $this->cacheKey . ':list:' . $real;

        $cached = $this->config->getCache()->fetch($cacheKey);

        if ($cached !== null) {
            $cached = array();
            $list = ftp_nlist($this->connection, '-la ' . $real);

            $isSingleFile = true;

            foreach ($list as $item) {
                if (preg_match('#^([\-ldrwxsSt]{10})\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\w{3}\s+\d{2}\s+(?:\d{2}:\d{2}|\d{4}))\s+(.*)(\s+->\s+(.*))?$#s', $item, $match)) {
                    $stat = (object) array(
                        'perms'       => $match[1],
                        'mode'        => Util::string2bitMode($match[1]),
                        'type'        => (int) $match[2],
                        'isDirectory' => $match[2] == 2,
                        'isFile'      => $match[2] == 1,
                        'isLink'      => $match[2] == 1 && $match[1][0] == 'l',
                        'user'        => (int) $match[3],
                        'group'       => (int) $match[4],
                        'size'        => (int) $match[5],
                        'modified'    => strtotime($match[6]),
                        'name'        => $match[7],
                        'target'      => isset($match[9]) ? $match[9] : null
                    );

                    if ($match[2] == 100) {
                        $isSingleFile = false;

                        if ($match[7] == '.') {
                            $directoryCacheKey = $this->cacheKey . ':stat:' . $real;
                            $this->config->getCache()->store($directoryCacheKey, $stat);
                        }
                        else if ($match[7] == '..') {
                            $directoryCacheKey = $this->cacheKey . ':stat:' . dirname($real);
                            $this->config->getCache()->store($directoryCacheKey, $stat);
                        }
                    }
                    else {
                        $fileCacheKey = $this->cacheKey . ':stat:' . $real . ($isSingleFile ? '' : '/' . $match[7]);
                        $this->config->getCache()->store($fileCacheKey, $stat);
                        $cached[] = $stat;
                    }
                }
                else {
                    throw new FtpFilesystemException('Implementation error: Could not parse list item ' . $item);
                }
            }

            if ($isSingleFile) {
                $cached = false;
            }

            $this->config->getCache()->store($cacheKey, $cached);
        }

        return $cached;
    }

    public function ftpChmod(FtpFile $file, $mode)
    {
        $stat = $this->ftpStat($file);

        if ($stat) {
            $real = $this->getBasePath() . $file->getPathname();
            return ftp_chmod($this->connection, $mode, $real);
        }

        return false;
    }

    public function ftpDelete(FtpFile $file)
    {
        $stat = $this->ftpStat($file);

        if ($stat) {
            $real = $this->getBasePath() . $file->getPathname();

            if ($stat['isDirectory']) {
                if (ftp_rmdir($this->connection, $real)) {
                    $this->config->getCache()->store($this->cacheKey . ':stat:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . dirname($real), null);
                    return true;
                }
            }
            else {
                if (ftp_delete($this->connection, $real)) {
                    $this->config->getCache()->store($this->cacheKey . ':stat:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . dirname($real), null);
                    return true;
                }
            }
        }

        return false;
    }

    public function ftpGet(FtpFile $file)
    {

    }

    public function ftpPut(FtpFile $file, $content)
    {

    }

    public function ftpMkdir(FtpFile $file)
    {

    }

    public function ftpRename(FtpFile $source, FtpFile $target)
    {

    }

    public function ftpRmdir(FtpFile $file)
    {

    }
}
