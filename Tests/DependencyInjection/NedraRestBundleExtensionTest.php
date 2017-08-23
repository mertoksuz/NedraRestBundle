<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use PHPUnit\Framework\TestCase;


class ResourceControllerTest extends TestCase
{
    public function test_no_nedra_rest_bundle_when_active_is_false()
    {
        $container = ContainerFactory::createContainer("disabled.yml");
        $this->assertFalse($container->has(NedraRestBundle::REGISTRY_ID));
    }

    public function test_if_no_fos_rest_bundle_then_no_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, []);
        $this->assertFalse($container->hasExtension("fos_rest"));
    }

    public function test_if_yes_fos_rest_bundle_then_yes_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, [new FOSRestExtension()]);
        $this->assertTrue($container->hasExtension("fos_rest"));
    }

    public function test_if_no_cmf_routing_bundle_then_no_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, []);
        $this->assertFalse($container->hasExtension("cmf"));
    }

    public function test_if_yes_cmf_routing_bundle_then_yes_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, [new CmfRoutingExtension()]);
        $this->assertTrue($container->hasExtension("cmf_routing"));
    }

    public function test_if_nedra_rest_active_then_create_routes_by_given_entities()
    {
        $config = [
            'nedra_rest' => [
                'entities' => [
                    'app.book' => [
                        'only'  => ['index'],
                        'classes' => [
                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Test',
                        ]
                    ]
                ]
            ]
        ];

        $container = ContainerFactory::createDummyContainer();
        $container->setParameter("nedrarest.config", $config);

        $ext = new NedraRestExtension();
        $ext->load($config, $container);
        $container->registerExtension($ext);
        $bundle = new NedraRestBundle();
        $bundle->build($container);

        $routeProvider = $container->get("nedra_rest.route_provider");
        $routes = $routeProvider->getRouteCollection();

        if ($routes) {
            $this->assertArrayHasKey("app_book_index", $routes->all());
        }
    }
}