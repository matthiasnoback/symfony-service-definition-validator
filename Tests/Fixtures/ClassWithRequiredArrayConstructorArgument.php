<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithRequiredArrayConstructorArgument
{
    public function __construct(array $options)
    {
    }
}
