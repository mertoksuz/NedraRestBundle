<?php
namespace Nedra\RestBundle\Routing;

use Symfony\Component\Routing\RouterInterface;

interface ModularRouterInterface extends RouterInterface
{
    public function addRouteCollectionProvider(RouteCollectionProviderInterface $routeCollectionProvider);
}
