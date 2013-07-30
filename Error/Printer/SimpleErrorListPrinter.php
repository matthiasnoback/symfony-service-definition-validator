<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error\Printer;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorInterface;
use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorListInterface;

class SimpleErrorListPrinter implements ErrorListPrinterInterface
{
    public function printErrorList(ValidationErrorListInterface $errorList)
    {
        $result = '';
        $result .= $this->printHeader($errorList);

        foreach ($errorList as $error) {
            $result .= $this->printError($error);
        }

        return $result;
    }

    private function printHeader(ValidationErrorListInterface $errorList)
    {
        return sprintf(
            'Service definition validation errors (%d):',
            count($errorList)
        );
    }

    private function printError(ValidationErrorInterface $error)
    {
        return sprintf(
            "\n".'- %s: %s',
            $error->getServiceId(),
            $error->getException()->getMessage()
        );
    }
}
