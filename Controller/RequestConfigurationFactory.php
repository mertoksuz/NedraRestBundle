<?php
namespace MertOksuz\ApiBundle\Controller;

use MertOksuz\ApiBundle\Metadata\RegistryInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class RequestConfigurationFactory implements RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, Request $request)
    {
        $parameters = $request->attributes->all();
        $aliasFull = $parameters["_route"];

        if (!empty($aliasFull)) {
            $split = explode("_", $aliasFull);
            $alias = $split[0].".".$split[1];

            $conf = $registry->get($alias);
        } else {
            throw new InvalidArgumentException("Alias ".$aliasFull." not match");
        }

        return $conf;
    }
}