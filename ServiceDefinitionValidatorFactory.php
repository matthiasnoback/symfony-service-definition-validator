<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceDefinitionValidatorFactory implements ServiceDefinitionValidatorFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder)
    {
        $resultingClassResolver = new ResultingClassResolver($containerBuilder);

        $constructorResolver = new ConstructorResolver($containerBuilder, $resultingClassResolver);

        $argumentValidator = new ArgumentValidator($containerBuilder, $resultingClassResolver);
        $argumentsValidator = new ArgumentsValidator($argumentValidator);

        $definitionArgumentsValidator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);

        $methodCallsValidator = new MethodCallsValidator($resultingClassResolver, $argumentsValidator);

        $validator = new ServiceDefinitionValidator($containerBuilder, $definitionArgumentsValidator, $methodCallsValidator);

        return $validator;
    }

}
