<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

interface RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, Request $request);
}