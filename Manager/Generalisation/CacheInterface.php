<?php
/**
 * This file is part of the <Cache> project.
 *
 * @subpackage   Cache
 * @package    Builder
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since 2012-02-23
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CacheBundle\Manager\Generalisation;

/**
 * CacheInterface 
 * 
 * @subpackage   Cache
 * @package    Builder
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
interface CacheInterface
{
    /**
     * Inject options values (optional)
     *
     * @param mixed $options The options
     * @access public
     * @return void
     */
    public function setOptions($options);

    /**
     * Retrieve the value corresponding to a provided key
     *
     * @param string $key Unique identifier
     * @access public
     * @return mixed Result from the cache
     */
    public function get($key);

    /**
     * Add a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @param mixed $value Data to store in the cache
     * @param int $ttl Lifetime for stored data (in seconds)
     * @access public
     * @return void
     */
    public function set($key, $value, $ttl);

    /**
     * Fresh a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @param mixed $value Data to store in the cache
     * @access public
     * @return void
     */
    public function fresh($key, $value = null);

    /**
     * Check the state of the cache
     *
     * @access public
     * @return boolean True if the cache is in a usable state, otherwise false
     */
    public function isSafe();

    /**
     * Delete a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @access public
     * @return boolean
     */
    public function clear($key);

    /**
     * Set path
     *
     * @param string $path
     * @access public
     * @return CacheInterface
     */
    public function setPath($path);
}
