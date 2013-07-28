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

    public function validate(\ReflectionMethod $method, $arguments)
    {
        foreach ($method->getParameters() as $parameterNumber => $parameter) {
            $argument = $this->resolveArgument($parameterNumber, $parameter, $arguments);
            $this->argumentValidator->validate($parameter, $argument);
        }
    }

    /**
     * Find the argument by the numeric index of the given parameter
     *
     * @param $parameterIndex
     * @param \ReflectionParameter $parameter
     * @param array $definitionArguments
     * @return mixed
     * @throws Exception\MissingRequiredArgumentException
     */
    private function resolveArgument($parameterIndex, \ReflectionParameter $parameter, array $definitionArguments)
    {
        if (array_key_exists($parameterIndex, $definitionArguments)) {
            return $definitionArguments[$parameterIndex];
        }

        if (!$parameter->isOptional()) {
            throw new MissingRequiredArgumentException($parameter->getDeclaringClass()->getName(), $parameter->getName());
        }

        // TODO test this
        return null;
    }
}
