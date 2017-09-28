<?php
namespace Nedra\RestBundle\Tests\DI;

use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Nedra\RestBundle\Routing\Provider\RouteCollectionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ConfigurationTest extends TestCase
{
    public function test_bundle_is_active()
    {
        $container = $this->createContainer();
        $container->compile();
        $this->assertArrayHasKey('nedra_rest.route_provider', $container->getDefinitions());
    }

    public function test_bundle_is_not_active()
    {
        $container = $this->createContainer('config_disabled.yml');
        $this->assertArrayNotHasKey('nedra_rest.route_provider', $container->getDefinitions());
    }

    public function test_routes_are_generated_by_given_entities()
    {
        $container = $this->createContainer();
        $config = $this->getNedraRestConfig($container);

        $routeCollectionProvider = new RouteCollectionProvider($config);
        $routes = $routeCollectionProvider->getRouteCollection();

        $this->assertArraySubset(['app_model_index', 'app_model_create', 'app_model_update', 'app_model_show', 'app_model_delete'], array_keys($routes->all()));
    }

    public function test_only_route_are_generated_by_given_entities()
    {
        $container = $this->createContainer('config_only.yml');
        $config = $this->getNedraRestConfig($container);

        $routeCollectionProvider = new RouteCollectionProvider($config);
        $routes = $routeCollectionProvider->getRouteCollection();

        $this->assertArraySubset(['app_model_index', 'app_model_create'], array_keys($routes->all()));
    }

    public function test_except_route_are_generated_by_given_entities()
    {
        $container = $this->createContainer('config_except.yml');
        $config = $this->getNedraRestConfig($container);

        $routeCollectionProvider = new RouteCollectionProvider($config);
        $routes = $routeCollectionProvider->getRouteCollection();

        $this->assertArraySubset(['app_model_index', 'app_model_show', 'app_model_delete'], array_keys($routes->all()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You can configure only one of "except" & "only" options.
     */
    public function test_throw_error_when_only_and_except_defined()
    {
        $container = $this->createContainer('config_except_only.yml');
        $config = $this->getNedraRestConfig($container);

        $routeCollectionProvider = new RouteCollectionProvider($config);
        $routeCollectionProvider->getRouteCollection();
    }

    private function createContainer($config = 'config.yml')
    {
        /** @var ContainerBuilder $container */
        $container = new ContainerBuilder(new ParameterBag([]));

        $bundle = new NedraRestBundle();
        $bundle->build($container);

        $container->registerExtension(new NedraRestExtension());

        $fileLocator = new FileLocator([__DIR__]);
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load($config);

        //$container->compile();

        return $container;
    }

    /**
     * @param $container
     *
     * @return array
     */
    private function getNedraRestConfig($container)
    {
        $configs = $container->getExtensionConfig('nedra_rest');

        $config = [];
        if (isset($configs[0])) {
            $config = $configs[0];
        }

        return $config;
    }
}
