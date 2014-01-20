<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceDefinitionValidatorFactory implements ServiceDefinitionValidatorFactoryInterface
{
    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        $this->configuration = $configuration;
    }

    public function create(ContainerBuilder $containerBuilder)
    {
        $resultingClassResolver = new ResultingClassResolver($containerBuilder);

        $constructorResolver = new ConstructorResolver($containerBuilder, $resultingClassResolver);

        $argumentValidator = new ArgumentValidator(
            $containerBuilder,
            $resultingClassResolver,
            $this->configuration->getEvaluateExpressions()
        );
        $argumentsValidator = new ArgumentsValidator($argumentValidator);

        $definitionArgumentsValidator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);

        $methodCallsValidator = new MethodCallsValidator($resultingClassResolver, $argumentsValidator);

        $validator = new ServiceDefinitionValidator($containerBuilder, $definitionArgumentsValidator, $methodCallsValidator);

        return $validator;
    }
}
