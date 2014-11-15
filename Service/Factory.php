<?php

namespace SHyx0rmZ\MagicInjection\Service;

class Factory
{
    public function get()
    {
        $arguments = 0;

        foreach (func_get_args() as $argument) {
            if ($argument instanceof Wrapper) {
                break;
            }

            $arguments++;
        }

        $object = null;

        for ($i = $arguments; $i < func_num_args(); ++$i) {
            /** @var Wrapper $wrapper */
            $wrapper = func_get_arg($i);

            if ($object === null) {
                $class = new \ReflectionClass($wrapper->class);
                $object = $class->newInstanceArgs(func_get_args());
            }

            $wrapper->injector->injectService($object, $wrapper->property, $wrapper->type);
        }

        return $object;
    }
}
