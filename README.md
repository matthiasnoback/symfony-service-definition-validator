# Symfony Service Definition Validator

By Matthias Noback

[![Build Status](https://secure.travis-ci.org/matthiasnoback/symfony-service-definition-validator.png)](http://travis-ci.org/matthiasnoback/symfony-service-definition-validator)

## Installation

Using Composer:

    php composer.phar require matthiasnoback/symfony-service-definition-validator 0.*

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

This will prevent lots of run-time problems, and will warn you about inconsistencies in your
service definitions early on.

### Reporting false-negatives

I've tested the validator with the latest Symfony Standard Edition which has (of course) only valid service definitions.
Please let me know if the validator fails inside your project when it should not have failed. When you report an issue,
please attach a copy of the error message and the relevant lines in ``app/cache/dev/appDevDebugProjectContainer.xml``.

## Usage

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
        $container->addCompilerPass(
            new ValidateServiceDefinitionsPass(),
            PassConfig::TYPE_AFTER_REMOVING
        );
    }
}
```

This compiler pass will throw an exception. The message of this exception will contain a list
of invalid service definitions.
