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

namespace Doppo\Definition;

use Closure;

/**
 * Class ServiceDefinitionChain
 */
class ServiceDefinitionChain
{
    /**
     * @var array
     *
     * ServiceDefinitions
     */
    private $serviceDefinitions = array();

    /**
     * Add serviceDefinition
     *
     * @param ServiceDefinition $serviceDefinition ServiceDefinition to be added
     *
     * @return $this self Object
     */
    public function addServiceDefinition(ServiceDefinition $serviceDefinition)
    {
        $this->serviceDefinitions[$serviceDefinition->getName()] = $serviceDefinition;

        return $this;
    }

    /**
     * Get serviceDefinitions
     *
     * @return array ServiceDefinitions
     */
    public function getServiceDefinitions()
    {
        return $this->serviceDefinitions;
    }

    /**
     * Applies the given function to each element in the collection
     *
     * @param Closure $function Function to apply
     */
    public function each(Closure $function)
    {
        foreach ($this->getServiceDefinitions() as $serviceDefinition) {

            $function($serviceDefinition);
        }
    }

    /**
     * Has Service Definition
     *
     * @param string $serviceName Service name
     *
     * @return boolean Service is defined inside this chain
     */
    public function has($serviceName)
    {
        return is_string($serviceName) && array_key_exists($serviceName, $this->serviceDefinitions);
    }

    /**
     * Return required service
     *
     * @param string $serviceName Service name
     *
     * @return ServiceDefinition Service required
     */
    public function get($serviceName)
    {
        return $this->serviceDefinitions[$serviceName];
    }
}
