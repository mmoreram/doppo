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

namespace Doppo\Tests\Abstracts;

use Exception;
use PHPUnit_Framework_TestCase;

use Doppo\Interfaces\DependencyInjectionContainerInterface;

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
     * @return DependencyInjectionContainerInterface Doppo
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
    public function testCreate()
    {
        $this->getDoppoInstance($this->standardConfiguration);
    }

    /**
     * Test error compilation
     *
     * @dataProvider dataCompilationFailure
     * @expectedException Exception
     */
    public function testCreateFailure($config)
    {
        $this->getDoppoInstance($config);
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

        $this->assertInstanceOf('Doppo\Tests\Data\Foo', $doppo->get('foo'));
        $this->assertInstanceOf('Doppo\Tests\Data\Goo', $doppo->get('goo'));
        $this->assertInstanceOf('Doppo\Tests\Data\Moo', $doppo->get('moo'));
    }

    /**
     * Testing get method with bad values
     *
     * @expectedException Exception
     */
    public function testGetFail()
    {
        $doppo = $this->getDoppoInstance(array(
            'moo' => array(
                'class' => 'Doppo\Tests\Data\Moo'
            ),
        ));

        $doppo->get('foo');
    }

    /**
     * Testing get method with good values
     */
    public function testGetParameterOK()
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);

        $this->assertEquals('my.value', $doppo->getParameter('my.parameter'));
    }

    /**
     * Testing get method with bad values
     *
     * @dataProvider dataGetParameterFail
     * @expectedException Exception
     */
    public function testGetParameterFail($parameterName)
    {
        $doppo = $this->getDoppoInstance($this->standardConfiguration);
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
}
