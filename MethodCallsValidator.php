<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Symfony\Component\DependencyInjection\Definition;

class MethodCallsValidator implements MethodCallsValidatorInterface
{
    private $resultingClassResolver;
    private $argumentsValidator;

    public function __construct(
        ResultingClassResolverInterface $resultingClassResolver,
        ArgumentsValidatorInterface $argumentsValidator
    )
    {
        $this->resultingClassResolver = $resultingClassResolver;
        $this->argumentsValidator = $argumentsValidator;
    }

    public function validate(Definition $definition)
    {
        $resultingClass = $this->resultingClassResolver->resolve($definition);
        if ($resultingClass === null) {
            // cannot validate method calls for definitions with unknown classes
            return;
        }

        $methodCalls = $definition->getMethodCalls();

        foreach ($methodCalls as $methodCall) {
            list($method, $arguments) = $methodCall;
            $this->validateMethodCall($resultingClass, $method, $arguments);
        }
    }

    private function validateMethodCall($class, $method, array $arguments)
    {
        if (!method_exists($class, $method)) {
            throw new MethodNotFoundException($class, $method);
        }

        $reflectionMethod = new \ReflectionMethod($class, $method);

        $this->argumentsValidator->validate($reflectionMethod, $arguments);
    }
}
