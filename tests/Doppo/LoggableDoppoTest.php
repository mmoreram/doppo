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
use Doppo\Interfaces\ContainerInterface;
use Doppo\LoggableDoppo;
use Doppo\Tests\Abstracts\AbstractDoppoTest;

/**
 * Class LoggableDoppoTest
 */
class LoggableDoppoTest extends AbstractDoppoTest
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
     * @return ContainerInterface Doppo
     */
    public function getDoppoInstance(array $configuration)
    {
        return new LoggableDoppo(
            new Doppo($configuration),
            $this->logger
        );
    }

    /**
     * Test if error logger is called when get() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetFail()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $doppo->compile();
        $doppo->get('zoo');
    }

    /**
     * Test if error logger is called when getParameter() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetParameterFail()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $doppo->compile();
        $doppo->getParameter('another.parameter');
    }

    /**
     * Test if error logger is called when compile is called more than once
     *
     * @expectedException Exception
     */
    public function testLogCallOnCompileMoreThanOnce()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $doppo->compile();
        $doppo->compile();
    }
}
