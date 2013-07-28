<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

interface ArgumentsValidatorInterface
{
    public function validate(\ReflectionMethod $method, $arguments);
}
