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

/**
 * Class ParameterDefinition
 */
class ParameterDefinition
{
    /**
     * @var string
     *
     * Parameter name
     */
    private $name;

    /**
     * @var string
     *
     * Parameter value
     */
    private $value;

    /**
     * Construct method
     *
     * @param string $name  Parameter name
     * @param mixed  $value Parameter value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
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
     * Get Value
     *
     * @return mixed Value
     */
    public function getValue()
    {
        return $this->value;
    }
}
