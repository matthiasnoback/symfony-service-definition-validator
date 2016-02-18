<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithOptionalArrayConstructorArgument
{
    public function __construct(array $options = null)
    {
    }
}
