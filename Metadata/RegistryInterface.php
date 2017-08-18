<?php

namespace MertOksuz\ApiBundle\Metadata;

use MertOksuz\ApiBundle\Component\MetadataInterface;

/**
 * Interface for the registry of all resources.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
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
