<?php
declare(strict_types = 1);

namespace League\OAuth2\Client\Test\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;

final class LiberatedMethod implements MethodReflection
{
    /**
     * @var \PHPStan\Reflection\MethodReflection
     */
    private $methodReflection;

    public function __construct(MethodReflection $methodReflection)
    {
        $this->methodReflection = $methodReflection;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->methodReflection->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->methodReflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getPrototype(): MethodReflection
    {
        return new self($this->methodReflection->getPrototype());
    }

    public function getName(): string
    {
        return $this->methodReflection->getName();
    }

    /**
     * @return \PHPStan\Reflection\ParameterReflection[]
     */
    public function getParameters(): array
    {
        return $this->methodReflection->getParameters();
    }

    public function isVariadic(): bool
    {
        return $this->methodReflection->isVariadic();
    }

    public function getReturnType(): Type
    {
        return $this->methodReflection->getReturnType();
    }
}
