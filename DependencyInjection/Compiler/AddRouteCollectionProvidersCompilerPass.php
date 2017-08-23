<?php

namespace Nedra\RestBundle\DependencyInjection\Compiler;

use Nedra\RestBundle\NedraRestBundle;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nedra\RestBundle\Routing\ModularRouterInterface;
use Nedra\RestBundle\Routing\RouteCollectionProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

final class AddRouteCollectionProvidersCompilerPass implements CompilerPassInterface
{
    /**
     * You can update container before compile.
     *
     * @param ContainerBuilder $containerBuilder
     */
    public function process(ContainerBuilder $containerBuilder)
    {

        if (!$containerBuilder->has(NedraRestBundle::REGISTRY_ID)) {
            return;
        }

        $this->loadCollectorWithType(
            $containerBuilder,
            ModularRouterInterface::class,
            RouteCollectionProviderInterface::class,
            'addRouteCollectionProvider'
        );
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param $collectorType
     * @param $collectedType
     * @param $setterMethod
     */
    private function loadCollectorWithType(
        ContainerBuilder $containerBuilder,
        $collectorType,
        $collectedType,
        $setterMethod
    ) {
        $collectorDefinition = $this->getByType($containerBuilder, $collectorType);
        foreach ($containerBuilder->getDefinitions() as $name => $definition) {
            if (!is_subclass_of($definition->getClass(), $collectedType)) {
                continue;
            }

            $collectorDefinition->addMethodCall($setterMethod, [new Reference($name)]);
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param $type
     * @return \Symfony\Component\DependencyInjection\Definition
     */
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
