<?php
namespace Nedra\RestBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\RestBundle\View\ViewHandler;
use Nedra\RestBundle\Controller\RequestFormConfigurationInterface;
use Nedra\RestBundle\Controller\ResourceController;
use Nedra\RestBundle\Form\Type\DefaultResourceType;
use Nedra\RestBundle\Tests\DependencyInjection\Models\Book;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ResourceControllerTest extends TestCase
{
    private $router;

    private $serializer;

    private $templating;

    private $requestStack;

    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->serializer = $this->getMockBuilder('FOS\RestBundle\Serializer\Serializer')->disableOriginalConstructor()->setMethods(['serialize', 'deserialize'])->getMock();
        $this->templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')->getMock();
        $this->requestStack = new RequestStack();

        parent::setUp();
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The "Whatever" has not been found
     */
    public function test_find_or_404_fail()
    {
        $controller = new ResourceController();
        $request = $this->buildRequestWithFind(null, $controller);
        $controller->findOr404($request);
    }

    public function test_find_or_404_find()
    {
        $controller = new ResourceController();

        $entity = new \StdClass();
        $request = $this->buildRequestWithFind($entity, $controller);
        $this->assertEquals($entity, $controller->findOr404($request, 1));
    }

    public function test_find_or_404_findAll()
    {
        $controller = new ResourceController();

        $entities = $this->getTestEntities();
        $request = $this->buildRequestWithFindAll($controller);
        $this->assertEquals($entities, $controller->findAllOr404($request));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The "Whatever" has not been found
     */
    public function test_find_or_404_findAll_fail()
    {
        $controller = new ResourceController();

        $request = $this->buildRequestWithFindAll($controller, null);
        $controller->findAllOr404($request);
    }

    public function test_index_action()
    {
        $controller = new ResourceController();

        $request = $this->buildRequestWithFindAll($controller);

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);
        $this->jsonHandler($viewHandler);

        $controller->setViewHandler($viewHandler);
        $this->assertEquals('[{"id":1,"title":"My Title","description":"My Description"},{"id":2,"title":"My Title 1","description":"My Description 1"}]', $controller->indexAction($request));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The "Whatever" has not been found
     */
    public function test_index_action_fail()
    {
        $controller = new ResourceController();
        $entity = null;

        $request = $this->buildRequestWithFindAll($controller, null);
        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);

        $controller->setViewHandler($viewHandler);
        $controller->indexAction($request);
    }

    public function test_show_action()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();

        $request = $this->buildRequestWithFind($entity, $controller);
        $serializer = $this->getSerializer();

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);
        $this->jsonHandler($viewHandler);

        $controller->setViewHandler($viewHandler);

        $this->assertEquals('{"id":1,"title":"My Title","description":"My Description"}', $controller->showAction($entity->getId(), $request));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not Found
     */
    public function test_show_action_fail()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();

        $request = $this->buildRequestWithFind($entity, $controller);

        $viewHandler = $this->getMockBuilder(ViewHandler::class)->disableOriginalConstructor()->setMethods(['handle'])->getMock();
        $viewHandler->expects($this->any())->method('handle')->willThrowException(new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Not Found'));

        $controller->setViewHandler($viewHandler);
        $controller->showAction(3, $request);
    }

    public function test_delete_action()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();
        $request = $this->buildRequestWithFind($entity, $controller);

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);

        $viewHandler->registerHandler('json', function ($handler, $view) use (&$serializer) {
            return $view->getData();
        });

        $controller->setViewHandler($viewHandler);

        $this->assertEquals(null, $controller->deleteAction($entity->getId(), $request));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not Found
     */
    public function test_delete_action_fail()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();
        $request = $this->buildRequestWithFind($entity, $controller, null, true);

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);
        $controller->setViewHandler($viewHandler);

        $controller->deleteAction($entity->getId(), $request);
    }

    public function test_update_action()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();
        $request = $this->buildRequestWithFind($entity, $controller);

        $request->request->set('title', 'New Title');

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);
        $this->jsonHandler($viewHandler);
        $controller->setViewHandler($viewHandler);

        $formFactory = $this->getFormFactory($entity);
        $controller->setRequestFormFactory($formFactory);

        $this->assertEquals('{"id":1,"title":"New Title","description":"My Description"}', $controller->updateAction($entity->getId(), $request));
    }

    /**
     * @return ParameterBag
     */
    private function getParameterBag()
    {

        $parameterBag = new ParameterBag();
        $parameterBag->set('_nedrarest_model', 'Whatever');

        return $parameterBag;
    }

    /**
     * @param $entity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function findByEntity($entity)
    {
        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->setMethods(['find', 'findAll'])->getMock();
        $repository->expects($this->any())->method('find')->willReturn($entity);

        return $repository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function findAllByEntity($return = true)
    {
        $entity = $this->getTestEntities();
        if (is_null($return)) {
            $entity = null;
        }

        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->setMethods(['find', 'findAll'])->getMock();
        $repository->expects($this->any())->method('findAll')->willReturn($entity);

        return $repository;
    }

    /**
     * @param $repository
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager($repository, $remove = null, $removeException = false)
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository', 'remove', 'flush', 'refresh'])->getMock();
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        if ($removeException) {
            $entityManager->expects($this->any())->method('remove')->willThrowException(new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Not Found"));
        } else {
            $entityManager->expects($this->any())->method('remove')->willReturn($remove);
        }

        return $entityManager;
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    private function getSerializer()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);

        return $serializer;
    }

    /**
     * @return Book
     */
    private function getTestEntity()
    {
        $entity = new Book();
        $entity->setTitle('My Title');
        $entity->setDescription('My Description');
        $entity->setId(1);

        return $entity;
    }

    /**
     * @return $collection
     */
    private function getTestEntities()
    {
        $collection = new ArrayCollection();

        $entity = new Book();
        $entity->setTitle('My Title');
        $entity->setDescription('My Description');
        $entity->setId(1);

        $entity2 = new Book();
        $entity2->setTitle('My Title 1');
        $entity2->setDescription('My Description 1');
        $entity2->setId(2);

        $collection->add($entity);
        $collection->add($entity2);

        return $collection;
    }

    /**
     * @param $controller
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildRequestWithFindAll($controller, $return = true)
    {
        $repository = $this->findAllByEntity($return);
        $entityManager = $this->getEntityManager($repository);

        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();
        $request = new Request();
        $request->attributes = $parameterBag;
        $this->requestStack->push($request);

        return $request;
    }

    /**
     * @param      $entity
     * @param      $controller
     * @param null $remove
     * @param bool $removeException
     *
     * @return Request
     */
    private function buildRequestWithFind($entity, $controller, $remove = null, $removeException = false)
    {
        $repository = $this->findByEntity($entity);

        $entityManager = $this->getEntityManager($repository, $remove, $removeException);
        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();
        $request = new Request();
        $request->attributes = $parameterBag;
        $this->requestStack->push($request);

        return $request;
    }

    private function createViewHandler($formats = null, $failedValidationCode = Response::HTTP_BAD_REQUEST, $emptyContentCode = Response::HTTP_NO_CONTENT, $serializeNull = false, $forceRedirects = null, $defaultEngine = 'twig')
    {
        return new ViewHandler(
            $this->router,
            $this->serializer,
            $this->templating,
            $this->requestStack,
            $formats,
            $failedValidationCode,
            $emptyContentCode,
            $serializeNull,
            $forceRedirects,
            $defaultEngine
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormFactory(Book $entity)
    {

        $form = $this->getMockBuilder(DefaultResourceType::class)->disableOriginalConstructor()->setMethods(['handleRequest'])->getMock();
        $form->expects($this->any())->method('handleRequest')->will($this->returnCallback(function ($arg) use (&$entity) {
            $title = $arg->request->get('title');
            $entity->setTitle($title);

            return $entity;
        }));
        $formFactory = $this->getMockBuilder(RequestFormConfigurationInterface::class)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $formFactory->expects($this->any())->method('create')->willReturn($form);

        return $formFactory;
    }

    /**
     * @param $viewHandler
     */
    private function jsonHandler($viewHandler)
    {
        $serializer = $this->getSerializer();

        $viewHandler->registerHandler('json', function ($handler, $view) use (&$serializer) {
            return $serializer->serialize($view->getData(), 'json');
        });
    }
}
