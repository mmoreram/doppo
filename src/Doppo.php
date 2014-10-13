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

use Doppo\Argument\Argument;
use Doppo\Argument\ArgumentChain;
use Doppo\Argument\ParameterArgument;
use Doppo\Argument\ServiceArgument;
use Doppo\Argument\ValueArgument;
use Doppo\Definition\ParameterDefinition;
use Doppo\Definition\ParameterDefinitionChain;
use Doppo\Definition\ServiceDefinition;
use Doppo\Definition\ServiceDefinitionChain;
use Doppo\Exception\DoppoAlreadyCompiledException;
use Doppo\Exception\DoppoNotCompiledException;
use Doppo\Exception\DoppoParameterNotExistsException;
use Doppo\Exception\DoppoServiceArgumentNotExistsException;
use Doppo\Exception\DoppoServiceClassNotFoundException;
use Doppo\Exception\DoppoServiceNotExistsException;
use Doppo\Interfaces\ContainerInterface;

/**
 * Class Doppo
 *
 * ** Configuration reference
 *
 * $debug = true;
 * $configuration = array(
 *      'my_service' => array(
 *          'class' => 'My\Class\Namespace',
 *          'arguments' => array(
 *              '@my_other_service',
 *              '~my_parameter',
 *              'simple_value',
 *          ),
 *      ),
 *      'my_other_service' => array(
 *          'class' => 'My\Class\Namespace',
 *      ),
 *      'my_parameter' => 'parameter_value',
 * );
 *
 * ** Usage
 *
 * $doppo = new Doppo($configuration, $debug);
 * $doppo->compile();
 *
 * $serviceInstance = $doppo->get('my_service');
 * $parameterValue = $doppo->getParameter('my_parameter');
 */
class Doppo implements ContainerInterface
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
     * Configuration
     */
    protected $configuration;

    /**
     * @var boolean
     *
     * Debug mode
     */
    private $debug;

    /**
     * @var ParameterDefinitionChain
     *
     * Parameters
     */
    protected $parameters;

    /**
     * @var ServiceDefinitionChain
     *
     * Services
     */
    protected $services;

    /**
     * @var boolean
     *
     * Compiled
     */
    protected $compiled;

    /**
     * @var array
     *
     * ServicesInstances
     */
    protected $serviceInstances;

    /**
     * Constructor
     *
     * @param array   $configuration Container Configuration
     * @param boolean $debug         Debug mode
     */
    public function __construct(array $configuration, $debug)
    {
        $this->configuration = $configuration;
        $this->debug = $debug;
        $this->services = new ServiceDefinitionChain();
        $this->parameters = new ParameterDefinitionChain();
        $this->compiled = false;
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

        $this->compileConfiguration($this->configuration);
        $this->checkServiceArgumentsReferences();

        $this->compiled = true;
    }

    /**
     * Is debug
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Compile the configuration
     *
     * @param array $configuration Container Configuration
     *
     * @throws Exception Element type is not correct
     */
    protected function compileConfiguration(array $configuration)
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
     * @throws DoppoServiceClassNotFoundException Service class not found
     */
    protected function compileService($serviceName, array $serviceConfiguration)
    {
        if (!class_exists($serviceConfiguration['class'])) {

            throw new DoppoServiceClassNotFoundException(
                sprintf(
                    'Class %s not found',
                    $serviceConfiguration['class']
                )
            );
        }

        $arguments = isset($serviceConfiguration['arguments'])
            ? $serviceConfiguration['arguments']
            : array();

        $public = isset($serviceConfiguration['public'])
            ? $serviceConfiguration['public']
            : true;

        $this
            ->services
            ->addServiceDefinition(
                new ServiceDefinition(
                    $serviceName,
                    '\\' . ltrim($serviceConfiguration['class'], '\\'),
                    $this->compileArguments($arguments),
                    $public
                )
            );
    }

    /**
     * Compile arguments
     *
     * @param array $arguments Argument configuration
     *
     * @return ArgumentChain Argument chain
     */
    protected function compileArguments(array $arguments)
    {
        $argumentChain = new ArgumentChain();

        foreach ($arguments as $argument) {

            $argumentChain->addArgument(
                $this->compileArgument($argument)
            );
        }

        return $argumentChain;
    }

    /**
     * Given an argument return its definition
     *
     * @param string $argument Argument
     *
     * @return Argument Argument
     */
    protected function compileArgument($argument)
    {
        $argumentDefinition = null;

        if (is_string($argument) && strpos($argument, Doppo::SERVICE_PREFIX) === 0) {

            $cleanArgument = preg_replace('#^' . Doppo::SERVICE_PREFIX . '{1}#', '', $argument);

            $argumentDefinition = $this->compileServiceArgument($cleanArgument);

        } elseif (is_string($argument) && strpos($argument, Doppo::PARAMETER_PREFIX) === 0) {

            $cleanArgument = preg_replace('#^' . Doppo::PARAMETER_PREFIX . '{1}#', '', $argument);

            $argumentDefinition = $this->compileParameterArgument($cleanArgument);
        } else {

            $argumentDefinition = $this->compileValueArgument($argument);
        }

        return $argumentDefinition;
    }

    /**
     * Given a service argument value return its definition
     *
     * @param string $argumentValue Argument value
     *
     * @return ServiceArgument Service argument
     */
    protected function compileServiceArgument($argumentValue)
    {
        return new ServiceArgument($argumentValue);
    }

    /**
     * Given a parameter argument value return its definition
     *
     * @param string $argumentValue Argument value
     *
     * @return ParameterArgument Parameter argument
     */
    protected function compileParameterArgument($argumentValue)
    {
        return new ParameterArgument($argumentValue);
    }

    /**
     * Given a value argument value return its definition
     *
     * @param mixed $argumentValue Argument value
     *
     * @return ValueArgument Value argument
     */
    protected function compileValueArgument($argumentValue)
    {
        return new ValueArgument($argumentValue);
    }

    /**
     * Compile a parameter
     *
     * @param string $parameterName  Parameter name
     * @param string $parameterValue Parameter value
     */
    protected function compileParameter($parameterName, $parameterValue)
    {
        $this
            ->parameters
            ->addParameterDefinition(
                new ParameterDefinition(
                    $parameterName,
                    $parameterValue
                )
            );
    }

    /**
     * Check services arguments references
     *
     * This call has only sense if the service stack is built before. The why
     * of this methods is because now we have the correct acknowledgement about
     * all the services and parameters we will work with.
     *
     * We will now check that all service arguments have correct references.
     *
     * @throws DoppoServiceArgumentNotExistsException service argument not found
     */
    protected function checkServiceArgumentsReferences()
    {
        $this
            ->services
            ->each(function (ServiceDefinition $serviceDefinition) {

                $serviceDefinition
                    ->getArgumentChain()
                    ->each(function (Argument $argument) use ($serviceDefinition) {

                        $argumentValue = $argument->getValue();
                        if ($argument instanceof ServiceArgument) {

                            if (!$this->services->has($argumentValue)) {

                                throw new DoppoServiceArgumentNotExistsException(
                                    sprintf(
                                        'Service "%s" not found in "@%s" arguments list',
                                        $argumentValue,
                                        $serviceDefinition->getName()
                                    )
                                );
                            }
                        }

                        if ($argument instanceof ParameterArgument) {

                            if (!$this->parameters->has($argumentValue)) {

                                throw new DoppoServiceArgumentNotExistsException(
                                    sprintf(
                                        'Parameter "%s" not found in "@%s" arguments list',
                                        $argumentValue,
                                        $serviceDefinition->getName()
                                    )
                                );
                            }
                        }
                    });
            });
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
        $serviceDefinition = $this->services->get($serviceName);
        $serviceReflectionClass = new ReflectionClass($serviceDefinition->getClass());
        $serviceArguments = array();

        /**
         * Each argument is built recursively. If the argument is defined
         * as a service we will return the value of the get() call.
         *
         * Otherwise, if is defined as a parameter we will return the
         * parameter value
         *
         * Otherwise, we will treat the value as a plain value, not precessed.
         */
        $serviceDefinition
            ->getArgumentChain()
            ->each(function (Argument $argument) use (&$serviceArguments) {

                $argumentValue = $argument->getValue();
                if ($argument instanceof ServiceArgument) {

                    $serviceArguments[] = $this->get($argumentValue);
                } elseif ($argument instanceof ParameterArgument) {

                    $serviceArguments[] = $this->getParameter($argumentValue);
                } else {

                    $serviceArguments[] = $argumentValue;
                }
            });

        return $serviceReflectionClass->newInstanceArgs($serviceArguments);
    }

    /**
     * Get service instance
     *
     * @param string $serviceName Service Name
     *
     * @return mixed Service instance
     *
     * @throws DoppoNotCompiledException      Container not compiled yet
     * @throws DoppoServiceNotExistsException Service not found
     */
    public function get($serviceName)
    {
        if (!$this->compiled) {

            throw new DoppoNotCompiledException(
                'Container should be compiled before being used'
            );
        }

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
        if (!$this->services->has($serviceName)) {

            throw new DoppoServiceNotExistsException(
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
     * @throws DoppoNotCompiledException        Container not compiled yet
     * @throws DoppoParameterNotExistsException Parameter not found
     */
    public function getParameter($parameterName)
    {
        if (!$this->compiled) {

            throw new DoppoNotCompiledException(
                'Container should be compiled before being used'
            );
        }

        if (!$this->parameters->has($parameterName)) {

            throw new DoppoParameterNotExistsException(
                sprintf(
                    'Parameter "%s" not found',
                    $parameterName
                )
            );
        }

        return $this
            ->parameters
            ->get($parameterName)
            ->getValue();
    }
}
