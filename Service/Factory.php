<?php

namespace SHyx0rmZ\MagicInjection\Service;

class Factory
{
    /**
     * Creates a new instance of some service that needs other services injected. Do not use manually.
     * @return object
     */
    public function createInstance()
    {
        $arguments = func_get_args();

        $regularArguments = $this->getNumberOfRegularArguments($arguments);
        $object = $this->getObjectWithServicesInjected($arguments, $regularArguments);

        return $object;
    }

    /**
     * @param null|object $object
     * @param string $class
     * @param array $arguments
     * @return object
     */
    private function ensureObjectInstantiated($object, $class, array $arguments)
    {
        if ($object === null) {
            $class = new \ReflectionClass($class);
            $object = $class->newInstanceArgs($arguments);
        }

        return $object;
    }

    /**
     * @param array $arguments
     * @return int
     */
    private function getNumberOfRegularArguments(array $arguments)
    {
        $regularArguments = 0;

        foreach ($arguments as $argument) {
            if ($argument instanceof Wrapper) {
                break;
            }

            $regularArguments++;
        }
        return $regularArguments;
    }

    /**
     * @param array $arguments
     * @param integer $regularArguments
     * @return object
     */
    private function getObjectWithServicesInjected(array $arguments, $regularArguments)
    {
        /** @var Wrapper[] $serviceWrappers */
        $wrappers = array_splice($arguments, $regularArguments);
        $object = null;

        /** @var Wrapper $wrapper */
        foreach ($wrappers as $wrapper) {
            $object = $this->ensureObjectInstantiated($object, $wrapper->class, $arguments);

            $wrapper->injector->injectService($object, $wrapper->property, $wrapper->type);
        }

        return $object;
    }
}
