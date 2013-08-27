<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException;

class ArgumentsValidator implements ArgumentsValidatorInterface
{
    private $argumentValidator;

    public function __construct(ArgumentValidatorInterface $argumentValidator)
    {
        $this->argumentValidator = $argumentValidator;
    }

    public function validate(\ReflectionMethod $method, array $arguments)
    {
        foreach ($method->getParameters() as $parameterNumber => $parameter) {
            $argument = $this->resolveArgument($parameterNumber, $parameter, $arguments);

            $this->argumentValidator->validate($parameter, $argument);
        }
    }

    /**
     * Find the argument by the numeric index of the given parameter
     */
    private function resolveArgument($parameterIndex, \ReflectionParameter $parameter, array $arguments)
    {
        if (array_key_exists($parameterIndex, $arguments)) {
            return $arguments[$parameterIndex];
        }

        if (!$parameter->isOptional()) {
            throw new MissingRequiredArgumentException($parameter->getDeclaringClass()->getName(), $parameter->getName(
            ));
        }

        return null;
    }
}
