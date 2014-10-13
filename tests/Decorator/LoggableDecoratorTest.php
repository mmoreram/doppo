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

namespace Doppo\Tests\Decorator;

use Exception;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;

use Doppo\Decorator\LoggableDecorator;
use Doppo\Interfaces\ContainerInterface;

/**
 * Class LoggableDecoratorTest
 */
class LoggableDecoratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface
     *
     * Logger
     */
    protected $logger;

    /**
     * @var ContainerInterface
     *
     * Container
     */
    private $container;

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
     * @param boolean $debug Debug mode
     *
     * @return LoggableDecorator Decorator
     */
    public function getDoppoInstance($debug)
    {
        $this->container = $this->getMock('Doppo\Interfaces\ContainerInterface');

        $this->container
            ->expects($this->any())
            ->method('isDebug')
            ->will($this->returnValue($debug));

        return new LoggableDecorator(
            $this->container,
            $this->logger
        );
    }

    /**
     * Test if error logger is called when get() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetFailDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(true);
        $this
            ->container
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $loggerDecorator->get('zoo');
    }

    /**
     * Test if error logger is called when getParameter() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetParameterFailDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(true);
        $this
            ->container
            ->expects($this->once())
            ->method('getParameter')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $loggerDecorator->getParameter('another.parameter');
    }

    /**
     * Test if error logger is called when compile is called more than once
     *
     * @expectedException Exception
     */
    public function testLogCallOnCompileMoreThanOnceDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(true);
        $this
            ->container
            ->expects($this->once())
            ->method('compile')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $loggerDecorator->compile();
    }

    /**
     * Test if error logger is called when get() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetFailNoDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(false);
        $this
            ->container
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->never())
            ->method('error');

        $loggerDecorator->get('zoo');
    }

    /**
     * Test if error logger is called when getParameter() fails
     *
     * @expectedException Exception
     */
    public function testErrorLogCallOnGetParameterFailNoDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(false);
        $this
            ->container
            ->expects($this->once())
            ->method('getParameter')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->never())
            ->method('error');

        $loggerDecorator->getParameter('another.parameter');
    }

    /**
     * Test if error logger is called when compile is called more than once
     *
     * @expectedException Exception
     */
    public function testLogCallOnCompileMoreThanOnceNoDebug()
    {
        $loggerDecorator = $this->getDoppoInstance(false);
        $this
            ->container
            ->expects($this->once())
            ->method('compile')
            ->will($this->throwException(new Exception()));

        $this
            ->logger
            ->expects($this->never())
            ->method('error');

        $loggerDecorator->compile();
    }
}
