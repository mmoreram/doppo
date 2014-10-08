<?php

/**
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 */

namespace Doppo\Tests;

use Doppo\CacheableDoppo;
use Doppo\Doppo;
use Doppo\Interfaces\ContainerInterface;
use Doppo\LoggableDoppo;
use Doppo\Tests\Abstracts\AbstractDoppoTest;

/**
 * Class AllTest
 */
class AllTest extends AbstractDoppoTest
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
        return new CacheableDoppo(
            new LoggableDoppo(
                new Doppo($configuration),
                $this->getMock('Psr\Log\LoggerInterface')
            ),
            sys_get_temp_dir() . '/doppo.cache.php',
            true
        );
    }
}
