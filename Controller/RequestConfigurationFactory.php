<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestConfigurationFactory implements RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, ParameterBag $parameterBag)
    {
        $model = $parameterBag->get("_nedrarest")["model"];
        $conf = $registry->getByClass($model);

        if (!$conf) {
            throw new InvalidArgumentException("Model class not found. !");
        }

        return $conf;
    }
}
