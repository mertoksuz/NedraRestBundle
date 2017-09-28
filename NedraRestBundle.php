<?php

namespace Nedra\RestBundle;

use Nedra\RestBundle\DependencyInjection\Compiler\AddRouteCollectionProvidersCompilerPass;
use Nedra\RestBundle\DependencyInjection\Compiler\RegistryRegisterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NedraRestBundle extends Bundle
{
    const DRIVER_DOCTRINE_ORM = "doctrine";
    const REGISTRY_ID = 'nedra_rest.registry';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddRouteCollectionProvidersCompilerPass);
    }
}
