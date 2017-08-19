<?php
namespace MertOksuz\ApiBundle\Controller;

use MertOksuz\ApiBundle\Component\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestFormConfigurationInterface
 * @package MertOksuz\ApiBundle\Controller
 */
interface RequestFormConfigurationInterface
{
    public function create(MetadataInterface $metadata, Request $request, $resource);
}