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

namespace Doppo\Tests;

use Exception;
use Psr\Log\LoggerInterface;

use Doppo\Doppo;
use Doppo\Interfaces\DependencyInjectionContainerInterface;
use Doppo\LoggedDoppo;
use Doppo\Tests\Abstracts\AbstractDoppoTest;

/**
 * Class LoggedDoppoTest
 */
class LoggedDoppoTest extends AbstractDoppoTest
{
    /**
     * @var LoggerInterface
     *
     * Logger
     */
    protected $logger;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
    }

    /**
     * Return instance of doppo given a configuration
     *
     * @param array $configuration Configuration
     *
     * @return DependencyInjectionContainerInterface Doppo
     */
    public function getDoppoInstance(array $configuration)
    {
        return new LoggedDoppo(
            new Doppo($configuration),
            $this->logger
        );
    }

    /**
     * Test if logger is called when get() is called
     */
    public function testLogCallOnGetOK()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this
            ->logger
            ->expects($this->once())
            ->method('debug');

        $this
            ->logger
            ->expects($this->never())
            ->method('error');

        $doppo->get('foo');
    }

    /**
     * Test if logger is called when get() is called
     *
     * @expectedException Exception
     */
    public function testLogCallOnGetFail()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this
            ->logger
            ->expects($this->once())
            ->method('debug');

        $this
            ->logger
            ->expects($this->once())
            ->method('error');

        $doppo->get('zoo');
    }
}
