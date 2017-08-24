<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use Nedra\RestBundle\Component\MetadataInterface;
use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Psr\Log\InvalidArgumentException;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ResourceControllerTest extends TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @return null
     */
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();

        parent::setUp();
    }

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

    public function test_if_nedra_rest_configured_and_request_has_meta_data()
    {
        $config = [
            'nedra_rest' => [
                'entities' => [
                    'app.book' => [
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

        $requestFactory = $container->get("nedra_rest.request_configuration_factory");
        $registry = $container->get("nedra_rest.registry");

        $container->register("nedra_rest.form_factory", "Nedra\RestBundle\Controller\RequestFormConfiguration")
            ->addArgument($this->container->get("form.factory"))
            ->addArgument($registry);


        $registry->addFromAliasAndConfiguration("app.book",
            [
                'driver' => 'doctrine',
                'classes' => ['model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book']

            ]
        );

        $req = new Request();
        $req->attributes->add([
            '_nedrarest' => [
                'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
            ]
        ]);

        $model = $requestFactory->create($registry, $req);
        $this->assertTrue(($model instanceof MetadataInterface)?true:false);
    }

    public function test_if_nedra_rest_active_then_create_routes_by_given_entities()
    {
        $config = [
            'nedra_rest' => [
                'entities' => [
                    'app.book' => [
                        'classes' => [
                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
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
            $this->assertArrayHasKey("app_book_create", $routes->all());
            $this->assertArrayHasKey("app_book_update", $routes->all());
            $this->assertArrayHasKey("app_book_show", $routes->all());
            $this->assertArrayHasKey("app_book_delete", $routes->all());
        }
    }

    public function test_if_only_and_except_options_defined_then_error()
    {
        $config = [
            'nedra_rest' => [
                'entities' => [
                    'app.book' => [
                        'only' => ['index'],
                        'except' => ['show'],
                        'classes' => [
                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
                        ]
                    ]
                ]
            ]
        ];

        try {
            $container = ContainerFactory::createDummyContainer();
            $container->setParameter("nedrarest.config", $config);

            $ext = new NedraRestExtension();
            $ext->load($config, $container);
            $container->registerExtension($ext);
            $bundle = new NedraRestBundle();
            $bundle->build($container);
        } catch (InvalidArgumentException $exception) {
            $this->assertTrue($exception->getMessage());
        }
    }
}
