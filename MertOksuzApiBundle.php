<?php

namespace MertOksuz\ApiBundle;

use MertOksuz\ApiBundle\DependencyInjection\Compiler\AddRouteCollectionProvidersCompilerPass;
use MertOksuz\ApiBundle\DependencyInjection\Compiler\RegistryRegisterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MertOksuzApiBundle extends Bundle
{
    const DRIVER_DOCTRINE_ORM = "doctrine";

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddRouteCollectionProvidersCompilerPass);
        $container->addCompilerPass(new RegistryRegisterPass);
    }
}
