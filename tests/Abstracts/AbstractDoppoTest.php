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

namespace Doppo\Tests\Abstracts;

use PHPUnit_Framework_TestCase;

use Doppo\Interfaces\ContainerInterface;

/**
 * Abstract Class AbstractDoppoTest
 */
abstract class AbstractDoppoTest extends PHPUnit_Framework_TestCase
{
    /**
     * Return instance of doppo given a configuration
     *
     * @param array $configuration Configuration
     *
     * @return ContainerInterface Doppo
     */
    abstract public function getDoppoInstance(array $configuration);

    /**
     * @var array
     *
     * Standard Container definition
     */
    protected $standardConfiguration = array(
        'foo'          => array(
            'class'     => '\Doppo\Tests\Data\Foo',
            'arguments' => array(
                'value1',
                array('value2'),
                '~my.parameter',
            ),
        ),
        'goo'          => array(
            'class'     => 'Doppo\Tests\Data\Goo',
            'arguments' => array(
                '@foo',
                '@moo',
            ),
        ),
        'moo'          => array(
            'class' => 'Doppo\Tests\Data\Moo'
        ),
        'my.parameter' => 'my.value',
    );

    /**
     * Test container compilation
     */
    public function testCompile()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->compile();
    }

    /**
     * Test container compilation more than once
     *
     * @expectedException \Doppo\Exception\DoppoAlreadyCompiledException
     */
    public function testCompileMoreThanOnce()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->compile();
        $doppo->compile();
    }

    /**
     * Test error compilation
     *
     * @dataProvider dataCompilationFailure
     * @expectedException \Doppo\Exception\Abstracts\DoppoCompilationException
     */
    public function testCompileFailure($config)
    {
        $doppo = $this->getDoppoInstance($config);
        $doppo->compile();
    }

    /**
     * Data for testCompilationFailure
     *
     * @return array
     */
    public function dataCompilationFailure()
    {
        return array(
            array(
                array(
                    'foo' => array(
                        'class' => 'Non\Existant\Class',
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => null,
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => '',
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => false,
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => true,
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => 'Doppo\Tests\Data\Foo',
                        'arguments' => array(
                            '@bee'
                        )
                    ),
                )
            ),
            array(
                array(
                    'foo' => array(
                        'class' => 'Doppo\Tests\Data\Foo',
                        'arguments' => array(
                            '~non.existing.parameter'
                        )
                    ),
                )
            ),
        );
    }

    /**
     * Testing get method with good values
     */
    public function testGetOK()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->compile();

        $this->assertInstanceOf('Doppo\Tests\Data\Foo', $doppo->get('foo'));
        $this->assertInstanceOf('Doppo\Tests\Data\Goo', $doppo->get('goo'));
        $this->assertInstanceOf('Doppo\Tests\Data\Moo', $doppo->get('moo'));
    }

    /**
     * Testing get method with bad values
     *
     * @expectedException \Doppo\Exception\DoppoServiceNotExistsException
     */
    public function testGetFail()
    {
        $doppo = $this->getDoppoInstance(array(
            'moo' => array(
                'class' => 'Doppo\Tests\Data\Moo'
            ),
        ));

        $doppo->compile();
        $doppo->get('foo');
    }

    /**
     * Testing get method with good values
     */
    public function testGetParameterOK()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->compile();

        $this->assertEquals('my.value', $doppo->getParameter('my.parameter'));
    }

    /**
     * Testing get method with bad values
     *
     * @dataProvider dataGetParameterFail
     * @expectedException \Doppo\Exception\DoppoParameterNotExistsException
     */
    public function testGetParameterFail($parameterName)
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->compile();
        $doppo->getParameter($parameterName);
    }

    /**
     * Data for testGetParameterFail
     *
     * @return array
     */
    public function dataGetParameterFail()
    {
        return array(
            array('my.nonexisting.parameter'),
            array(true),
            array(false),
            array(null),
        );
    }

    /**
     * Testing get method with a non-compiled container
     *
     * @expectedException \Doppo\Exception\DoppoNotCompiledException
     */
    public function testGetWithoutCompile()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->get('foo');
    }

    /**
     * Testing getParameter method with a non-compiled container
     *
     * @expectedException \Doppo\Exception\DoppoNotCompiledException
     */
    public function testGetParameterWithoutCompile()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
        $doppo->getParameter('my.parameter');
    }
}
