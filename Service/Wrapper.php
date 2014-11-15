<?php

namespace SHyx0rmZ\MagicInjection\Service;

class Wrapper
{
    private $wrappedService;
    private $wrappedClass;

    public function __construct(Injector $injector, $property, $type, $wrappedService)
    {
        $this->wrappedService = $wrappedService;

        while (get_class($this->wrappedService) == self::class) {
            $this->wrappedService = $this->wrappedService->wrappedService;
        }

        $this->wrappedClass = new \ReflectionClass(get_class($this->wrappedService));

        $injector->injectService($this->wrappedService, $property, $type);
    }

    public function __get($name)
    {
        return $this->wrappedService->$name;
    }

    public function __set($name, $value)
    {
        $this->wrappedService->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->wrappedService->$name);
    }

    public function __unset($name)
    {
        unset($this->wrappedService->$name);
    }

    public function __call($name, $arguments)
    {
        $this->wrappedClass->getMethod($name)->invokeArgs($this->wrappedService, $arguments);
    }
}
