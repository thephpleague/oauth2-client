<?php
declare(strict_types = 1);

namespace League\OAuth2\Client\Test\PHPStan;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;

final class LiberatedMethod implements MethodReflection
{
    /** @var \PHPStan\Reflection\MethodReflection */
    private $reflection;

    public function __construct(MethodReflection $reflection)
    {
        $this->reflection = $reflection;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->reflection->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->reflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->reflection->getPrototype();
    }

    /**
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getVariants(): array
    {
        return $this->reflection->getVariants();
    }
}
