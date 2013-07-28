<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ArgumentValidator implements ArgumentValidatorInterface
{
    private $containerBuilder;
    private $resultingClassResolver;

    public function __construct(ContainerBuilder $containerBuilder, ResultingClassResolverInterface $resultingClassResolver)
    {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
    }

    public function validate(\ReflectionParameter $parameter, $argument)
    {
        $typeHint = $parameter->getClass();

        if (!$typeHint) {
            // skip validation for parameters with no type-hint
            return;
        }

        if (!($argument instanceof Reference)) {
            throw new TypeHintMismatchException(sprintf(
                'Type-hint "%s" require this argument to be an instance of Symfony\Component\DependencyInjection\Reference',
                $typeHint
            ));
        }

        // the __toString method of a Reference is the referenced service id
        $referencedServiceId = (string)$argument;
        $definition = $this->containerBuilder->findDefinition($referencedServiceId);
        // we don't have to check if the definition exists, since the ContainerBuilder itself does that already

        $resultingClass = $this->resultingClassResolver->resolve($definition);
        if ($resultingClass === null) {
            // TODO test this
            return;
        }

        $reflectionClass = new \ReflectionClass($resultingClass);
        if (!$reflectionClass->isSubclassOf($typeHint)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument with type-hint "%s" is a reference to a service of class "%s"',
                $typeHint,
                $resultingClass
            ));
        }

        // TODO test this
    }
}
