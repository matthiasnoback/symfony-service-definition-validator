<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error\Printer;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorListInterface;

interface ErrorListPrinterInterface
{
    /**
     * @param ValidationErrorListInterface $errorList
     * @return string
     */
    public function printErrorList(ValidationErrorListInterface $errorList);
}
