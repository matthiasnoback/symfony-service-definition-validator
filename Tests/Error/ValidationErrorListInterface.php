<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Error;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorListInterface as BaseValidationErrorListInterface;

interface ValidationErrorListInterface extends \Iterator, BaseValidationErrorListInterface
{
}
