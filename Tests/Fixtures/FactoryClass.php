<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class FactoryClass
{
    public function createWithRequiredArgument(\DateTime $date)
    {
    }

    public static function create()
    {
    }

    public function createNonStatic()
    {
    }
}
