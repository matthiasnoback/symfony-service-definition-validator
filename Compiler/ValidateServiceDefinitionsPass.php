<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Compiler;

use Matthias\SymfonyServiceDefinitionValidator\BatchServiceDefinitionValidator;
use Matthias\SymfonyServiceDefinitionValidator\Error\Printer\SimpleErrorListPrinter;
use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorFactory;
use Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidServiceDefinitionsException;
use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidatorFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ValidateServiceDefinitionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceDefinitions = $container->getDefinitions();

        $validatorFactory = new ServiceDefinitionValidatorFactory();
        $validator = $validatorFactory->create($container);

        $batchValidator = new BatchServiceDefinitionValidator(
            $validator,
            new ValidationErrorFactory()
        );

        $errorList = $batchValidator->validate($serviceDefinitions);

        if (count($errorList) === 0) {
            return;
        }

        $errorListPrinter = new SimpleErrorListPrinter();
        $message = $errorListPrinter->printErrorList($errorList);

        throw new InvalidServiceDefinitionsException($message);
    }
}
