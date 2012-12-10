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

use Bit3\Filesystem\AbstractFilesystem;
use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\PublicURLProvider;
use Bit3\Filesystem\Util;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFilesystem
	extends AbstractFilesystem
{
	const CONFIG_CLASS = 'Bit3\Filesystem\FTP\FTPFilesystemConfig';

    /**
     * @var resource
     */
    protected $connection;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @param FTPFilesystemConfig $config
     */
    public function __construct(FTPFilesystemConfig $config, PublicURLProvider $provider = null)
    {
    	parent::__construct($config, $provider);

    	// TODO OH: since the cache comes from the config, this base key should
    	// be generated there, too
        $this->cacheKey = 'ftpfs:' . ($this->config->getSSL() ? 'ssl:' : '') . $this->config->getUsername() . '@' . $this->config->getHost() . ':' . $this->config->getPort() . ($this->config->getBasePath() ?: '/');

        if (!$this->config->getLazyConnect())
        {
            $this->getConnection();
        }
    }

    public function __destruct()
    {
        if ($this->connection)
        {
            ftp_close($this->connection);
        }
    }

    /**
     * Get the root (/) file node.
     *
     * @return File
     */
    public function getRoot()
    {
        return new FTPFile('/', $this);
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
        return new FTPFile($path, $this);
    }

    /**
     * Returns available space on filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function getFreeSpace(File $path = null)
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
    public function getTotalSpace(File $path = null)
    {
        return -1;
    }

    /**
     *
     */
    public function connect()
    {
        if ($this->connection !== null) {
            return;
        }

        if ($this->config->getSSL()) {
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
            throw new FTPFilesystemConnectionException('Could not connect to ' . $this->config->getHost());
        }

        if ($this->config->getUsername()) {
            if (!ftp_login($this->connection,
                      $this->config->getUsername(),
                      $this->config->getPassword())) {
                throw new FTPFilesystemAuthenticationException('Could not login to ' . $this->config->getHost() . ' with username ' . $this->config->getUsername() . ':' . ($this->config->getPassword() ? '*****' : 'NOPASS'));
            }
        }

        ftp_pasv($this->connection, $this->config->getPassiveMode());

        if ($this->config->getBasePath()) {
            if (!ftp_chdir($this->connection, $this->config->getBasePath())) {
                throw new FTPFilesystemException('Could not change into directory ' . $this->config->getBasePath() . ' on ' . $this->config->getHost());
            }
        }
    }

    public function getConnection()
    {
        if (!$this->connection)
        {
            $this->connect();
        }
        return $this->connection;
    }

    public function ftpStat(FTPFile $file)
    {
        $real = $this->config->getBasePath() . $file->getPathname();
        $cacheKey = $this->cacheKey . ':stat:' . $real;

        $cached = $this->config->getCache()->fetch($cacheKey);

        if ($cached === null) {
            $this->ftpList($file);

            $cached = $this->config->getCache()->fetch($cacheKey);
        }

        return $cached;
    }

    public function ftpList(FTPFile $file)
    {
        $real = $this->config->getBasePath() . $file->getPathname();
        $cacheKey = $this->cacheKey . ':list:' . $real;

        $cached = $this->config->getCache()->fetch($cacheKey);

        if ($cached === null) {
            $cached = array();
            $list = ftp_rawlist($this->getConnection(), '-la ' . $real);

            $isSingleFile = true;

            foreach ($list as $item) {
                if (preg_match('#^([\-ldrwxsSt]{10})\s+(\d+)\s+([\w\d]+)\s+([\w\d]+)\s+(\d+)\s+(\w{3}\s+\d{1,2}\s+(?:\d{2}:\d{2}|\d{4}))\s+(.*?)(\s+->\s+(.*))?$#s', $item, $match)) {
                    $stat = (object) array(
                        'perms'       => $match[1],
                        'mode'        => Util::string2bitMode($match[1]),
                        'type'        => (int) $match[2],
                        'isDirectory' => $match[1][0] == 'd',
                        'isFile'      => $match[1][0] != 'd',
                        'isLink'      => $match[1][0] == 'l',
                        'user'        => (int) $match[3],
                        'group'       => (int) $match[4],
                        'size'        => (int) $match[5],
                        'modified'    => strtotime($match[6]),
                        'name'        => $match[7],
                        'target'      => isset($match[9]) ? $match[9] : null
                    );

                    if ($stat->name == '.') {
                        $isSingleFile = false;
                        $directoryCacheKey = $this->cacheKey . ':stat:' . $real;
                        $this->config->getCache()->store($directoryCacheKey, $stat);
                    }
                    else if ($stat->name == '..') {
                        if (dirname($real) != $real) {
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
                    throw new FTPFilesystemException('Implementation error: Could not parse list item ' . $item);
                }
            }

            if ($isSingleFile) {
                $cached = false;
            }

            $this->config->getCache()->store($cacheKey, $cached);
        }

        return $cached;
    }

    public function ftpChmod(FTPFile $file, $mode)
    {
        $stat = $this->ftpStat($file);

        if ($stat) {
            $real = $this->config->getBasePath() . $file->getPathname();
            return ftp_chmod($this->getConnection(), $mode, $real);
        }

        return false;
    }

    public function ftpDelete(FTPFile $file)
    {
        $stat = $this->ftpStat($file);

        if ($stat) {
            $real = $this->config->getBasePath() . $file->getPathname();

            if ($stat->isDirectory) {
                if (ftp_rmdir($this->getConnection(), $real)) {
                    $this->config->getCache()->store($this->cacheKey . ':stat:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . dirname($real), null);
                    return true;
                }
            }
            else {
                if (ftp_delete($this->getConnection(), $real)) {
                    $this->config->getCache()->store($this->cacheKey . ':stat:' . $real, null);
                    $this->config->getCache()->store($this->cacheKey . ':list:' . dirname($real), null);
                    return true;
                }
            }
        }

        return false;
    }

    public function ftpStreamGet(FTPFile $source, $targetStream)
    {
        $stat = $this->ftpStat($source);

        if ($stat and !$stat->isDirectory) {
            $real = $this->config->getBasePath() . $source->getPathname();

            return ftp_fget($this->getConnection(), $targetStream, $real, FTP_BINARY);
        }

        return false;
    }

    public function ftpStreamPut(FTPFile $target, $sourceStream)
    {
        $stat = $this->ftpStat($target);

        if (!$stat or !$stat->isDirectory) {
            $real = $this->config->getBasePath() . $target->getPathname();

            return ftp_fput($this->getConnection(), $real, $sourceStream, FTP_BINARY);
        }

        return false;
    }

    public function ftpGet(FTPFile $source, File $target)
    {
        $stat = $this->ftpStat($source);

        if ($stat and !$stat->isDirectory) {
            $realSource = $this->config->getBasePath() . $source->getPathname();
            $realTarget = $target->getRealURL();

            return ftp_get($this->getConnection(), $realTarget, $realSource, FTP_BINARY);
        }

        return false;
    }

    public function ftpPut(FTPFile $target, File $source)
    {
        $stat = $this->ftpStat($target);

        if (!$stat or !$stat->isDirectory) {
            $realSource = $source->getRealURL();
            $realTarget = $this->config->getBasePath() . $target->getPathname();

            return ftp_put($this->getConnection(), $realTarget, $realSource, FTP_BINARY);
        }

        return false;
    }

    public function ftpMkdir(FTPFile $file)
    {
        $stat = $this->ftpStat($file);

        if (!$stat) {
            $real = $this->config->getBasePath() . $file->getPathname();

            return ftp_mkdir($this->getConnection(), $real);
        }

        return false;
    }

    public function ftpRename(FTPFile $source, FTPFile $target)
    {
        $sourceStat = $this->ftpStat($source);
        $targetStat = $this->ftpStat($target);

        if ($sourceStat and (!$targetStat or (!$sourceStat['isDirectory'] and !$targetStat['isDirectory']))) {
            $realSource = $this->config->getBasePath() . $source->getPathname();
            $realTarget = $this->config->getBasePath() . $target->getPathname();

            return ftp_rename($this->getConnection(), $realSource, $realTarget);
        }

        return false;
    }

    public function ftpRmdir(FTPFile $file)
    {
        $stat = $this->ftpStat($file);

        if ($stat && $stat->isDirectory) {
            $real = $this->config->getBasePath() . $file->getPathname();

            return ftp_rmdir($this->getConnection(), $real);
        }

        return false;
    }
}
