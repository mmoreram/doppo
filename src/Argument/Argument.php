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

namespace Doppo\Argument;

/**
 * Class Argument
 */
class Argument
{
    /**
     * @var string
     *
     * Value
     */
    protected $value;

    /**
     * Construct method
     *
     * @param string $value Value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string Argument value
     */
    public function getValue()
    {
        return $this->value;
    }
}
