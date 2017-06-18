<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithTypeHintedAliasConstructorArgument
{
    public function __construct(\AliasedExpectedClass $expected)
    {
    }
}
