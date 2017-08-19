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
        $model = $parameters["_mertoksuz"]["model"];
        $conf = $registry->getByClass($model);

        if (!$conf) {
            throw new InvalidArgumentException("Model class not found. !");
        }

        return $conf;
    }
}