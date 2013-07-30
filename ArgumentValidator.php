<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ArgumentValidator implements ArgumentValidatorInterface
{
    private $containerBuilder;
    private $resultingClassResolver;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ResultingClassResolverInterface $resultingClassResolver
    )
    {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
    }

    public function validate(\ReflectionParameter $parameter, $argument)
    {
        if ($parameter->isArray()) {
            $this->validateArrayArgument($argument);
        } elseif ($parameter->getClass()) {
            $this->validateObjectArgument($parameter->getClass()->getName(), $argument);
        }
    }

    private function validateArrayArgument($argument)
    {
        if (!is_array($argument)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument of type "%s" should have been an array',
                gettype($argument)
            ));
        }
    }

    private function validateObjectArgument($className, $argument)
    {
        if (!($argument instanceof Reference)) {
            throw new TypeHintMismatchException(sprintf(
                'Type-hint "%s" requires this argument to be an instance of Symfony\Component\DependencyInjection\Reference',
                $className
            ));
        }

        // the __toString method of a Reference is the referenced service id
        $referencedServiceId = (string)$argument;
        $definition = $this->containerBuilder->findDefinition($referencedServiceId);
        // we don't have to check if the definition exists, since the ContainerBuilder itself does that already

        $resultingClass = $this->resultingClassResolver->resolve($definition);
        if ($resultingClass === null) {
            return;
        }

        if ($className === $resultingClass) {
            return;
        }

        $reflectionClass = new \ReflectionClass($resultingClass);
        if (!$reflectionClass->isSubclassOf($className)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument with type-hint "%s" is a reference to a service of class "%s"',
                $className,
                $resultingClass
            ));
        }
    }
}
