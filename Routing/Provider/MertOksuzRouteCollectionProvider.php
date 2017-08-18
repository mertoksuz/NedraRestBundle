<?php
namespace MertOksuz\ApiBundle\Routing\Provider;

use Gedmo\Sluggable\Util\Urlizer;
use MertOksuz\ApiBundle\Component\Metadata;
use MertOksuz\ApiBundle\Component\MetadataInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use MertOksuz\ApiBundle\Routing\RouteCollectionProviderInterface;

final class MertOksuzRouteCollectionProvider implements RouteCollectionProviderInterface
{
    private $configuration;

    public function __construct(array $configs)
    {
        $this->configuration = $configs;
    }

    public function getRouteCollection()
    {
        $routes = new RouteCollection();

        $entities = $this->configuration['entities'];

        foreach ($entities as $alias => $configuration) {

            $routesToGenerate = ['show', 'index', 'create', 'update', 'delete'];

            if (!empty($configuration['only'])) {
                $routesToGenerate = $configuration['only'];
            }
            if (!empty($configuration['except'])) {
                $routesToGenerate = array_diff($routesToGenerate, $configuration['except']);
            }

            /** @var MetadataInterface $metadata */
            $metadata = Metadata::fromAliasAndConfiguration($alias, $configuration);

            $rootPath = sprintf('/%s/', isset($configuration['path']) ? $configuration['path'] : Urlizer::urlize($metadata->getPluralName()));
            $identifier = sprintf('{%s}', $configuration['identifier']);

            if (in_array('index', $routesToGenerate)) {
                $indexRoute = $this->createRoute($rootPath, 'index', ['GET']);
                $routes->add($this->getRouteName($metadata, $configuration, 'index'), $indexRoute);
            }

            if (in_array('create', $routesToGenerate)) {
                $createRoute = $this->createRoute($rootPath . 'new', 'create', ['POST']);
                $routes->add($this->getRouteName($metadata, $configuration, 'create'), $createRoute);
            }

            if (in_array('update', $routesToGenerate)) {
                $updateRoute = $this->createRoute($rootPath . $identifier, 'update', ['PUT', 'PATCH']);
                $routes->add($this->getRouteName($metadata, $configuration, 'update'), $updateRoute);
            }

            if (in_array('show', $routesToGenerate)) {
                $showRoute = $this->createRoute($rootPath . $identifier, 'show', ['GET']);
                $routes->add($this->getRouteName($metadata, $configuration, 'show'), $showRoute);
            }

            if (in_array('delete', $routesToGenerate)) {
                $deleteRoute = $this->createRoute($rootPath . $identifier, 'delete', ['DELETE']);
                $routes->add($this->getRouteName($metadata, $configuration, 'delete'), $deleteRoute);
            }
        }

        return $routes;
    }

    /**
     * @param string $path
     * @param string $actionName
     * @param array $methods
     *
     * @return Route
     */
    private function createRoute($path, $actionName, array $methods)
    {
        $defaults = [
            '_controller' => "MertOksuz\ApiBundle\Controller\ResourceController".sprintf(':%sAction', $actionName),
        ];

        return $this->createMainRoute($path, $defaults, [], [], '', [], $methods);
    }

    /**
     * @param MetadataInterface $metadata
     * @param array $configuration
     * @param string $actionName
     *
     * @return string
     */
    private function getRouteName(MetadataInterface $metadata, array $configuration, $actionName)
    {
        $sectionPrefix = isset($configuration['section']) ? $configuration['section'].'_' : '';

        return sprintf('%s_%s%s_%s', $metadata->getApplicationName(), $sectionPrefix, $metadata->getName(), $actionName);
    }

    /**
     * {@inheritdoc}
     */
    private function createMainRoute($path, array $defaults = [], array $requirements = [], array $options = [], $host = '', $schemes = [], $methods = [], $condition = '')
    {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }
}