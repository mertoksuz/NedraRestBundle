<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use Nedra\RestBundle\Controller\ResourceController;
use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Nedra\RestBundle\Tests\DependencyInjection\Models\Book;


class ResourceControllerTest extends TestCase
{

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $kernel;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @return null
     */
    public function setUp()
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();

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

    public function test_resource_controller()
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
            ->addArgument($registry)
            ->addArgument($this->entityManager);


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

        $controller = new ResourceController($registry, $requestFactory, $this->entityManager, $container->get("nedra_rest.form_factory"));

        if ($controller) {
            $this->assertTrue(true);
        }
    }

    public function test_if_nedra_rest_active_then_create_routes_by_given_entities()
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
}