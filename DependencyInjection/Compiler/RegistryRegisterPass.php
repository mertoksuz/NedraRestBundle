<?php
namespace Nedra\RestBundle\DependencyInjection\Compiler;

use Nedra\RestBundle\NedraRestBundle;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegistryRegisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {

        if (!$containerBuilder->has(NedraRestBundle::REGISTRY_ID)) {
            return;
        }

        try {
            $resources = $containerBuilder->getParameter('nedrarest.config');
            $registry = $containerBuilder->findDefinition('nedra_rest.registry');
        } catch (InvalidArgumentException $exception) {
            return ;
        }

        foreach ($resources['entities'] as $alias => $config) {
            $registry->addMethodCall('addFromAliasAndConfiguration', [$alias, $config]);
        }
    }
}