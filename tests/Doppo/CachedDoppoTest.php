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

use Doppo\CachedDoppo;
use Doppo\Doppo;
use Doppo\Interfaces\DependencyInjectionContainerInterface;
use Doppo\Tests\Abstracts\AbstractDoppoTest;

/**
 * Class CachedDoppoTest
 */
class CachedDoppoTest extends AbstractDoppoTest
{
    /**
     * Return instance of doppo given a configuration
     *
     * @param array $configuration Configuration
     *
     * @return DependencyInjectionContainerInterface Doppo
     */
    public function getDoppoInstance(array $configuration)
    {
        return new CachedDoppo(
            new Doppo($configuration),
            sys_get_temp_dir() . '/doppo.cache.php',
            true
        );
    }
}
