<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Metadata\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

interface RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, Request $request);
}