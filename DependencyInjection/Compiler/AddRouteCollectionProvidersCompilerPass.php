<?php

namespace Nedra\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nedra\RestBundle\Routing\ModularRouterInterface;
use Nedra\RestBundle\Routing\RouteCollectionProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

final class AddRouteCollectionProvidersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $this->loadCollectorWithType(
            $containerBuilder,
            ModularRouterInterface::class,
            RouteCollectionProviderInterface::class,
            'addRouteCollectionProvider'
        );
    }

    private function loadCollectorWithType(
        ContainerBuilder $containerBuilder,
        $collectorType,
        $collectedType,
        $setterMethod
    ) {
        $collectorDefinition = $this->getByType($containerBuilder, $collectorType);
        foreach ($containerBuilder->getDefinitions() as $name => $definition) {
            if (! is_subclass_of($definition->getClass(), $collectedType)) {
                continue;
            }

            $collectorDefinition->addMethodCall($setterMethod, [new Reference($name)]);
        }
    }

    private function getByType(ContainerBuilder $containerBuilder, $type)
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if (is_a($definition->getClass(), $type, true)) {
                return $definition;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Definition for type "%s" was not found.', $type)
        );
    }
}
