<?php
declare(strict_types = 1);

namespace League\OAuth2\Client\Test\PHPStan;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;

final class LiberatedType extends ObjectType
{
    public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
    {
        return new LiberatedMethod(parent::getMethod($methodName, $scope));
    }
}
