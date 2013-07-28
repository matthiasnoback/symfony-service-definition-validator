<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithNonPublicConstructor
{
    private function __construct()
    {
    }
}
