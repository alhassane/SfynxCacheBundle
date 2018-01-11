<?php
namespace Sfynx\CacheBundle\Manager\Client;

use Sfynx\ToolBundle\Util\PiFileManager;
use Sfynx\CacheBundle\Manager\Generalisation\ClientInterface;

/**
 * Completely untested and undocumented. Use at your own risk!
 *
 * Fixes appreciated!
 *
 * @uses       CacheInterface
 * @package    Cache
 * @subpackage Manager
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since      2012-02-03
 */
class FilecacheClient implements ClientInterface
{
    protected $path = null;
    protected $options = null;

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($this->isBuildSafe($key)) {
            $file = file_get_contents( $this->buildFilename($key));
            $file = unserialize( $file );
            if (!is_array($file)) {
                return false;
            } elseif ($file['key'] != $key) {
                return false;
            } elseif ($file['ttl'] ==  0) {
            	return unserialize( $file['value']);
            } elseif (time() - $file['ctime'] > $file['ttl']) {
                return false;
            }
            return unserialize($file['value']);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 3600)
    {
        $file = [];
        $file['key'] = $key;
        $file['value'] = serialize($value);
        $file['ttl'] = $ttl;
        $file['ctime'] = time();

        if ($this->isSafe($key)) {
            return file_put_contents($this->buildFilename($key), serialize($file));
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function fresh($key, $value = null)
    {
        if ($this->isBuildSafe($key)) {
        	$file = file_get_contents($this->buildFilename($key));
        	$file = unserialize($file);
        	if (!is_array($file)) {
        	    return false;
            } elseif ($file['key'] != $key) {
            	return false;
            }
            $file['value'] = serialize($value);

            return file_put_contents($this->buildFilename($key), serialize($file));
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSafe($key = null)
    {
        if (empty($key) || is_null($this->path)) {
            return false;
        }
        return is_dir($this->path) && is_writable($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key)
    {
        if ($this->isBuildSafe($key)) {
            unlink($this->buildFilename($key));
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        PiFileManager::mkdirr($path);
        if (!empty($path) && is_dir($path) && is_writable($path)) {
            $this->path = $path;
        }
        return $this;
    }

    public function isFull()
    {
        //Check if the cache has exceeded its alotted size
    }

    protected function isBuildSafe($key = null)
    {
        if (!$this->isSafe($key) || !file_exists( $this->buildFilename($key))) {
            return false;
        }
        return true;
    }

    protected function buildFilename($key)
    {
        return $this->path . sha1($key) . '_file.cache';
        return $this;
    }
}
