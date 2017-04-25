# Symfony Service Definition Validator

By Matthias Noback

[![Build Status](https://secure.travis-ci.org/matthiasnoback/symfony-service-definition-validator.png)](http://travis-ci.org/matthiasnoback/symfony-service-definition-validator)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/matthiasnoback/symfony-service-definition-validator/badges/quality-score.png?s=c04fb4a34fa5d4c27f1e4ef0d8794c07b3c40e91)](https://scrutinizer-ci.com/g/matthiasnoback/symfony-service-definition-validator/)
[![Latest Stable Version](https://poser.pugx.org/matthiasnoback/symfony-service-definition-validator/v/stable.png)](https://packagist.org/packages/matthiasnoback/symfony-service-definition-validator)

## Installation

Using Composer:

    php composer.phar require matthiasnoback/symfony-service-definition-validator ~1

## Problems the validator can spot

Using the service definition validator in this library, the following service definition
problems can be recognized:

- Non-existing classes
- Non-existing factory methods
- Method calls to non-existing methods
- Missing required arguments for constructors
- Non-public constructor methods
- Non-static factory methods
- Missing required arguments for method calls
- Type-hint mismatches for constructor arguments (array or class/interface)
- Type-hint mismatches for method call arguments (array or class/interface)
- Syntax errors in arguments that are
  [expressions](http://symfony.com/doc/current/book/service_container.html#using-the-expression-language)
- Expressions that cause errors when being evaluated

This will prevent lots of run-time problems, and will warn you about inconsistencies in your
service definitions early on.

### Reporting false-negatives

I've tested the validator with the latest Symfony Standard Edition which has (of course) only valid service definitions.
Please let me know if the validator fails inside your project when it should not have failed. When you report an issue,
please attach a copy of the error message and the relevant lines in ``app/cache/dev/appDevDebugProjectContainer.xml``.

## Usage

If you have an existing Symfony application and you want to ensure that the service definitions are always valid the easiest way is to add the compiler pass to your bundle as described [here](https://github.com/matthiasnoback/symfony-service-definition-validator#compiler-pass). That will validate your service definitions every time the bundle is compiled, which happens every single request if the cache is turned off (the default in debug mode).

If you want to validate the service definitions in phpunit for a Symfony project follow the steps [here](https://github.com/matthiasnoback/symfony-service-definition-validator#running-the-validator-in-phpunit).

### Service validator factory

You can use the stand-alone validator for single definitions:

```php
<?php

use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidatorFactory;

// an instance of Symfony\Component\DependencyInjection\ContainerBuilder
$containerBuilder = ...;

$validatorFactory = new ServiceDefinitionValidatorFactory();
$validator = $validatorFactory->create($containerBuilder);

// an instance of Symfony\Component\DependencyInjection\Definition
$definition = ...;

// will throw an exception for any validation error
$validator->validate($definition);
```

To process multiple definitions at once, wrap the validator inside a batch validator:

```php
<?php

use Matthias\SymfonyServiceDefinitionValidator\BatchServiceDefinitionValidator;
use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorFactory;

$batchValidator = new BatchServiceDefinitionValidator(
    $validator,
    new ValidationErrorFactory()
);

$errorList = $batchValidator->validate($serviceDefinitions);
```

The resulting error list will contain errors about problematic service definitions.

### Compiler pass

To check for the validity of all your service definitions at compile time, add the `ValidateServiceDefinitionsPass`
compiler pass to the `ContainerBuilder` instance:

```php
<?php

use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class SomeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(
                new ValidateServiceDefinitionsPass(),
                PassConfig::TYPE_AFTER_REMOVING
            );
        }
    }
}
```

This compiler pass will throw an exception. The message of this exception will contain a list
of invalid service definitions.

### Running the validator in PHPUnit
In Symfony, adding the compiler pass will validate your services each time the page is loaded in your browser or when you use a command. But what if you want to run the validator on demand using PHPUnit? To do this, first set up the compiler pass as explained above and then create a new PHPUnit test with this inside:
```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContainerServiceDefinitionsTest extends WebTestCase
{
    /**
     * @test
     */
    public function validateServiceDefinitions()
    {
        $kernel = static::createKernel();

        $kernel->boot();
    
        $bootedProperty = (new \ReflectionObject($kernel))
            ->getProperty('booted');
        $bootedProperty->setAccessible(true);
        self::assertTrue($bootedProperty->getValue($kernel));
    }
}
```

This simple functional test just boots up the symfony kernel using the "test" environment. If you look back to the code you added to set up the compiler pass you'll see that we enabled the validator for the "test" environment too. So this simple PHPUnit test will validate the services each time it is run.

In fact, if you already have functional tests you don't need this test, since the kernel will be booted (and therefore the services will be validated) in the other functional tests. The example test above is only needed if you don't already have any Symfony functional tests of your application.

### Configure the validator

Both ``ValidateServiceDefinitionsPass`` and ``ServiceDefinitionValidatorFactory`` accept a ``Configuration`` object.
It allows you to configure whether or not expression arguments should be evaluated. Since evaluating expressions can
cause all kinds of runtime errors, it is *off by default*, but you can easily turn it on:

```php
<?php

use Matthias\SymfonyServiceDefinitionValidator\Configuration;
use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;

$configuration = new Configuration();
$configuration->setEvaluateExpressions(true);

$compilerPass = new ValidateServiceDefinitionsPass($configuration);

// or

$validatorFactory = new ServiceDefinitionValidatorFactory($configuration);
```

### Fixing invalid service definitions in third-party bundles

When the validator finds a problem with one of the service definitions that is your own, you can of course fix the
problem yourself, but when the invalid service definition is defined in some other bundle, you can still fix problems by
dynamically modifying the service definition. First you need to create a compiler pass:

```php
<?php

namespace YourBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FixInvalidServiceDefinitionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // find the bad definition:

        $invalidDefinition = $container->findDefinition('the_service_id');

        // for example, fix the class
        $invalidDefinition->setClass('TheRightClass');
    }
}
```

After you have selected the invalid service definition, you can modify it in any way you like. For a list of everything
you can do with ``Definition`` objects, see
http://symfony.com/doc/master/components/dependency_injection/definitions.html.

Don't forget to register the compiler pass in your bundle class:

```php
<?php

namespace MyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new FixInvalidServiceDefinitionPass());
        }
    }
}
```

