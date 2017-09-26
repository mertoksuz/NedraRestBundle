<?php
namespace Nedra\RestBundle\Controller;

use Nedra\RestBundle\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestFormConfigurationInterface
 * @package Nedra\RestBundle\Controller
 */
interface RequestFormConfigurationInterface
{
    public function create(\Doctrine\ORM\Mapping\ClassMetadata $metadata, Request $request, $resource);
}
