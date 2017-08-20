<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Component\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestFormConfigurationInterface
 * @package Nedra\RestBundle\Controller
 */
interface RequestFormConfigurationInterface
{
    public function create(MetadataInterface $metadata, Request $request, $resource);
}