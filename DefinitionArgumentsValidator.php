<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

class DefinitionArgumentsValidator implements DefinitionArgumentsValidatorInterface
{
    private $constructorResolver;
    private $argumentsValidator;

    public function __construct(
        ConstructorResolverInterface $constructorResolver,
        ArgumentsValidatorInterface $argumentsValidator
    ) {
        $this->constructorResolver = $constructorResolver;
        $this->argumentsValidator = $argumentsValidator;
    }

    public function validate(Definition $definition)
    {
        if ($definition->isAbstract()) {
            return;
        }

        if ($definition->isSynthetic()) {
            return;
        }

        $constructor = $this->constructorResolver->resolve($definition);
        if ($constructor === null) {
            return;
        }

        $arguments = $definition->getArguments();

        $this->argumentsValidator->validate($constructor, $arguments);
    }
}
