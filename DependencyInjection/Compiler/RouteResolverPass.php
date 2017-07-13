<?php

namespace MertOksuz\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RouteResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $routing = $container->getDefinition($container->getAlias('router'));
    }
}
