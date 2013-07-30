<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class ServiceNotFoundException extends \InvalidArgumentException implements DefinitionValidationExceptionInterface
{
    public function __construct($serviceId)
    {
        parent::__construct(sprintf(
            'Service "%s" does not exist',
            $serviceId
        ));
    }
}
