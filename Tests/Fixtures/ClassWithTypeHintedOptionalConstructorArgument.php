<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithTypeHintedOptionalConstructorArgument
{
    public function __construct(ExpectedClass $expected = null)
    {
    }
}
