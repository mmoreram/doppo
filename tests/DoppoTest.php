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

namespace Doppo\Tests;

use Doppo\Doppo;
use Doppo\Interfaces\ContainerInterface;
use Doppo\Tests\Abstracts\AbstractDoppoTest;
use Doppo\Tests\Data\Moo;

/**
 * Class DoppoTest
 */
class DoppoTest extends AbstractDoppoTest
{
    /**
     * Return instance of doppo given a configuration
     *
     * @param array $configuration Configuration
     *
     * @return ContainerInterface Doppo
     */
    public function getDoppoInstance(array $configuration)
    {
        return new Doppo($configuration, true);
    }

    /**
     * Test the use of "return built instance" on get method
     */
    public function testGetSeveralTimes()
    {
        /**
         * @var Doppo $doppo
         */
        $doppo = $this
            ->getMockBuilder('Doppo\Doppo')
            ->setMethods(array('buildExistentService'))
            ->setConstructorArgs(array($this->standardConfiguration, false))
            ->getMock();

        $doppo
            ->expects($this->once())
            ->method('buildExistentService')
            ->will($this->returnValue(new Moo()));

        $doppo->compile();
        $doppo->get('moo');
        $doppo->get('moo');
    }
}
