<?php

/*
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

namespace Doppo\Cache;

use Doppo\Argument\Argument;
use Doppo\Argument\ParameterArgument;
use Doppo\Argument\ServiceArgument;
use Doppo\Definition\ParameterDefinition;
use Doppo\Definition\ParameterDefinitionChain;
use Doppo\Definition\ServiceDefinition;
use Doppo\Definition\ServiceDefinitionChain;

/**
 * Class Cache
 */
class Cache
{
    /**
     * @var ServiceDefinitionChain
     *
     * Service Definition chain
     */
    private $serviceDefinitionChain;

    /**
     * @var ParameterDefinitionChain
     *
     * Parameter Definition chain
     */
    private $parameterDefinitionChain;

    /**
     * Construct method
     *
     * @param ServiceDefinitionChain   $serviceDefinitionChain   Service Definition chain
     * @param ParameterDefinitionChain $parameterDefinitionChain Parameter Definition chain
     */
    public function __construct(
        ServiceDefinitionChain $serviceDefinitionChain,
        ParameterDefinitionChain $parameterDefinitionChain
    ) {
        $this->serviceDefinitionChain = $serviceDefinitionChain;
        $this->parameterDefinitionChain = $parameterDefinitionChain;
    }

    /**
     * Build cache
     *
     * @return string Cache stream
     */
    public function build()
    {
        $templateFile = dirname(__FILE__) . '/Templates/doppo.cache.php.template';
        $cacheContent = file_get_contents($templateFile);
        $cacheContent = str_replace(
            array(
                '{% parameters_content %}',
                '{% services_content %}',
            ),
            array(
                $this->buildServiceCacheBlock(),
                $this->buildParameterCacheBlock(),
            ),
            $cacheContent
        );

        return $cacheContent;
    }

    /**
     * Build service cache block
     *
     * @return string Service cache block
     */
    public function buildServiceCacheBlock()
    {
        $cacheContent = '';

        $this
            ->serviceDefinitionChain
            ->each(function (ServiceDefinition $serviceDefinition) use (&$cacheContent) {

                $serviceName = $serviceDefinition->getName();
                $serverClass = $serviceDefinition->getClass();
                $cacheArgumentsStack = array();
                $serviceDefinition
                    ->getArgumentChain()
                    ->each(function (Argument $argument) use (&$cacheDefinition, &$cacheArgumentsStack) {

                        $argumentValue = $argument->getValue();
                        if ($argument instanceof ServiceArgument) {

                            $method = $this->getCachedServiceMethodName($argumentValue);
                            $cacheArgumentsStack[] = "\$this->{$method}()";
                        } elseif ($argument instanceof ParameterArgument) {

                            $cacheArgumentsStack[] = "\$this->parameters[\"{$argumentValue}\"]";
                        } else {

                            $cacheArgumentsStack[] = var_export($argumentValue, true);
                        }
                    });

                $serviceCacheableMethod = self::getCachedServiceMethodName($serviceName);
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
     * @return {$serverClass} Service instance
     */
    public function {$serviceCacheableMethod}()
    {
        return new {$serverClass}({$cacheArguments});
    }

    ";
            });

        return $cacheContent;
    }

    /**
     * Build parameter cache block
     *
     * @return string Parameter cache block
     */
    public function buildParameterCacheBlock()
    {
        $parameters = array();

        $this
            ->parameterDefinitionChain
            ->each(function (ParameterDefinition $parameterDefinition) use (&$parameters) {

                $parameters[$parameterDefinition->getName()] = $parameterDefinition->getValue();
            });

        $parametersExported = var_export($parameters, true);
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
     * Build service cached name given its name
     *
     * @param string $serviceName Service name
     *
     * @return string Service Cached method name
     */
    public static function getCachedServiceMethodName($serviceName)
    {
        return 'getService_' . self::sanitizeServiceNameForCache($serviceName);
    }

    /**
     * Sanitize service name for cache
     *
     * @param string $serviceName Service name
     *
     * @return string Service name sanitized
     */
    public static function sanitizeServiceNameForCache($serviceName)
    {
        return preg_replace('#[^\w]#', '_', $serviceName);
    }
}
