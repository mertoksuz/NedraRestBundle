<?php

namespace MertOksuz\ApiBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MertOksuzApiBundle extends Bundle
{
    const DRIVER_DOCTRINE_ORM = "doctrine";

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
