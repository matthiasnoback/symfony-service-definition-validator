<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class_alias('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ExpectedClass', 'AliasedExpectedClass');

class ClassWithTypeHintedAliasConstructorArgument
{
    public function __construct(\AliasedExpectedClass $expected)
    {
    }
}
