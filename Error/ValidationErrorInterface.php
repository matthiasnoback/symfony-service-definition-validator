<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

use Symfony\Component\DependencyInjection\Definition;

interface ValidationErrorInterface
{
    /**
     * @return Definition
     */
    public function getDefinition();

    /**
     * @return \Exception
     */
    public function getException();

    /**
     * @return string
     */
    public function getServiceId();
}
