<?php
namespace Nedra\RestBundle\Component;

use Nedra\RestBundle\Metadata\Metadata;
use Nedra\RestBundle\Metadata\MetadataInterface;

/**
 * Class Registry
 * @package Nedra\RestBundle\Metadata
 */
final class Registry implements RegistryInterface
{
    /**
     * @var array
     */
    private $metadata = [];

    /**
     * {@inheritdoc}
     */
    public function getAll() {
        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias) {
        if (!array_key_exists($alias, $this->metadata)) {
            throw new \InvalidArgumentException(sprintf('Resource "%s" does not exist.', $alias));
        }

        return $this->metadata[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function getByClass($className) {
        foreach ($this->metadata as $metadata) {
            if ($className === $metadata->getClass('model')) {
                return $metadata;
            }
        }

        throw new \InvalidArgumentException(sprintf('Resource with model class "%s" does not exist.', $className));
    }

    /**
     * {@inheritdoc}
     */
    public function add(MetadataInterface $metadata) {
        $this->metadata[$metadata->getAlias()] = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function addFromAliasAndConfiguration($alias, array $configuration) {
        $this->add(Metadata::fromAliasAndConfiguration($alias, $configuration));
    }
}
