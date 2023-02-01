<?php

namespace ConstructorInjection;

use Closure;
use ConstructorInjection\Exception\EntryNotFoundException;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private array $bindings = [];

    /**
     * @param string $id
     * @return object|null
     * @throws ReflectionException
     */
    public function get(string $id): object|null
    {
        $resolvedInstance = $this->resolved($id);

        if (!($resolvedInstance instanceof ReflectionClass)) {
            return $resolvedInstance;
        }

        return $this->createInstance($resolvedInstance);
    }

    /**
     * @param string $id
     * @param Closure|null $callable
     * @return $this
     */
    public function bind(string $id, Closure $callable = null): self
    {
        if (is_null($callable)) {
            $callable = $id;
        }

        $this->bindings[$id] = $callable;

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     * @throws EntryNotFoundException
     */
    public function has(string $id): bool
    {
        try {
            $resolvedInstance = $this->resolved($id);
        } catch (Exception $e) {
            throw new EntryNotFoundException($id, $e->getCode(), $e);
        }

        if ($resolvedInstance instanceof ReflectionClass) {
            return $resolvedInstance->isInstantiable();
        }

        return isset($resolvedInstance);
    }

    /**
     * @param $id
     * @return ReflectionClass
     * @throws ReflectionException
     */
    private function resolved($id)
    {
        try {
            $name = $id;

            if (isset($this->bindings[$id])) {
                $name = $this->bindings[$id];
                if (is_callable($name)) {
                    return $name();
                }
            }

            return new ReflectionClass($name);
        } catch (Exception $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return object|null
     * @throws ReflectionException
     * @throws Exception
     */
    protected function createInstance(ReflectionClass $reflectionClass): object|null
    {
        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            return $reflectionClass->newInstance();
        }

        if (!$constructor->getParameters()) {
            return $reflectionClass->newInstance();
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($type = $parameter->getType()) {
                $parameters[] = $this->get($type->getName());
            }
        }

        return $reflectionClass->newInstanceArgs($parameters);
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param string $id
     * @return null|ReflectionClass
     * @throws ReflectionException
     */
    public function getBinding(string $id)
    {
        if (is_callable($this->bindings[$id])) {
            return $this->resolved($id);
        }

        return new $this->bindings[$id];
    }
}
