<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

interface ResultingClassResolverInterface
{
    /**
     * Return the class that will eventually be instantiated for this definition
     *
     * @param Definition $definition
     * @return string|null
     */
    public function resolve(Definition $definition);
}
