<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithTypeHintedConstructorArgument
{
    public function __construct(ExpectedClass $expected)
    {
    }
}
