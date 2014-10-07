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
use Psr\Log\LoggerInterface;

use Doppo\Interfaces\DependencyInjectionContainerInterface;

/**
 * Class LoggedDoppo
 */
class LoggedDoppo implements DependencyInjectionContainerInterface
{
    /**
     * @var DependencyInjectionContainerInterface
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
     * @param DependencyInjectionContainerInterface $doppo  Doppo
     * @param LoggerInterface                       $logger Logger
     */
    public function __construct(
        DependencyInjectionContainerInterface $doppo,
        LoggerInterface $logger
    )
    {
        $this->doppo = $doppo;
        $this->logger = $logger;
    }

    /**
     * Return compiled service configuration
     *
     * @return array Compiled service configuration
     */
    public function getCompiledServiceConfiguration()
    {
        $this->doppo->getCompiledServiceConfiguration();
    }

    /**
     * Return compiled configuration
     *
     * @return array Compiled configuration
     */
    public function getCompiledParameterConfiguration()
    {
        $this->doppo->getCompiledParameterConfiguration();
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
    protected function logDebugContainerAction($log = '')
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
