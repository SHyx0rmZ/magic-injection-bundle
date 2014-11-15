<?php

namespace SHyx0rmZ\MagicInjection;

use SHyx0rmZ\MagicInjection\DependencyInjection\Compiler\MagicInjectionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MagicInjectionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MagicInjectionPass());
    }
}
