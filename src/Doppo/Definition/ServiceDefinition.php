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

namespace Doppo\Definition;

use Doppo\Argument\ArgumentChain;

/**
 * Class ServiceDefinition
 */
class ServiceDefinition
{
    /**
     * @var string
     *
     * Scope public
     */
    const SCOPE_PUBLIC = 'public';

    /**
     * @var string
     *
     * Scope private
     */
    const SCOPE_PRIVATE = 'private';

    /**
     * @var string
     *
     * Parameter name
     */
    private $name;

    /**
     * @var string
     *
     * Service class
     */
    private $class;

    /**
     * @var ArgumentChain
     *
     * Argument chain
     */
    private $argumentChain;

    /**
     * @var String
     *
     * Scope
     */
    private $scope = self::SCOPE_PUBLIC;

    /**
     * Construct method
     *
     * @param string        $name          Service name
     * @param string        $class         Service class
     * @param ArgumentChain $argumentChain Argument chain
     */
    public function __construct(
        $name,
        $class,
        ArgumentChain $argumentChain
    )
    {
        $this->name = $name;
        $this->class = $class;
        $this->argumentChain = $argumentChain;
    }

    /**
     * Get ArgumentChain
     *
     * @return ArgumentChain ArgumentChain
     */
    public function getArgumentChain()
    {
        return $this->argumentChain;
    }

    /**
     * Get Class
     *
     * @return string Class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get Name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets Scope
     *
     * @param String $scope Scope
     *
     * @return $this Self object
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get Scope
     *
     * @return String Scope
     */
    public function getScope()
    {
        return $this->scope;
    }
}
