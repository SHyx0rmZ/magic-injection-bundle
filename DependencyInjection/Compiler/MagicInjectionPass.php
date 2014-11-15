<?php

namespace SHyx0rmZ\MagicInjection\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\MagicInjection\Service\Annotation\MagicInjection;
use SHyx0rmZ\MagicInjection\Service\Wrapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MagicInjectionPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throw \RuntimeException
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('magic_injection.registry');

        foreach ($container->findTaggedServiceIds('magic_injection.injectable_service') as $id => $tags) {
            $this->registerInjectables($tags, $registry, $id);
        }

        foreach ($container->findTaggedServiceIds('magic_injection.injection_target') as $id => $tags) {
            $this->setupInjectionFactories($container, $id);
        }
    }

    /**
     * @param \ReflectionProperty $property
     * @return null|MagicInjection
     */
    private function getMagicInjectionAnnotation(\ReflectionProperty $property)
    {
        $annotation = new AnnotationReader();
        $annotation = $annotation->getPropertyAnnotation($property, MagicInjection::class);

        return $annotation;
    }

    /**
     * @param array $tags
     * @param Definition $registry
     * @param string $id
     */
    private function registerInjectables(array $tags, Definition $registry, $id)
    {
        foreach ($tags as $attributes) {
            if (isset($attributes['type'])) {
                $registry->addMethodCall('registerInjectable', array(new Reference($id), $attributes['type']));
            } else {
                $registry->addMethodCall('registerInjectable', array(new Reference($id)));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param \ReflectionProperty $property
     * @param MagicInjection $annotation
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function registerNewFactory(ContainerBuilder $container, $id, \ReflectionProperty $property, MagicInjection $annotation)
    {
        $wrapper = $container->register($id . '.factory.' . $property->getName(), Wrapper::class);
        $wrapper->setPublic(false);
        $wrapper->setProperties(array(
            'class' => $container->getDefinition($id)->getClass(),
            'property' => $property->getName(),
            'type' => $annotation->getType(),
            'injector' => new Reference('magic_injection.injector')
        ));

        return $wrapper;
    }

    /**
     * @param Definition $targetDefinition
     * @param Definition $wrapperDefinition
     */
    private function setFactory(Definition $targetDefinition, Definition $wrapperDefinition)
    {
        $targetDefinition->setFactoryService('magic_injection.factory')->setFactoryMethod('createInstance');
        $targetDefinition->addArgument($wrapperDefinition);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @throw \RuntimeException
     */
    private function setupInjectionFactories(ContainerBuilder $container, $id)
    {
        $targetDefinition = $container->getDefinition($id);
        $targetClass = $targetDefinition->getClass();
        $reflectionClass = new \ReflectionClass($targetClass);
        $annotationFound = false;

        foreach ($reflectionClass->getProperties() as $property) {
            $annotationFound = $this->setupAnnotatedPropertyInjection($container, $id, $property);
        }

        if (!$annotationFound) {
            throw new \RuntimeException(sprintf('No MagicInjection annotation found in service: %s', $id));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param \ReflectionProperty $property
     * @return bool
     */
    private function setupAnnotatedPropertyInjection(ContainerBuilder $container, $id, \ReflectionProperty $property)
    {
        $annotation = $this->getMagicInjectionAnnotation($property);

        if ($annotation !== null) {
            $targetDefinition = $container->getDefinition($id);
            $wrapperDefinition = $this->registerNewFactory($container, $id, $property, $annotation);

            $this->setFactory($targetDefinition, $wrapperDefinition);

            return true;
        }

        return false;
    }
}
