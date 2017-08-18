<?php
namespace MertOksuz\ApiBundle\Controller;

use MertOksuz\ApiBundle\Metadata\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

interface RequestConfigurationInterface
{
    public function create(RegistryInterface $registry, Request $request);
}