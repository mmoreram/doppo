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

use Doppo\CacheableDoppo;
use Doppo\Interfaces\ContainerInterface;
use Doppo\Tests\Abstracts\AbstractDoppoTest;

/**
 * Class CachedDoppoTest
 */
class CacheableDoppoTest extends AbstractDoppoTest
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
            $configuration,
            true,
            sys_get_temp_dir() . '/doppo.cache.php'
        );
    }
}
