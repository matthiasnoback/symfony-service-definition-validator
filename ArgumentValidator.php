<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

class ArgumentValidator implements ArgumentValidatorInterface
{
    private $containerBuilder;
    private $resultingClassResolver;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ResultingClassResolverInterface $resultingClassResolver
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
    }

    public function validate(\ReflectionParameter $parameter, $argument)
    {
        if ($parameter->isArray()) {
            $this->validateArrayArgument($argument);
        } elseif ($parameter->getClass()) {
            $this->validateObjectArgument($parameter->getClass()->getName(), $argument, $parameter->allowsNull());
        }

        // other arguments don't need to be or can't be validated
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

    private function validateObjectArgument($className, $argument, $allowsNull)
    {
        if ($argument instanceof Reference) {
            $this->validateReferenceArgument($className, $argument);
        } elseif ($argument instanceof Definition) {
            $this->validateDefinitionArgument($className, $argument);
        } elseif ($argument === null && $allowsNull) {
            return;
        } elseif (class_exists('Symfony\Component\ExpressionLanguage\Expression') && $argument instanceof Expression) {
            // We currently have no way to validate an expression
            // See also https://github.com/matthiasnoback/symfony-service-definition-validator/issues/6
            return;
        } else {
            throw new TypeHintMismatchException(sprintf(
                'Type-hint "%s" requires this argument to be a reference to a service or an inline service definition',
                $className
            ));
        }
    }

    private function validateReferenceArgument($className, Reference $reference)
    {
        // the __toString method of a Reference is the referenced service id
        $referencedServiceId = (string)$reference;
        $definition = $this->containerBuilder->findDefinition($referencedServiceId);
        // we don't have to check if the definition exists, since the ContainerBuilder itself does that already

        $this->validateDefinitionArgument($className, $definition);
    }

    private function validateDefinitionArgument($className, Definition $definition)
    {
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
                'Argument for type-hint "%s" should point to a service of class "%s"',
                $className,
                $resultingClass
            ));
        }
    }
}
