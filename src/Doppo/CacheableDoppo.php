<?php

/**
 * This file is part of the Doppo package
 *
 * Copyright (c) 2014 Marc Morera
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Doppo;

use Exception;

use Doppo\Cache\Cache;
use Doppo\Definition\ParameterDefinitionChain;
use Doppo\Definition\ServiceDefinitionChain;
use Doppo\Interfaces\ContainerInterface;
use DoppoCache;

/**
 * Class CacheableDoppo
 */
class CacheableDoppo implements ContainerInterface
{
    /**
     * @var string
     *
     * Cache file template
     */
    private $cacheFileTemplate;

    /**
     * @var Doppo
     *
     * Doppo container
     */
    private $doppo;

    /**
     * @var string
     *
     * Cache file
     */
    private $cacheFile;

    /**
     * @var boolean
     *
     * Debug mode
     */
    private $debug;

    /**
     * @var boolean
     *
     * Compiled
     */
    private $compiled;

    /**
     * @var DoppoCache
     *
     * Cache
     */
    public $doppoCache;

    /**
     * @var array
     *
     * ServicesInstances
     */
    private $serviceInstances;

    /**
     * construct method
     *
     * @param ContainerInterface $doppo     Doppo instance
     * @param string             $cacheFile Cache file in local filesystem
     * @param boolean            $debug     Debug mode
     */
    public function __construct(
        ContainerInterface $doppo,
        $cacheFile,
        $debug
    )
    {
        $this->doppo = $doppo;
        $this->cacheFile = $cacheFile;
        $this->debug = $debug;
        $this->serviceInstances = array();
    }

    /**
     * Compile container
     *
     * @throws Exception Container already compiled
     */
    public function compile()
    {
        if (true === $this->compiled) {

            throw new Exception(
                'Container already compiled'
            );
        }

        if (!$this->isCacheUsable()) {

            $this->doppo->compile();
            $this->warmUpCache();
        }

        require_once $this->cacheFile;
        $this->doppoCache = new DoppoCache;

        $this->compiled = true;
    }

    /**
     * Return if cache is useful
     *
     * @return boolean Cache is useful
     */
    private function isCacheUsable()
    {
        return (!$this->debug && file_exists($this->cacheFile));
    }

    /**
     * Warm up cache. If cache exists, override it
     */
    private function warmUpCache()
    {
        if (file_exists($this->cacheFile)) {

            unlink($this->cacheFile);
        }

        $cache = new Cache(
            $this->getCompiledServiceDefinitions(),
            $this->getCompiledParameterDefinitions()
        );

        file_put_contents($this->cacheFile, $cache->build());
    }

    /**
     * Return compiled service definitions
     *
     * @return ServiceDefinitionChain Compiled service definitions
     */
    public function getCompiledServiceDefinitions()
    {
        return $this->doppo->getCompiledServiceDefinitions();
    }

    /**
     * Return compiled parameter definitions
     *
     * @return ParameterDefinitionChain Compiled parameter definitions
     */
    public function getCompiledParameterDefinitions()
    {
        return $this->doppo->getCompiledParameterDefinitions();
    }

    /**
     * Get service instance
     *
     * @param string $serviceName Service Name
     *
     * @return mixed Service instance
     *
     * @throws Exception Service not found
     */
    public function get($serviceName)
    {
        /**
         * The service is found as an instance, so we can be ensured that the
         * value inside this position is a valid Service instance
         */
        if (isset($this->serviceInstances[$serviceName])) {
            return $this->serviceInstances[$serviceName];
        }

        /**
         * Otherwise, we must check if the service defined with its name has
         * been compiled
         */
        $methodName = Cache::getCachedServiceMethodName($serviceName);

        if (!method_exists($this->doppoCache, $methodName)) {

            throw new Exception(
                sprintf(
                    'Service "%s" not found',
                    $serviceName
                )
            );
        }

        return $this->serviceInstances[$serviceName] = $this
            ->doppoCache
            ->$methodName();
    }

    /**
     * Get parameter value
     *
     * @param string $parameterName Parameter Name
     *
     * @return mixed Parameter value
     *
     * @throws Exception Parameter not found
     */
    public function getParameter($parameterName)
    {
        return $this->doppo->getParameter($parameterName);
    }
}
