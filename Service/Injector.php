<?php

namespace SHyx0rmZ\MagicInjection\Service;

class Injector
{
    /** @var RegistryInterface */
    protected $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function injectService($target, $propertyName, $type)
    {
        $serviceName = ucfirst($propertyName);

        if (($injectable = $this->registry->getInjectable($serviceName, $type)) !== null) {
            $class = new \ReflectionClass(get_class($target));
            $property = $class->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($target, $injectable);
        } else {
            throw new \RuntimeException(sprintf('Tried to magically inject unknown injectable into property %s::%s', get_class($target), $propertyName));
        }
    }
}
