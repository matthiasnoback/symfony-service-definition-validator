<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

class ClassWithTypeHintedDateTimeConstructorArgument
{
    public function __construct(\DateTime $date)
    {
    }
}
