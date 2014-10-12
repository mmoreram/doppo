Doppo, Dependency Injection Container for PHP
=============================================

[![Build Status](https://travis-ci.org/mmoreram/doppo.svg)](https://travis-ci.org/mmoreram/doppo)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/908f5de1-9aeb-41d9-9440-0e65a5390fbe/mini.png)](https://insight.sensiolabs.com/projects/908f5de1-9aeb-41d9-9440-0e65a5390fbe)
[![Latest Stable Version](https://poser.pugx.org/mmoreram/doppo/v/stable.png)](https://packagist.org/packages/mmoreram/doppo)
[![Latest Unstable Version](https://poser.pugx.org/mmoreram/doppo/v/unstable.png)](https://packagist.org/packages/mmoreram/doppo)
[![License](https://poser.pugx.org/mmoreram/doppo/license.png)](https://packagist.org/packages/mmoreram/doppo)

Oh, no way... another Dependency Injection written in PHP?

Well, in fact it is, but the purpose of this project is not to be used in
production, but discuss some interesting topics about how is the best approach
to build a Dependency Injection Container.

Doppo is a basic DIC implementation, with some features (nothing new, just what
I think is the most important and useful) implemented. Is developed using TDD,
with several refactoring iterations through many architectural changes.

So, what is all about? Let's take a look at the features.

Container
---------

Doppo container needs to be built with two required parameters. First of all we
need to define the configuration we want to compile, and second, we need to
define the debug flag.

``` php
use Doppo\Doppo;

$configuration = [];
$debug = true;

$doppo = new Doppo(
    $configuration,
    $debug
);
```

This is an empty Doppo container, we can see that the configuration is empty,
and is created in debug mode. This information is not modifiable anymore, so is
like a fingerprint for a container instance.

    We could not define this object as immutable because when is compiled, the
    object's state changes from an external point of view.

The `$configuration` value is what defines how this container has to be compiled
and how all defined services must be built. Let's see the full definition.

Definition
----------

``` php
$configuration = array(
    'my_service' => array(
        'class' => 'My\Class\Namespace',
        'arguments' => array(
            '@my_other_service',
            '~my_parameter',
            'simple_value',
        )
    ),
    'my_other_service' => array(
        'class' => 'My\Class\Namespace',
    ),
    'my_parameter' => 'parameter_value',
);
```

Configuration allow two types of elements: Services and Parameters. The
difference between them from the point of view of the DIC is that any service
definition must contain a class value, so if the definition is an array, and the
`class` key exists, this will be treated as a service.

Otherwise, will be treated as a parameter.

As you can see, when we define the arguments of a service, we can refer to
another service using the prefix `@`, we can also refer to a parameter value
using the prefix `~` or we can just pass a plain value, like a string, an array
or an object.

The only way of passing dependencies to a service is by its constructor. Some
other Containers allow you to build a service using setters or public variables,
but this one does not. Considering that a service dependency must be injected
in the constructor, otherwise is not a dependency but a configuration,
container should only know about building, not configuring, at least while not
implemented specifically.

Compile
-------

Once the container is built with a configuration and a debug mode, we must
compile it. At that point, container will build internally a structure to serve
properly service instances and parameter values.

``` php
use Doppo\Doppo;

$configuration = array(
    'my_service' => array(
        'class' => 'My\Class\Namespace',
        'arguments' => array(
            '@my_other_service',
            '~my_parameter',
            'simple_value',
        )
    ),
    'my_other_service' => array(
        'class' => 'My\Class\Namespace',
    ),
    'my_parameter' => 'parameter_value',
);
$debug = true;

$doppo = new Doppo(
    $configuration,
    $debug
);

$doppo->compile();
```

The container compilation can only be done once. If we compile a compiled
container, an Exception is thrown.

Usage
-----

Once the container is compiled you can retrieve any defined service instance by
using the method `get` and any defined parameter value using the method
`getParameter`.

``` php

$myServiceInstance = $doppo->get('my_service');
$myParameterValue = $doppo->getParameter('my_parameter');
```

When you retrieve a service instance, this one is only build once. It means that
internally, when any service is required and built, the resultant instance, just
before returning it, is stored locally. When the service is called again,
existent instance will be returned instead of building it again.

Cache
-----

The problem here is that the container is built and compiled every time is
needed. This means that all the container will be processed and checked in every
execution, what makes it very inefficient.

This package also provides an extension of the main class Doppo. Is called
CacheableDoppo and is built like this.

``` php
use Doppo\Doppo;

$configuration = [];
$debug = true;
$cachePath = '/tmp/doppo.cache.php';

$doppo = new Doppo(
    $configuration,
    $debug,
    $cachePath
);
```

This new class works as previous one, both implement same interface
`ContainerInterface`, but this one adds a caching layer above the first one.
When the container is compiled, a cache file is built and stored where specified
as the last constructor parameter.

Each time the container needs to be built, if the cache file is already created,
this one will be loaded providing a set of methods defining all the service
construction specification.

Decorators
----------

The container can also be decorated. A decorator class must also implement
`ContainerInterface` and can add some behavior to specified interface without
changing old implementation.

### LoggableDecorator

This decorator logs, depending on the container debug mode, all external
interaction with the public container API.

``` php
use Doppo\Doppo;

$configuration = [];
$debug = true;
$cachePath = '/tmp/doppo.cache.php';
$logger = new Logger();

$doppo = new LoggableDecorator(
    new Doppo(
        $configuration,
        $debug,
        $cachePath
    ),
    $logger
);
```

Logger instance must implements `Psr\Log\LoggerInterface` provided by the
package `Psr\Log`. You can see some public implementations in these
[Package list](https://packagist.org/providers/psr/log-implementation)
