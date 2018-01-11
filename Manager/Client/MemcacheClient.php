<?php
/**
 * This file is part of the <Cache> project.
 * 
 * @uses CacheClientInterface
 * @subpackage   Cache
 * @package    Manager
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since 2012-02-03
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CacheBundle\Manager\Client;

use Sfynx\CacheBundle\Manager\Generalisation\ClientInterface;

/**
 * Client interface for Memcache servers
 * 
 * @uses CacheInterface
 * @subpackage   Cache
 * @package    Manager
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class MemcacheClient implements ClientInterface
{
    protected $path = null;
    protected $options = null;

    protected $safe = false;
    protected $mem = null;
    protected $servers = [];
    protected $sockttl = 0.2;
    protected $compression = false;

    /**
   * Constructs the cache client using an injected Memcache instance
     * 
     * @access public
     * @return void
     */
    public function __construct( \Memcache $memcache )
    {
        $this->mem = $memcache;
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
        if ($this->isSafe()) {
            return $this->mem->get($key);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl)
    {
        if ($this->isSafe()) {
            return $this->mem->set($key, $value, $this->compression, $ttl);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function fresh($key, $value = null)
    {
        if ($this->isSafe()) {
            return $this->mem->set($key, $value, $this->compression);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSafe($key = null)
    {
        return $this->safe;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key)
    {
        return $this->mem->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        return $this;
    }

    /**
     * Add a server to the memcache pool.
     *
     * Does not probe server, does not set Safe to true.
     *
     * Should really be private, or modified to handle the probeServer action itself.
     *
     * @param string $ip Location of memcache server
     * @param int $port Optional: Port number (default: 11211)
     * @access public
     * @return void
     */
    public function addServer($ip, $port = 11211)
    {
        if (is_object($this->mem)) {
            return $this->mem->addServer($ip, $port);
        }
    }

    /**
     * Add an array of servers to the memcache pool
     *
     * Uses ProbeServer to verify that the connection is valid.
     *
     * Format of array:
     *
     *   $servers[ '127.0.0.1' ] = 11211;
     *
     * Logic is somewhat flawed, of course, because it wouldn't let you add multiple
     * servers on the same IP.
     *
     * Serious flaw, right? ;-)
     *
     * @param array $servers See above format definition
     * @access public
     * @return void
     */
    public function addServers(array $servers)
    {
        if (count( $servers ) == 0) {
            return false;
        }

        foreach ($servers as $ip=>$port) {
            if ($this->probeServer($ip, $port)) {
                $status = $this->addServer($ip, $port);
                $this->safe = true;
            }
        }
    }

    /**
     * Spend a few tenths of a second opening a socket to the requested IP and port
     *
     * The purpose of this is to verify that the server exists before trying to add it,
     * to cut down on weird errors when doing ->get(). This could be a controversial or
     * flawed way to go about this.
     *
     * @param string $ip IP address (or hostname, possibly)
     * @param int $port Port that memcache is running on
     * @access public
     * @return boolean True if the socket opens successfully, or false if it fails
     */
    public function probeServer($ip, $port)
    {
        $errno  = null;
        $errstr = null;
        $fp     = fsockopen($ip, $port, $errno, $errstr, $this->sockttl);

        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * getStats returns the result of Memcache::getExtendedStats(), an associative array
     * containing arrays of server stats
     * 
     * @access public
     * @return array Server stats array
     */
    public function getStats()
    {
        return $this->mem->getExtendedStats();
    }
}