<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Symfony\Component\DependencyInjection\Definition;

class ResultingClassResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsClassOfNormalDefinition()
    {
        $resolver = new ResultingClassResolver();

        $definition = new Definition('stdClass');
        $resolvedClass = $resolver->resolve($definition);
        $this->assertSame('stdClass', $resolvedClass);
    }
}
