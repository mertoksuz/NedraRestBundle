<?php

namespace MertOksuz\ApiBundle\Routing;

use Gedmo\Sluggable\Util\Urlizer;
use MertOksuz\ApiBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $loaded = false;

    /** @var Container  */
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        $processor = new Processor();
        $configurationDefinition = new Configuration();


        $params = $this->container->getParameter("mertoksuz_api.config");
        $configuration = $processor->processConfiguration($configurationDefinition, ['mert_oksuz_api' => $params]);

        if (!empty($configuration['only']) && !empty($configuration['except'])) {
            throw new \InvalidArgumentException('You can configure only one of "except" & "only" options.');
        }

        $routesToGenerate = ['show', 'index', 'create', 'update', 'delete'];

        if (!empty($configuration['only'])) {
            $routesToGenerate = $configuration['only'];
        }
        if (!empty($configuration['except'])) {
            $routesToGenerate = array_diff($routesToGenerate, $configuration['except']);
        }

        $isApi = $type === 'mert_oksuz.api';

        $metadata = $configuration['alias'];
        $routes = new RouteCollection();

        $rootPath = sprintf('/%s/', isset($configuration['path']) ? $configuration['path'] : Urlizer::urlize($metadata->getPluralName()));
        $identifier = sprintf('{%s}', $configuration['identifier']);

        if (in_array('index', $routesToGenerate)) {
            $indexRoute = $this->createRoute($metadata, $configuration, $rootPath, 'index', ['GET'], $isApi);
            $routes->add($this->getRouteName($metadata, $configuration, 'index'), $indexRoute);
        }

        if (in_array('create', $routesToGenerate)) {
            $createRoute = $this->createRoute($metadata, $configuration, $isApi ? $rootPath : $rootPath . 'new', 'create', $isApi ? ['POST'] : ['GET', 'POST'], $isApi);
            $routes->add($this->getRouteName($metadata, $configuration, 'create'), $createRoute);
        }

        if (in_array('update', $routesToGenerate)) {
            $updateRoute = $this->createRoute($metadata, $configuration, $isApi ? $rootPath . $identifier : $rootPath . $identifier . '/edit', 'update', $isApi ? ['PUT', 'PATCH'] : ['GET', 'PUT', 'PATCH'], $isApi);
            $routes->add($this->getRouteName($metadata, $configuration, 'update'), $updateRoute);
        }

        if (in_array('show', $routesToGenerate)) {
            $showRoute = $this->createRoute($metadata, $configuration, $rootPath . $identifier, 'show', ['GET'], $isApi);
            $routes->add($this->getRouteName($metadata, $configuration, 'show'), $showRoute);
        }

        if (in_array('delete', $routesToGenerate)) {
            $deleteRoute = $this->createRoute($metadata, $configuration, $rootPath . $identifier, 'delete', ['DELETE'], $isApi);
            $routes->add($this->getRouteName($metadata, $configuration, 'delete'), $deleteRoute);
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'mert_oksuz.api' === $type;
    }

    private function createRoute(MetadataInterface $metadata, array $configuration, $path, $actionName, array $methods, $isApi = false)
    {
        $defaults = [
            '_controller' => $metadata->getServiceId('controller').sprintf(':%sAction', $actionName),
        ];

        if ($isApi && 'index' === $actionName) {
            $defaults['_sylius']['serialization_groups'] = ['Default'];
        }
        if ($isApi && in_array($actionName, ['show', 'create', 'update'], true)) {
            $defaults['_sylius']['serialization_groups'] = ['Default', 'Detailed'];
        }
        if ($isApi && 'delete' === $actionName) {
            $defaults['_sylius']['csrf_protection'] = false;
        }
        if (isset($configuration['grid']) && 'index' === $actionName) {
            $defaults['_sylius']['grid'] = $configuration['grid'];
        }
        if (isset($configuration['form']) && in_array($actionName, ['create', 'update'], true)) {
            $defaults['_sylius']['form'] = $configuration['form'];
        }
        if (isset($configuration['serialization_version'])) {
            $defaults['_sylius']['serialization_version'] = $configuration['serialization_version'];
        }
        if (isset($configuration['section'])) {
            $defaults['_sylius']['section'] = $configuration['section'];
        }
        if (!empty($configuration['criteria'])) {
            $defaults['_sylius']['criteria'] = $configuration['criteria'];
        }
        if (isset($configuration['templates']) && in_array($actionName, ['show', 'index', 'create', 'update'], true)) {
            $defaults['_sylius']['template'] = sprintf('%s:%s.html.twig', $configuration['templates'], $actionName);
        }
        if (isset($configuration['redirect']) && in_array($actionName, ['create', 'update'], true)) {
            $defaults['_sylius']['redirect'] = $this->getRouteName($metadata, $configuration, $configuration['redirect']);
        }
        if (isset($configuration['permission'])) {
            $defaults['_sylius']['permission'] = $configuration['permission'];
        }
        if (isset($configuration['vars']['all'])) {
            $defaults['_sylius']['vars'] = $configuration['vars']['all'];
        }
        if (isset($configuration['vars'][$actionName])) {
            $vars = isset($configuration['vars']['all']) ? $configuration['vars']['all'] : [];
            $defaults['_sylius']['vars'] = array_merge($vars, $configuration['vars'][$actionName]);
        }

        return $this->createMainRoute($path, $defaults, [], [], '', [], $methods);
    }

    /**
     * {@inheritdoc}
     */
    public function createMainRoute($path, array $defaults = [], array $requirements = [], array $options = [], $host = '', $schemes = [], $methods = [], $condition = '')
    {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
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
}