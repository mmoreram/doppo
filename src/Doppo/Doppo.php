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
use ReflectionClass;

use Doppo\Interfaces\DependencyInjectionContainerInterface;

/**
 * Class Doppo
 *
 * ** Configuration reference
 *
 * $config = array(
 *      'my_service' => array(
 *          'class' => 'My\Class\Namespace',
 *          'arguments => array(
 *              '@my_other_service',
 *              '~my_parameter',
 *              'simple_value',
 *          )
 *      ),
 *      'my_other_service' => array(
 *          'class' => 'My\Class\Namespace',
 *      ),
 *      'my_parameter' => 'parameter_value',
 * );
 *
 * ** Usage
 *
 * $doppo = new Doppo($config);
 *
 * $serviceInstance = $doppo->get('my_service');
 * $parameterValue = $doppo->getParameter('my_parameter');
 * $containerCompiledConfiguration = $doppo->getCompiledConfiguration();
 */
class Doppo implements DependencyInjectionContainerInterface
{
    /**
     * @var string
     *
     * Const for service definition
     */
    const SERVICE_PREFIX = '@';

    /**
     * @var string
     *
     * Const for service definition
     */
    const PARAMETER_PREFIX = '~';

    /**
     * @var array
     *
     * Parameters
     */
    private $parameters;

    /**
     * @var array
     *
     * Services
     */
    private $services;

    /**
     * @var array
     *
     * ServicesInstances
     */
    private $serviceInstances;

    /**
     * Constructor
     *
     * @param array $configuration Container Configuration
     *
     * @return self New instance of this object
     */
    public function __construct(array $configuration)
    {
        $this->services = array();
        $this->parameters = array();
        $this->compile($configuration);
        $this->checkServiceArgumentsReferences();
    }

    /**
     * Compile the container
     *
     * @param array $configuration Container Configuration
     *
     * @throws Exception Element type is not correct
     */
    protected function compile(array $configuration)
    {
        foreach ($configuration as $configurationName => $configurationElement) {

            if (is_array($configurationElement) && array_key_exists('class', $configurationElement)) {

                $this->compileService(
                    $configurationName,
                    $configurationElement
                );
            } else {
                $this->compileParameter(
                    $configurationName,
                    $configurationElement
                );
            }
        }
    }

    /**
     * Compile a service
     *
     * @param string $serviceName          Service name
     * @param array  $serviceConfiguration Service configuration
     *
     * @throws Exception Service class not found
     */
    protected function compileService($serviceName, array $serviceConfiguration)
    {
        if (!class_exists($serviceConfiguration['class'])) {

            throw new Exception(sprintf('Class %s not found', $serviceConfiguration['class']));
        }

        $arguments = isset($serviceConfiguration['arguments'])
            ? $serviceConfiguration['arguments']
            : array();

        $this->services[$serviceName] = array(
            'class'     => '\\' . ltrim($serviceConfiguration['class'], '\\'),
            'arguments' => array_values($arguments),
        );
    }

    /**
     * Compile a parameter
     *
     * @param string $parameterName  Parameter name
     * @param string $parameterValue Parameter value
     */
    protected function compileParameter($parameterName, $parameterValue)
    {
        $this->parameters[$parameterName] = $parameterValue;
    }

    /**
     * Check services arguments references
     *
     * This call has only sense if the service stack is built before. The why
     * of this methods is because now we have the correct acknowledgement about
     * all the services and parameters we will work with.
     *
     * We will now check that all service arguments have correct references.
     */
    protected function checkServiceArgumentsReferences()
    {
        foreach ($this->services as $serviceName => $service) {

            if (!isset($service['arguments'])) {

                continue;
            }

            $arguments = $service['arguments'];
            foreach ($arguments as $argument) {

                if (!is_string($argument)) {

                    continue;
                }

                /**
                 * Service reference
                 */
                if (0 === strpos($argument, self::SERVICE_PREFIX)) {

                    $cleanArgument = preg_replace('#^' . self::SERVICE_PREFIX . '{1}#', '', $argument);
                    if (!isset($this->services[$cleanArgument])) {

                        throw new Exception(
                            sprintf(
                                'Service "%s" not found in "@%s" arguments list',
                                $cleanArgument,
                                $serviceName
                            )
                        );
                    }
                }

                /**
                 * Parameter reference
                 */
                if (0 === strpos($argument, self::PARAMETER_PREFIX)) {

                    $cleanArgument = preg_replace('#^(' . self::PARAMETER_PREFIX . '){1}#', '', $argument);
                    if (!isset($this->parameters[$cleanArgument])) {

                        throw new Exception(
                            sprintf('Parameter "%s" not found in "@%s" arguments list',
                                $cleanArgument,
                                $serviceName
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Build service.
     *
     * @param string $serviceName Service Name
     *
     * @return mixed Service instance
     */
    protected function buildExistentService($serviceName)
    {
        $serviceConfiguration = $this->services[$serviceName];
        $serviceReflectionClass = new ReflectionClass($serviceConfiguration['class']);
        $serviceArguments = array();

        /**
         * Each argument is built using recursivity. If the argument is defined
         * as a service, with (@), we will return the value of the get() call.
         *
         * Otherwise, if is defined as a parameter (%) we will return the
         * parameter value
         *
         * Otherwise, we will treat the value as a plain value, not precessed.
         */
        foreach ($serviceConfiguration['arguments'] as $argument) {

            if (is_string($argument) && strpos($argument, self::SERVICE_PREFIX) === 0) {

                $cleanArgument = preg_replace('#^' . self::SERVICE_PREFIX . '{1}#', '', $argument);
                $serviceArguments[] = $this->get($cleanArgument);

            } elseif (is_string($argument) && strpos($argument, self::PARAMETER_PREFIX) === 0) {

                $cleanArgument = preg_replace('#^' . self::PARAMETER_PREFIX . '{1}#', '', $argument);
                $serviceArguments[] = $this->getParameter($cleanArgument);
            } else {

                $serviceArguments[] = $argument;
            }
        }

        return $serviceReflectionClass->newInstanceArgs($serviceArguments);
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
        if (!isset($this->services[$serviceName])) {

            throw new Exception(
                sprintf(
                    'Service "%s" not found',
                    $serviceName
                )
            );
        }

        return $this->serviceInstances[$serviceName] = $this->buildExistentService($serviceName);
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
        if (!isset($this->parameters[$parameterName])) {

            throw new Exception(
                sprintf(
                    'Parameter "%s" not found',
                    $parameterName
                )
            );
        }

        return $this->parameters[$parameterName];
    }

    /**
     * Return compiled service configuration
     *
     * @return array Compiled service configuration
     */
    public function getCompiledServiceConfiguration()
    {
        return $this->services;
    }

    /**
     * Return compiled configuration
     *
     * @return array Compiled configuration
     */
    public function getCompiledParameterConfiguration()
    {
        return $this->parameters;
    }
}
