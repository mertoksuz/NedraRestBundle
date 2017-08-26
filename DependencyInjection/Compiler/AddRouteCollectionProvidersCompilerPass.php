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

        $this->initiateRouteCollectionLoaderWithProviderType(
            $containerBuilder,
            ModularRouterInterface::class,
            RouteCollectionProviderInterface::class,
            'addRouteCollectionProvider'
        );
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param $routeCollector
     * @param $routeProvider
     * @param $setterMethod
     */
    private function initiateRouteCollectionLoaderWithProviderType(
        ContainerBuilder $containerBuilder,
        $routeCollector,
        $routeProvider,
        $setterMethod
    ) {
        $collectorDefinition = $this->getByRouteProvider($containerBuilder, $routeCollector);
        foreach ($containerBuilder->getDefinitions() as $name => $definition) {
            if (!is_subclass_of($definition->getClass(), $routeProvider)) {
                continue;
            }

            $collectorDefinition->addMethodCall($setterMethod, [new Reference($name)]);
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param $routeProvider
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function getByRouteProvider(ContainerBuilder $containerBuilder, $routeProvider)
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if (is_a($definition->getClass(), $routeProvider, true)) {
                return $definition;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Definition for type "%s" was not found.', $routeProvider)
        );
    }
}
