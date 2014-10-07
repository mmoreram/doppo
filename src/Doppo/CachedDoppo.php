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

use Doppo\Interfaces\DependencyInjectionContainerInterface;
use DoppoCache;

/**
 * Class CachedDoppo
 */
class CachedDoppo implements DependencyInjectionContainerInterface
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
     * @param DependencyInjectionContainerInterface $doppo     Doppo instance
     * @param string                                $cacheFile Cache file in local filesystem
     * @param boolean                               $debug     Debug mode
     */
    public function __construct(
        DependencyInjectionContainerInterface $doppo,
        $cacheFile,
        $debug
    )
    {
        $this->doppo = $doppo;
        $this->serviceInstances = array();
        $this->cacheFileTemplate = dirname(__FILE__) . '/Templates/doppo.php.cache';

        $this->loadCache($cacheFile, $debug);
    }

    /**
     * Cache compiled configuration
     *
     * @param string  $cacheFile Cache file in local filesystem
     * @param boolean $debug     Debug mode
     */
    protected function loadCache($cacheFile, $debug)
    {
        if ($debug || !file_exists($cacheFile)) {

            if (file_exists($cacheFile)) {

                unlink($cacheFile);
            }

            $cacheData = $this->getCacheCompiledConfiguration();
            file_put_contents($cacheFile, $cacheData);
        }

        require_once $cacheFile;
        $this->doppoCache = new DoppoCache;
    }

    /**
     * Get the code of all container related methods for Cache class
     *
     * This new cache is built by using the template and overriding needed
     * blocks.
     *
     * @return string Container cache code
     */
    protected function getCacheCompiledConfiguration()
    {
        $cacheContent = file_get_contents($this->cacheFileTemplate);
        $cacheContent = str_replace(
            '{% parameters_content %}',
            $this->getCacheCompiledParameterConfiguration(),
            $cacheContent
        );

        $cacheContent = str_replace(
            array(
                '{% parameters_content %}',
                '{% services_content %}',
            ),
            array(
                $this->getCacheCompiledParameterConfiguration(),
                $this->getCacheCompiledServiceConfiguration(),
            ),
            $cacheContent
        );

        return $cacheContent;
    }

    /**
     * Get the code of all services related methods for Cache class
     *
     * @return string Services cache code
     */
    protected function getCacheCompiledServiceConfiguration()
    {
        $cacheContent = '';

        foreach ($this->getCompiledServiceConfiguration() as $serviceName => $serviceConfiguration) {

            $cacheArgumentsStack = array();

            foreach ($serviceConfiguration['arguments'] as $argument) {

                if (is_string($argument) && strpos($argument, Doppo::SERVICE_PREFIX) === 0) {

                    $cleanArgument = preg_replace('#^' . Doppo::SERVICE_PREFIX . '{1}#', '', $argument);
                    $method = $this->getCachedServiceMethodName($cleanArgument);
                    $cacheArgumentsStack[] = "\$this->{$method}()";

                } elseif (is_string($argument) && strpos($argument, Doppo::PARAMETER_PREFIX) === 0) {

                    $cleanArgument = preg_replace('#^' . Doppo::PARAMETER_PREFIX . '{1}#', '', $argument);
                    $cacheArgumentsStack[] = "\$this->parameters[\"{$cleanArgument}\"]";
                } else {

                    $cacheArgumentsStack[] = var_export($argument, true);
                }
            }

            $method = $this->getCachedServiceMethodName($serviceName);
            $cacheArguments = '';

            if ($cacheArgumentsStack) {
                $cacheArguments = "
            " . implode(',
            ', $cacheArgumentsStack) . "
        ";
            }

            $cacheContent .= "/**
     * Return instance of service {$serviceName}
     *
     * @return {$serviceConfiguration['class']} Service instance
     */
    public function {$method}()
    {
        return new {$serviceConfiguration['class']}({$cacheArguments});
    }

    ";
        }

        return $cacheContent;
    }

    /**
     * Get the code of all parameters related methods for Cache class
     *
     * @return string Parameters cache code
     */
    protected function getCacheCompiledParameterConfiguration()
    {
        $parametersExported = var_export($this->getCompiledParameterConfiguration(), true);
        $cacheContent = "/**
     * @var array
     *
     * parameters
     */
    private \$parameters = {$parametersExported};

    ";

        return $cacheContent;
    }

    /**
     * Return compiled service configuration
     *
     * @return array Compiled service configuration
     */
    public function getCompiledServiceConfiguration()
    {
        return $this->doppo->getCompiledServiceConfiguration();
    }

    /**
     * Return compiled configuration
     *
     * @return array Compiled configuration
     */
    public function getCompiledParameterConfiguration()
    {
        return $this->doppo->getCompiledParameterConfiguration();
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
        $methodName = $this->getCachedServiceMethodName($serviceName);

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

    /**
     * Build service cached name given its name
     *
     * @param string $serviceName Service name
     *
     * @return string Service Cached method name
     */
    protected function getCachedServiceMethodName($serviceName)
    {
        return 'getService_' . $this->sanitizeServiceNameForCache($serviceName);
    }

    /**
     * Sanitize service name for cache
     *
     * @param string $serviceName Service name
     *
     * @return string Service name sanitized
     */
    protected function sanitizeServiceNameForCache($serviceName)
    {
        return preg_replace('#[^\w]#', '_', $serviceName);
    }
}
