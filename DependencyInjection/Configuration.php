<?php

namespace MertOksuz\ApiBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Factory\Factory;
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
        $rootNode = $treeBuilder->root('mert_oksuz_api');

        $this->addDriversSection($rootNode);
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
                            ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                            ->arrayNode('classes')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('interface')->cannotBeEmpty()->end()
                                ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                ->scalarNode('form')->defaultValue(DefaultResourceType::class)->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addDriversSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('drivers')
                    ->defaultValue([SyliusResourceBundle::DRIVER_DOCTRINE_ORM])
                    ->prototype('enum')->values(SyliusResourceBundle::getAvailableDrivers())->end()
                ->end()
            ->end()
        ;
    }
}
