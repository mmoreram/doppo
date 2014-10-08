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

use Doppo\Definition\ParameterDefinitionChain;
use Doppo\Definition\ServiceDefinitionChain;
use Exception;
use Psr\Log\LoggerInterface;

use Doppo\Interfaces\ContainerInterface;

/**
 * Class LoggableDoppo
 */
class LoggableDoppo implements ContainerInterface
{
    /**
     * @var ContainerInterface
     *
     * doppo
     */
    protected $doppo;

    /**
     * @var LoggerInterface
     *
     * logger
     */
    protected $logger;

    /**
     * Construct method
     *
     * @param ContainerInterface $doppo  Doppo
     * @param LoggerInterface    $logger Logger
     */
    public function __construct(
        ContainerInterface $doppo,
        LoggerInterface $logger
    )
    {
        $this->doppo = $doppo;
        $this->logger = $logger;
    }

    /**
     * Compile container
     */
    public function compile()
    {
        $this->logDebugContainerAction('Compiling container');

        try {

            $this->doppo->compile();
        } catch (Exception $e) {

            $this->logErrorContainerAction('Container compilation failed');
            throw $e;
        }
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
        $this->logDebugContainerAction(sprintf('Service %s requested', $serviceName));

        try {
            $serviceInstance = $this->doppo->get($serviceName);

        } catch (Exception $e) {

            $this->logErrorContainerAction(sprintf('Service %s requested and not found', $serviceName));
            throw $e;
        }

        return $serviceInstance;
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
        $this->logDebugContainerAction(sprintf('Parameter %s requested', $parameterName));

        try {
            $parameterValue = $this->doppo->getParameter($parameterName);

        } catch (Exception $e) {

            $this->logErrorContainerAction(sprintf('Parameter %s requested and not found', $parameterName));
            throw $e;
        }

        return $parameterValue;
    }

    /**
     * Log action
     *
     * @param string $log Log
     */
    protected function logDebugContainerAction($log)
    {
        $this
            ->logger
            ->debug($log);
    }

    /**
     * Log action
     *
     * @param string $log Log
     */
    protected function logErrorContainerAction($log)
    {
        $this
            ->logger
            ->error($log);
    }
}
