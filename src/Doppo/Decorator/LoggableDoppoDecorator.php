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

namespace Doppo\Decorator;

use Exception;
use Psr\Log\LoggerInterface;

use Doppo\Interfaces\ContainerInterface;

/**
 * Class LoggableDoppoDecorator
 */
class LoggableDoppoDecorator implements ContainerInterface
{
    /**
     * @var ContainerInterface
     *
     * container
     */
    protected $container;

    /**
     * @var LoggerInterface
     *
     * logger
     */
    protected $logger;

    /**
     * Construct method
     *
     * @param ContainerInterface $container container
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger
    )
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Compile container
     */
    public function compile()
    {
        if ($this->container->isDebug()) {

            $this->logDebugContainerAction('Compiling container');
        }

        try {

            $this->container->compile();
        } catch (Exception $e) {

            if ($this->container->isDebug()) {

                $this->logErrorContainerAction('Container compilation failed');
            }

            throw $e;
        }
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
        if ($this->container->isDebug()) {

            $this->logDebugContainerAction(sprintf('Service %s requested', $serviceName));
        }

        try {
            $serviceInstance = $this->container->get($serviceName);

        } catch (Exception $e) {

            if ($this->container->isDebug()) {

                $this->logErrorContainerAction(sprintf('Service %s requested and not found', $serviceName));
            }
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
        if ($this->container->isDebug()) {

            $this->logDebugContainerAction(sprintf('Parameter %s requested', $parameterName));
        }

        try {
            $parameterValue = $this->container->getParameter($parameterName);

        } catch (Exception $e) {

            if ($this->container->isDebug()) {

                $this->logErrorContainerAction(sprintf('Parameter %s requested and not found', $parameterName));
            }

            throw $e;
        }

        return $parameterValue;
    }

    /**
     * Is debug
     */
    public function isDebug()
    {
        return $this->container->isDebug();
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
