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

use Closure;

/**
 * Class ArgumentChain
 */
class ArgumentChain
{
    /**
     * @var array
     *
     * Arguments
     */
    private $arguments = array();

    /**
     * Add argument
     *
     * @param Argument $argument Argument to be added
     *
     * @return $this self Object
     */
    public function addArgument(Argument $argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Get arguments
     *
     * @return array Arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Applies the given function to each element in the collection
     *
     * @param Closure $function Function to apply
     */
    public function each(Closure $function)
    {
        foreach ($this->getArguments() as $argument) {

            $function($argument);
        }
    }
}
