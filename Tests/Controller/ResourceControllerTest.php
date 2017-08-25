<?php
namespace Nedra\RestBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Nedra\RestBundle\Controller\RequestConfigurationFactory;
use Nedra\RestBundle\Controller\RequestConfigurationInterface;
use Nedra\RestBundle\Controller\RequestFormConfigurationInterface;
use Nedra\RestBundle\Controller\ResourceController;
use Nedra\RestBundle\DependencyInjection\Compiler\RegistryRegisterPass;
use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\Metadata\RegistryInterface;
use Nedra\RestBundle\Tests\DependencyInjection\AppKernel;
use Nedra\RestBundle\Tests\DependencyInjection\ContainerFactory;
use Nedra\RestBundle\Tests\DependencyInjection\Models\Book;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
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

    /** @var RegistryInterface */
    private $registry;

    /** @var RequestConfigurationInterface */
    private $requestFactory;

    /** @var EntityManager */
    private $em;

    /** @var RequestFormConfigurationInterface */
    private $formFactory;

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

    public function test_index_action()
    {
        $controller = $this->getConstructor();

        $book = new Book();
        $book->setTitle('Test Book');
        $book->setDescription('i am description of book 1');
        $book->setId(1);

        $view = new View();
        $view->setData($book);

        $viewHandler = $this->getMockBuilder(ViewHandler::class)->disableOriginalConstructor()->getMock();
        $viewHandler->expects($this->any())->method('handle')->willReturn($view->getData());
        $controller->setViewHandler($viewHandler);

        $request = new Request();
        $request->attributes->set('_nedrarest', ['model' => 'Nedra\RestBundle\Tests\DependencyInjection\Models\Book']);
        $result = $controller->indexAction($request);

        $this->assertInstanceOf('Nedra\RestBundle\Tests\DependencyInjection\Models\Book', $result);
    }

    private function getConstructor()
    {
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setDescription('i am description of book 1');
        $book->setId(1);

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

        $pass = new RegistryRegisterPass();
        $pass->process($container);

        /** @var RequestConfigurationInterface $requestFactory */
        $this->requestFactory = $container->get("nedra_rest.request_configuration_factory");

        /** @var RegistryInterface $registry */
        $this->registry = $container->get("nedra_rest.registry");

        $container->register("form.factory", FormFactory::class)->addArgument($this->container->get("form.registry"))->addArgument("form.resolved_type_factory");

        /** @var RequestFormConfigurationInterface $formFactory */
        $this->formFactory = $container->get("nedra_rest.request_form_factory");

        $bookRepository = $this->getMockBuilder(ObjectRepository::class)->setMethods(['find', 'findAll', 'findBy', 'findOneBy', 'getClassName'])->getMock();
        $bookRepository->expects($this->any())->method('findAll')->willReturn($book);

        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $this->em ->expects($this->any())->method("getRepository")->willReturn($bookRepository);

        $controller = new ResourceController($this->registry, $this->requestFactory, $this->em, $this->formFactory);
        $controller->setContainer($this->container);

        return $controller;
    }
}
