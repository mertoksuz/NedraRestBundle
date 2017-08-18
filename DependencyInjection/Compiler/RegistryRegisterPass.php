<?php
namespace MertOksuz\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegistryRegisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        try {
            $resources = $containerBuilder->getParameter('mertoksuz_api.config');
            $registry = $containerBuilder->findDefinition('mert_oksuz.registry');
        } catch (InvalidArgumentException $exception) {
            return ;
        }

        foreach ($resources['entities'] as $alias => $config) {
            $registry->addMethodCall('addFromAliasAndConfiguration', [$alias, $config]);
        }
    }
}