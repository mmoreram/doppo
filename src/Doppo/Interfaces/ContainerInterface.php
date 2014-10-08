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

namespace Doppo\Interfaces;

use Doppo\Definition\ParameterDefinitionChain;
use Doppo\Definition\ServiceDefinitionChain;

/**
 * Interface ContainerInterface
 */
interface ContainerInterface
{
    /**
     * Return compiled service definitions
     *
     * @return ServiceDefinitionChain Compiled service definitions
     */
    public function getCompiledServiceDefinitions();

    /**
     * Return compiled parameter definitions
     *
     * @return ParameterDefinitionChain Compiled parameter definitions
     */
    public function getCompiledParameterDefinitions();

    /**
     * Compile container
     */
    public function compile();

    /**
     * Get service instance
     *
     * @param string $serviceName Service Name
     *
     * @return mixed Service instance
     */
    public function get($serviceName);

    /**
     * Get parameter value
     *
     * @param string $parameterName Parameter Name
     *
     * @return mixed Parameter value
     */
    public function getParameter($parameterName);
}
