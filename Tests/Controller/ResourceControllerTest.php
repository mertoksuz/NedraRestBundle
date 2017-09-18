<?php
namespace Nedra\RestBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Tests\View\ViewHandlerTest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Nedra\RestBundle\Controller\ResourceController;
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

        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->setMethods(['find', 'findAll'])->getMock();

        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->attributes = $parameterBag;
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

        $entity = $this->getTestEntity();
        $request = $this->getFindAllConfig($controller);
        $this->assertEquals($entity, $controller->findAllOr404($request));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The "Whatever" has not been found
     */
    public function test_find_or_404_findAll_fail()
    {
        $controller = new ResourceController();

        $repository = $this->findAllByEntity(null);

        $entityManager = $this->getEntityManager($repository);
        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->attributes = $parameterBag;
        $controller->findAllOr404($request);
    }

    public function test_index_action()
    {
        $controller = new ResourceController();

        $request = $this->getFindAllConfig($controller);
        $this->requestStack->push($request);

        $viewHandler = $this->createViewHandler(['json' => true, 'html' => false, 'xml' => false]);

        $viewHandler->registerHandler('json', function() {
            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
            return $serializer->serialize($this->getTestEntities(), 'json');
        });

        $controller->setViewHandler($viewHandler);
        $this->assertEquals('[{"id":1,"title":"My Title","description":"My Description"},{"id":2,"title":"My Title 1","description":"My Description 1"}]', $controller->indexAction($request));

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
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The "Whatever" has not been found
     */
    public function test_index_action_fail()
    {
        $controller = new ResourceController();

        $entity = null;

        $request = $this->getFindAllConfig($controller, null);

        $serializer = $this->getSerializer();
        $jsonData = $serializer->serialize($entity, 'json');

        $response = $this->getResponse($jsonData, 200);

        $viewHandler = $this->getMockBuilder(ViewHandler::class)->disableOriginalConstructor()->setMethods(['handle'])->getMock();
        $viewHandler->expects($this->any())->method('handle')->willReturn($response->getContent());

        $controller->setViewHandler($viewHandler);

        $controller->indexAction($request);
    }

    public function test_show_action()
    {
        $controller = new ResourceController();
        $entity = $this->getTestEntity();

        $request = $this->buildRequestWithFind($entity, $controller);

        $serializer = $this->getSerializer();
        $jsonData = $serializer->serialize($entity, 'json');

        $response = $this->getResponse($jsonData, 200);

        $viewHandler = $this->getMockBuilder(ViewHandler::class)->disableOriginalConstructor()->setMethods(['handle'])->getMock();
        $viewHandler->expects($this->any())->method('handle')->willReturn($response->getContent());

        $controller->setViewHandler($viewHandler);

        $this->assertEquals('{"id":1,"title":"My Title","description":"My Description"}', $controller->showAction($entity->getId(), $request));
    }

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager($repository)
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);
        return $entityManager;
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    private function getSerializer()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
        return $serializer;
    }

    /**
     * @param $jsonData
     * @param $statusCode
     * @return Response
     */
    private function getResponse($jsonData, $statusCode)
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent($jsonData);
        return $response;
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFindAllConfig($controller, $return = true)
    {
        $repository = $this->findAllByEntity($return);
        $entityManager = $this->getEntityManager($repository);

        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();
        $request = new Request();
        $request->attributes = $parameterBag;

        return $request;
    }

    /**
     * @param $entity
     * @param $controller
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildRequestWithFind($entity, $controller)
    {
        $repository = $this->findByEntity($entity);

        $entityManager = $this->getEntityManager($repository);
        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->attributes = $parameterBag;

        return $request;
    }
}
