<?php

namespace Nedra\RestBundle\Component;

use Nedra\RestBundle\Component\MetadataInterface;

/**
 * Interface RegistryInterface
 * @package Nedra\RestBundle\Metadata
 */
interface RegistryInterface
{
    /**
     * @return MetadataInterface[]
     */
    public function getAll();

    /**
     * @param string $alias
     *
     * @return MetadataInterface
     */
    public function get($alias);

    /**
     * @param string $className
     *
     * @return MetadataInterface
     */
    public function getByClass($className);

    /**
     * @param MetadataInterface $metadata
     */
    public function add(MetadataInterface $metadata);

    /**
     * @param string $alias
     * @param array $configuration
     */
    public function addFromAliasAndConfiguration($alias, array $configuration);
}
