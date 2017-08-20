<?php

namespace Nedra\RestBundle\DependencyInjection;

use Nedra\RestBundle\Controller\ResourceController;
use Nedra\RestBundle\Form\Type\DefaultResourceType;
use Nedra\RestBundle\NedraRestBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nedra_rest');

        $this->addEntitiesSection($rootNode);

        return $treeBuilder;
    }

    private function addEntitiesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode("active")->defaultTrue()->end()
                ->arrayNode("entities")
                ->useAttributeAsKey('name')
                    ->prototype("array")
                        ->children()
                            ->scalarNode('driver')->defaultValue(NedraRestBundle::DRIVER_DOCTRINE_ORM)->end()
                            ->scalarNode('path')->cannotBeEmpty()->end()
                            ->scalarNode('identifier')->defaultValue('id')->end()
                            ->arrayNode('classes')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('interface')->cannotBeEmpty()->end()
                                ->scalarNode('form')->defaultValue(DefaultResourceType::class)->cannotBeEmpty()->end()
                                ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                ->scalarNode('repository')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
