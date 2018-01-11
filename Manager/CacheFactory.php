<?php
/**
 * This file is part of the <Cache> project.
 * 
 * @uses CacheInterface
 * @subpackage   Cache
 * @package    Manager
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since 2012-02-03
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CacheBundle\Manager;

use Sfynx\CacheBundle\Manager\Generalisation\CacheInterface;
use Sfynx\CacheBundle\Manager\Generalisation\ClientInterface;

/**
 * cache factory.
 *
 * @uses CacheInterface
 * @subpackage   Cache
 * @package    Manager
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class CacheFactory implements CacheInterface
{
    protected $options = null;
    protected $client = null;
    protected $safe = false;

    /**
     * Prep the cache
     * 
     * @param CacheInterface $client Optional cache object/service
     * @param mixed $options Option values
     * @access public
     * @return void
     */
    public function __construct(ClientInterface $client = null, $options = null)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        $this->client->setOptions($options);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($this->isSafe($key)) {
            return $this->client->get($key);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 300 )
    {
        if ($this->isSafe($key)) {
            return $this->client->set($key, $value, $ttl);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key)
    {
        if ($this->isSafe($key)) {
            return $this->client->clear($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSafe($key = null)
    {
        if (!empty($key) && $this->client instanceof ClientInterface) {
            return $this->client->isSafe($key);
        }

        return $this->safe;
    }

    /**
     * {@inheritdoc}
     */
    public function fresh($key, $value = null)
    {
        if ($this->isSafe($key)) {
            return $this->client->fresh($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        $this->client->setPath($path);
        return $this;
    }
}
