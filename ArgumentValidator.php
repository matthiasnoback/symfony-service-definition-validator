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
            $this->validateArrayArgument($parameter, $argument);
        } elseif ($parameter->getClass()) {
            $this->validateObjectArgument($parameter->getClass()->getName(), $argument, $parameter->allowsNull());
        }

        // other arguments don't need to be or can't be validated
    }

    private function validateArrayArgument(\ReflectionParameter $parameter, $argument)
    {
        if ($parameter->allowsNull() && is_null($argument)) {
            return;
        }

        if (class_exists('Symfony\Component\ExpressionLanguage\Expression') && $argument instanceof Expression) {
            $this->validateExpressionArgument('array', $argument, $parameter->allowsNull());
        } else {
            if (is_array($argument)) {
                return;
            }

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

        if ('service_container' !== $referencedServiceId) {
            $definition = $this->containerBuilder->findDefinition($referencedServiceId);
            // we don't have to check if the definition exists, since the ContainerBuilder itself does that already
        } else {
            $definition = new Definition('Symfony\Component\DependencyInjection\Container');
        }

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

    private function validateExpressionArgument($type, Expression $expression, $allowsNull)
    {
        $expressionLanguage = new ExpressionLanguage();

        $this->validateExpressionSyntax($expression, $expressionLanguage);

        if ($this->evaluateExpressions) {
            $this->validateExpressionResult($type, $expression, $allowsNull, $expressionLanguage);
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

    private function validateExpressionResult($expectedType, Expression $expression, $allowsNull, ExpressionLanguage $expressionLanguage)
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
                $expectedType
            ));
        }

        if ($expectedType === 'array' && !is_array($result)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument for type-hint "%s" is an expression that evaluates to a non-array',
                $expectedType
            ));
        }

        if (class_exists($expectedType)) {
            if (!is_object($result)) {
                throw new TypeHintMismatchException(sprintf(
                    'Argument for type-hint "%s" is an expression that evaluates to a non-object',
                    $expectedType
                ));
            }

            $resultingClass = get_class($result);

            $this->validateClass($expectedType, $resultingClass);
        }
    }

    private function validateClass($expectedClassName, $actualClassName)
    {
        if ($expectedClassName === $actualClassName) {
            return;
        }

        if (!is_a($actualClassName, $expectedClassName, true)) {
            throw new TypeHintMismatchException(sprintf(
                'Argument for type-hint "%s" points to a service of class "%s"',
                $expectedClassName,
                $actualClassName
            ));
        }
    }
}
