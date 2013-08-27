<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorFactoryInterface;
use Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionValidationExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BatchServiceDefinitionValidator implements BatchServiceDefinitionValidatorInterface
{
    private $serviceDefinitionValidator;
    private $validationErrorFactory;

    public function __construct(
        ServiceDefinitionValidatorInterface $serviceDefinitionValidator,
        ValidationErrorFactoryInterface $validationErrorFactory
    ) {
        $this->serviceDefinitionValidator = $serviceDefinitionValidator;
        $this->validationErrorFactory = $validationErrorFactory;
    }

    public function validate(array $serviceDefinitions)
    {
        $validationErrorList = $this->validationErrorFactory->createValidationErrorList();

        foreach ($serviceDefinitions as $serviceId => $definition) {
            try {
                $this->serviceDefinitionValidator->validate($definition);
            } catch (\Exception $exception) {
                if ($exception instanceof DefinitionValidationExceptionInterface) {
                    $error = $this->validationErrorFactory->createValidationError($serviceId, $definition, $exception);
                    $validationErrorList->add($error);
                } else {
                    throw $exception;
                }
            }
        }

        return $validationErrorList;
    }
}
