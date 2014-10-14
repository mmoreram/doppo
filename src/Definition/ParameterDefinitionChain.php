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
 * Class ParameterDefinitionChain
 */
class ParameterDefinitionChain
{
    /**
     * @var array
     *
     * ParameterDefinitions
     */
    private $parameterDefinitions = array();

    /**
     * Add parameterDefinition
     *
     * @param ParameterDefinition $parameterDefinition ParameterDefinition to be added
     *
     * @return $this self Object
     */
    public function addParameterDefinition(ParameterDefinition $parameterDefinition)
    {
        $this->parameterDefinitions[$parameterDefinition->getName()] = $parameterDefinition;

        return $this;
    }

    /**
     * Get parameterDefinitions
     *
     * @return array ParameterDefinitions
     */
    public function getParameterDefinitions()
    {
        return $this->parameterDefinitions;
    }

    /**
     * Applies the given function to each element in the collection
     *
     * @param Closure $function Function to apply
     */
    public function each(Closure $function)
    {
        foreach ($this->getParameterDefinitions() as $parameterDefinition) {

            $function($parameterDefinition);
        }
    }

    /**
     * Has Parameter Definition
     *
     * @param string $parameterName Parameter name
     *
     * @return boolean Parameter is defined inside this chain
     */
    public function has($parameterName)
    {
        return is_string($parameterName) && array_key_exists($parameterName, $this->parameterDefinitions);
    }

    /**
     * Return required parameter
     *
     * @param string $parameterName Parameter name
     *
     * @return ParameterDefinition Parameter required
     */
    public function get($parameterName)
    {
        return $this->parameterDefinitions[$parameterName];
    }
}
