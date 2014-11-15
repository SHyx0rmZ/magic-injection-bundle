<?php

namespace SHyx0rmZ\MagicInjection\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\MagicInjection\Service\Annotation\MagicInjection;
use SHyx0rmZ\MagicInjection\Service\Wrapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MagicInjectionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('magic_injection.registry');

        foreach ($container->findTaggedServiceIds('magic_injection.injectable_service') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['type'])) {
                    $registry->addMethodCall('registerInjectable', array(new Reference($id), $attributes['type']));
                } else {
                    $registry->addMethodCall('registerInjectable', array(new Reference($id)));
                }
            }
        }

        foreach ($container->findTaggedServiceIds('magic_injection.injection_target') as $id => $tags) {
            $targetDefinition = $container->getDefinition($id);
            $targetClass = $targetDefinition->getClass();
            $reflectionClass = new \ReflectionClass($targetClass);
            $annotationFound = false;

            foreach ($reflectionClass->getProperties() as $property) {
                $annotation = new AnnotationReader();
                $annotation = $annotation->getPropertyAnnotation($property, MagicInjection::class);

                if ($annotation !== null) {
                    $annotationFound = true;
                    $container
                        ->register($id . '.magic_injection.decorator.' . $property->getName(), Wrapper::class)
                        ->addArgument(new Reference('magic_injection.injector'))
                        ->addArgument($property->getName())
                        ->addArgument($annotation->getType())
                        ->addArgument(new Reference($id . '.magic_injection.decorator.' . $property->getName() . '.inner'))
                        ->setPublic(false)
                        ->setDecoratedService($id);
                }
            }

            if (!$annotationFound) {
                throw new \RuntimeException(sprintf('No MagicInjection annotation found in service: %s', $id));
            }
        }
    }
}
