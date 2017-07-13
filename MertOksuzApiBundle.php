<?php

namespace MertOksuz\ApiBundle;

use MertOksuz\ApiBundle\DependencyInjection\Compiler\RouteResolverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MertOksuzApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RouteResolverPass());
    }
}
