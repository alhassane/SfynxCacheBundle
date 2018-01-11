<?php
/**
 * This file is part of the <Cache> project.
 *
 * @uses       CacheClientInterface
 * @package    Cache
 * @subpackage Manager
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since      2012-02-03
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CacheBundle\Manager\Client;

use Sfynx\CacheBundle\Manager\Generalisation\ClientInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Completely untested and undocumented. Use at your own risk!
 *
 * Fixes appreciated!
 *
 * @uses       CacheInterface
 * @package    Cache
 * @subpackage Manager
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * 
 * <code>
 *     $options     = [
 *         'namespace_dir'  => 'Sfynx/Page',
 *         'namespace_file' => 'appCache',
 *         'dumper_class'   => 'Sfynx\\CmfBundle\\Dumper\\PhpPageDumper',
 *         'dumper_options' => [],
 *         'cache_metadata' => null
 *     ];
 *     $this->container->get("sfynx.cache.dumpercache")->setOptions($options)->set($entity->getId(), $entity);
 * </code>
 */
class DumperClient implements ClientInterface
{
    protected $path = null;
    protected $options = null;
    protected $kernel;
    protected $tenvironment;
    protected $debug;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel      = $kernel;
        $this->environment = ucfirst($kernel->getEnvironment());
        $this->debug       = (bool) $kernel->isDebug();
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
            return new ConfigCache($this->setPath()->buildFilename($key), false);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 3600)
    {
        try {
            $cache = new ConfigCache($this->setPath()->buildFilename($key), false);
            // cache the container
            $dumper = new $this->options['dumper_class']($value);
            $cache->write($dumper->dump($this->options['dumper_options']), $this->options['cache_metadata']);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fresh($key, $value = null)
    {
        if ($this->isBuildSafe($key)) {
            $this->clear($key);
            $this->get($key);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSafe($key = null)
    {
        return true;
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
        $this->path = $this->kernel->getCacheDir().'/'. $this->options['namespace_dir'] .'/';
        return $this;
    }

    protected function isBuildSafe($key = null)
    {
        if (empty($key) || !file_exists($this->setPath()->buildFilename($key))) {
            return false;
        }
        return true;
    }

    protected function buildClass($key)
    {
        $this->options['dumper_options']['class'] = $this->options['namespace_file'] . $this->environment . ( $this->debug ? 'Debug' : '' )  . sha1($key);
        $this->options['dumper_options']['namespace'] = $this->options['dumper_options']['class'];
        return $this->options['dumper_options']['class'];
    }

    protected function buildFilename($key)
    {
        return $this->path . $this->buildClass($key);
    }
}
