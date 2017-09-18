<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use FOS\RestBundle\FOSRestBundle;
use Nedra\RestBundle\Metadata\MetadataInterface;
use Nedra\RestBundle\Controller\ResourceController;
use Nedra\RestBundle\DependencyInjection\Compiler\AddRouteCollectionProvidersCompilerPass;
use Nedra\RestBundle\DependencyInjection\Compiler\RegistryRegisterPass;
use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\Form\Type\DefaultResourceType;
use Nedra\RestBundle\Component\RegistryInterface;
use Nedra\RestBundle\NedraRestBundle;
use Nedra\RestBundle\Routing\ModularRouter;
use Nedra\RestBundle\Routing\ModularRouterInterface;
use Nedra\RestBundle\Routing\Provider\RouteCollectionProvider;
use Psr\Log\InvalidArgumentException;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class ResourceControllerTest extends TestCase
{
//    /**
//     * @var \Symfony\Component\HttpKernel\Kernel
//     */
//    protected $kernel;
//
//    /**
//     * @var \Symfony\Component\DependencyInjection\Container
//     */
//    protected $container;
//
//    /**
//     * @return null
//     */
//    public function setUp()
//    {
//        $this->kernel = new AppKernel('test', true);
//        $this->kernel->boot();
//
//        $this->container = $this->kernel->getContainer();
//
//        parent::setUp();
//    }
//
//    public function test_no_nedra_rest_bundle_when_active_is_false()
//    {
//        $container = ContainerFactory::createContainer("disabled.yml");
//        $this->assertFalse($container->has(NedraRestBundle::REGISTRY_ID));
//    }
//
//    public function test_if_no_fos_rest_bundle_then_no_nedra_rest()
//    {
//        $container = ContainerFactory::createContainer(null, []);
//        $this->assertFalse($container->hasExtension("fos_rest"));
//    }
//
//    public function test_if_yes_fos_rest_bundle_then_yes_nedra_rest()
//    {
//        $container = ContainerFactory::createContainer(null, [new FOSRestExtension()]);
//        $this->assertTrue($container->hasExtension("fos_rest"));
//    }
//
//    public function test_if_no_cmf_routing_bundle_then_no_nedra_rest()
//    {
//        $container = ContainerFactory::createContainer(null, []);
//        $this->assertFalse($container->hasExtension("cmf"));
//    }
//
//    public function test_if_yes_cmf_routing_bundle_then_yes_nedra_rest()
//    {
//        $container = ContainerFactory::createContainer(null, [new CmfRoutingExtension()]);
//        $this->assertTrue($container->hasExtension("cmf_routing"));
//    }
//
//    public function test_if_nedra_rest_configured_and_request_has_meta_data()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    'app.book' => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//        $bundle = new NedraRestBundle();
//        $bundle->build($container);
//
//        $requestFactory = $container->get("nedra_rest.request_configuration_factory");
//        $registry = $container->get("nedra_rest.registry");
//
//        $container->register("nedra_rest.form_factory", "Nedra\RestBundle\Controller\RequestFormConfiguration")
//            ->addArgument($this->container->get("form.factory"))
//            ->addArgument($registry);
//
//
//        $registry->addFromAliasAndConfiguration("app.book",
//            [
//                'driver' => 'doctrine',
//                'classes' => ['model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book']
//
//            ]
//        );
//
//        $req = new Request();
//        $req->attributes->add([
//            '_nedrarest' => [
//                'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//            ]
//        ]);
//
//        $model = $requestFactory->create($registry, $req);
//        $this->assertTrue(($model instanceof MetadataInterface)?true:false);
//    }
//
//    public function test_if_nedra_rest_active_then_create_routes_by_given_entities()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    'app.book' => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//        $bundle = new NedraRestBundle();
//        $bundle->build($container);
//
//        $routeProvider = $container->get("nedra_rest.route_provider");
//        $routes = $routeProvider->getRouteCollection();
//
//        if ($routes) {
//            $this->assertArrayHasKey("app_book_index", $routes->all());
//            $this->assertArrayHasKey("app_book_create", $routes->all());
//            $this->assertArrayHasKey("app_book_update", $routes->all());
//            $this->assertArrayHasKey("app_book_show", $routes->all());
//            $this->assertArrayHasKey("app_book_delete", $routes->all());
//        }
//    }
//
//    public function test_metadata()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "app.book" => [
//                        "driver" => "doctrine/orm",
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//
//        $pass = new RegistryRegisterPass();
//        $pass->process($container);
//
//        /** @var MetadataInterface $metadata */
//        $metadata = $container->get("nedra_rest.registry")->get("app.book");
//
//        $this->assertEquals("book", $metadata->getHumanizedName());
//        $this->assertEquals("doctrine/orm", $metadata->getDriver());
//        $this->assertArrayHasKey("identifier", $metadata->getParameters());
//        $this->assertEquals("id", $metadata->getParameter("identifier"));
//        $this->assertTrue(($metadata->hasParameter("identifier"))?true:false);
//        $this->assertEquals("app.test_service.book", $metadata->getServiceId("test_service"));
//        $this->assertEquals("app.book.test_permission", $metadata->getPermissionCode("test_permission"));
//
//        $parsedAlias = $metadata::fromAliasAndConfiguration("app.book", $metadata->getParameters());
//        $this->assertInstanceOf(MetadataInterface::class, $parsedAlias);
//    }
//
//
//    public function test_if_alias_format_is_wrong()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "appbook" => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        try {
//            $container = ContainerFactory::createDummyContainer();
//            $container->setParameter("nedrarest.config", $config);
//            $ext = new NedraRestExtension();
//            $ext->load($config, $container);
//
//            $pass = new RegistryRegisterPass();
//            $pass->process($container);
//
//            /** @var RegistryInterface $registry */
//            $metadata = $container->get("nedra_rest.registry")->getAll();
//
//        } catch (\InvalidArgumentException $exception) {
//            $this->assertEquals('Invalid alias supplied, it should conform to the following format "<applicationName>.<name>".', $exception->getMessage());
//        }
//    }
//
//    public function test_if_class_defined_but_not_in_registry()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "app.book" => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        try {
//            $container = ContainerFactory::createDummyContainer();
//            $container->setParameter("nedrarest.config", $config);
//            $ext = new NedraRestExtension();
//            $ext->load($config, $container);
//
//            $pass = new RegistryRegisterPass();
//            $pass->process($container);
//
//            /** @var RegistryInterface $registry */
//            $registry = $container->get("nedra_rest.registry")->getByClass("Nedra\RestBundle\Tests\DependencyInjection\Models\Flower");
//
//
//        } catch (\InvalidArgumentException $exception) {
//            $this->assertEquals("Resource with model class \"Nedra\RestBundle\Tests\DependencyInjection\Models\Flower\" does not exist.", $exception->getMessage());
//        }
//    }
//
//
//    public function test_fos_rest_if_fos_configured()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "app.book" => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $fos_config = [
//            'fos_rest' => [
//                'format_listener' => [
//                    'rules' => [
//                        'host' => null
//                    ]
//                ]
//            ]
//        ];
//
//        $fosext = new FOSRestExtension();
//        $fosext->load($fos_config, $container);
//        $container->registerExtension($fosext);
//        $container->prependExtensionConfig("fos_rest", $fos_config['fos_rest']);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//        $container->registerExtension($ext);
//
//        $ext->prepend($container);
//
//        $fos_rest_config = $container->getExtensionConfig('fos_rest');
//        $this->assertArrayHasKey("path", $fos_rest_config[0]['format_listener']['rules']);
//    }
//
//    public function test_fos_rest_if_fos_not_configured()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "app.book" => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book'
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $fos_config = [
//            'fos_rest' => [
//                'format_listener' => []
//            ]
//        ];
//        $fosext = new FOSRestExtension();
//        $fosext->load($fos_config, $container);
//        $container->registerExtension($fosext);
//        $container->prependExtensionConfig("fos_rest", $fos_config);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//        $container->registerExtension($ext);
//
//        $ext->prepend($container);
//
//        $fos_rest_config = $container->getExtensionConfig('fos_rest');
//        $this->assertArrayHasKey("rules", $fos_rest_config[0]['format_listener']);
//    }
//
//    public function test_if_no_classes_defined_then_error()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    "app.book" => []
//                ]
//            ]
//        ];
//
//        try {
//            $container = ContainerFactory::createDummyContainer();
//            $container->setParameter("nedrarest.config", $config);
//            $ext = new NedraRestExtension();
//            $ext->load($config, $container);
//        } catch (InvalidConfigurationException $exception) {
//            $this->assertEquals("The child node \"classes\" at path \"nedra_rest.entities.app.book\" must be configured.", $exception->getMessage());
//        }
//    }
//
//    public function test_if_no_alias_defined_then_error()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//
//        try {
//            $container->get("nedra_rest.registry")->get("app.book");
//        } catch (\InvalidArgumentException $exception) {
//            $this->assertEquals("Resource \"app.book\" does not exist.", $exception->getMessage());
//        }
//
//    }
//
//    public function test_if_only_and_except_options_defined_then_error()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    'app.book' => [
//                        'only' => ['index'],
//                        'except' => ['show'],
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        try {
//            $container = ContainerFactory::createDummyContainer();
//            $container->setParameter("nedrarest.config", $config);
//
//            $ext = new NedraRestExtension();
//            $ext->load($config, $container);
//            $bundle = new NedraRestBundle();
//            $bundle->build($container);
//        } catch (InvalidArgumentException $exception) {
//            $this->assertTrue($exception->getMessage());
//        }
//    }
//
//    public function test_registry_compiler_pass()
//    {
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    'app.book' => [
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//        $container->registerExtension($ext);
//
//        $container->register("form.factory", FormFactory::class)->addArgument($this->container->get("form.registry"))->addArgument("form.resolved_type_factory");
//
//        $locator = new FileLocator(__DIR__ . '/Fixtures');
//        $loader = new YamlFileLoader($container, $locator);
//        $loader->load('services.yml');
//
//        $pass = new RegistryRegisterPass();
//        $pass->process($container);
//
//        $registry = $container->get("nedra_rest.registry")->get("app.book");
//
//        $this->assertEquals("Nedra\RestBundle\Tests\DependencyInjection\Models\Book", $registry->getClass("model"));
//
//    }
//
//    public function test_modular_routing_class()
//    {
//
//        $config = [
//            'nedra_rest' => [
//                'entities' => [
//                    'app.book' => [
//                        'driver' => 'doctrine/orm',
//                        'identifier' => 'id',
//                        'classes' => [
//                            'model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book',
//                            'controller' => ResourceController::class,
//                            'form' => DefaultResourceType::class
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//        $container = ContainerFactory::createDummyContainer();
//        $container->setParameter("nedrarest.config", $config);
//
//        $ext = new NedraRestExtension();
//        $ext->load($config, $container);
//
//        $taggedServices = $container->findTaggedServiceIds('router');
//
//        if (!$taggedServices) {
//            $this->assertFalse(true);
//        }
//
//        foreach ($taggedServices as $id => $tags) {
//            $modularRouting = new $id;
//            if ($modularRouting instanceof ModularRouterInterface) {
//                $modularRouting->addRouteCollectionProvider(new RouteCollectionProvider($config['nedra_rest']));
//                $this->assertArrayHasKey("app_book_index", $modularRouting->getRouteCollection()->all());
//            } else {
//                $this->assertFalse(false);
//            }
//        }
//    }
}
