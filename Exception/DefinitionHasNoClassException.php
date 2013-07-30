<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class DefinitionHasNoClassException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Definition has no class');
    }
}
