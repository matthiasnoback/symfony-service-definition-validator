<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

interface ArgumentValidatorInterface
{
    public function validate(\ReflectionParameter $parameter, $argument);
}
