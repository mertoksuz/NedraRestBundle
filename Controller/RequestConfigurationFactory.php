<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class RequestConfigurationFactory implements RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, Request $request)
    {
        $parameters = $request->attributes->all();
        $model = $parameters["_nedrarest"]["model"];
        $conf = $registry->getByClass($model);

        if (!$conf) {
            throw new InvalidArgumentException("Model class not found. !");
        }

        return $conf;
    }
}