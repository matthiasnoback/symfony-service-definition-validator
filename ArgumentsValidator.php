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
            if (array_key_exists($parameterNumber, $arguments)) {
                $this->argumentValidator->validate($parameter, $arguments[$parameterNumber]);
            } else {
                if ($this->shouldParameterHaveAnArgument($parameter)) {
                    throw new MissingRequiredArgumentException(
                        $parameter->getDeclaringClass()->getName(),
                        $parameter->getName()
                    );
                }
            }
        }
    }

    private function shouldParameterHaveAnArgument(\ReflectionParameter $parameter)
    {
        if ($parameter->isOptional()) {
            // as far as I know not available for user-land arguments
            return false;
        }

        if ($parameter->isDefaultValueAvailable()) {
            // e.g. $username = 'root'
            return false;
        }

        if ($parameter->getClass() && $parameter->allowsNull()) {
            // e.g. LoggerInterface $logger = null
            return false;
        }

        return true;
    }
}
