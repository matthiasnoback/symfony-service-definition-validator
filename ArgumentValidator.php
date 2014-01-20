<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidExpressionException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidExpressionSyntaxException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ArgumentValidator implements ArgumentValidatorInterface
{
    private $containerBuilder;
    private $resultingClassResolver;
    private $evaluateExpressions;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ResultingClassResolverInterface $resultingClassResolver,
        $evaluateExpressions = false
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
        $this->evaluateExpressions = $evaluateExpressions;
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
        } elseif (class_exists('Symfony\Component\ExpressionLanguage\Expression') && $argument instanceof Expression) {
            $this->validateExpressionArgument($className, $argument, $allowsNull);
        } elseif ($argument === null && $allowsNull) {
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

        $this->validateClass($className, $resultingClass);
    }

    private function validateExpressionArgument($className, Expression $expression, $allowsNull)
    {
        $expressionLanguage = new ExpressionLanguage();

        $this->validateExpressionSyntax($expression, $expressionLanguage);

        if ($this->evaluateExpressions) {
            $this->validateExpressionResult($className, $expression, $allowsNull, $expressionLanguage);
        }
    }

    private function validateExpressionSyntax(Expression $expression, ExpressionLanguage $expressionLanguage)
    {
        try {
            $expressionLanguage->parse($expression, array('container'));
        } catch (SyntaxError $exception) {
            throw new InvalidExpressionSyntaxException($expression, $exception);
        }
    }

    private function validateExpressionResult($className, Expression $expression, $allowsNull, ExpressionLanguage $expressionLanguage)
    {
        try {
            $result = $expressionLanguage->evaluate($expression, array('container' => $this->containerBuilder));
        } catch (\Exception $exception) {
            throw new InvalidExpressionException($expression, $exception);
        }

        if ($result === null) {
            if ($allowsNull) {
                return;
            }

            throw new TypeHintMismatchException(sprintf(
                'Argument for type-hint "%s" is an expression that evaluates to null, which is not allowed',
                $className
            ));
        }

        if (!is_object($result)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument for type-hint "%s" is an expression that evaluates to a non-object',
                $className
            ));
        }

        $resultingClass = get_class($result);

        $this->validateClass($className, $resultingClass);
    }

    private function validateClass($expectedClassName, $actualClassName)
    {
        if ($expectedClassName === $actualClassName) {
            return;
        }

        $reflectionClass = new \ReflectionClass($actualClassName);
        if (!$reflectionClass->isSubclassOf($expectedClassName)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument for type-hint "%s" points to a service of class "%s"',
                $expectedClassName,
                $actualClassName
            ));
        }
    }
}
