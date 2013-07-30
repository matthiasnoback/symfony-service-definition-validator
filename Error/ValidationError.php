<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

use Symfony\Component\DependencyInjection\Definition;

class ValidationError implements ValidationErrorInterface
{
    private $serviceId;
    private $definition;
    private $exception;

    public function __construct(
        $serviceId,
        Definition $definition,
        \Exception $exception
    )
    {
        $this->serviceId = $serviceId;
        $this->definition = $definition;
        $this->exception = $exception;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }
}
