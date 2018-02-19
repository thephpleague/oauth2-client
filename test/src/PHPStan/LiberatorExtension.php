<?php
declare(strict_types = 1);

namespace League\OAuth2\Client\Test\PHPStan;

use Eloquent\Liberator\Liberator;
use Eloquent\Pops\ProxyInterface;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class LiberatorExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Liberator::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'liberate';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
    {
        $types = [new ObjectType(ProxyInterface::class)];
        foreach ($scope->getType($methodCall->args[0]->value)->getReferencedClasses() as $referencedClass) {
            $types[] = new LiberatedType($referencedClass);
        }
        return TypeCombinator::intersect(...$types);
    }
}
