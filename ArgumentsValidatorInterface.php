<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

interface ArgumentsValidatorInterface
{
    public function validate(\ReflectionFunctionAbstract $method, array $arguments);
}
