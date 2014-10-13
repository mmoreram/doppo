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

use Doppo\Cache\Cache;
use Doppo\Exception\DoppoAlreadyCompiledException;
use DoppoCache;

/**
 * Class CacheableDoppo
 */
class CacheableDoppo extends Doppo
{
    /**
     * @var string
     *
     * Cache file
     */
    private $cacheFile;

    /**
     * @var DoppoCache
     *
     * Cache
     */
    public $doppoCache;

    /**
     * Constructor
     *
     * @param array   $configuration Container Configuration
     * @param string  $cacheFile     Cache file in local filesystem
     * @param boolean $debug         Debug mode
     */
    public function __construct(
        array $configuration,
        $debug,
        $cacheFile
    ) {
        parent::__construct($configuration, $debug);

        $this->cacheFile = $cacheFile;
    }

    /**
     * Compile container
     *
     * @throws DoppoAlreadyCompiledException Container already compiled
     */
    public function compile()
    {
        if (true === $this->compiled) {

            throw new DoppoAlreadyCompiledException(
                'Container already compiled'
            );
        }

        if (!$this->isCacheUsable()) {

            $this->compiled = false;
            parent::compile();
            $this->warmUpCache();
        }

        require_once $this->cacheFile;
        $this->doppoCache = new DoppoCache;
    }

    /**
     * Return if cache is useful
     *
     * @return boolean Cache is useful
     */
    private function isCacheUsable()
    {
        return (!$this->isDebug() && file_exists($this->cacheFile));
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
            $this->services,
            $this->parameters
        );

        file_put_contents($this->cacheFile, $cache->build());
    }

    /**
     * Build service. We assume that the service exists and can be build
     *
     * @param string $serviceName Service Name
     *
     * @return mixed Service instance
     */
    protected function buildExistentService($serviceName)
    {
        $methodName = Cache::getCachedServiceMethodName($serviceName);

        return $this->serviceInstances[$serviceName] = $this
            ->doppoCache
            ->$methodName()
        ;
    }
}
