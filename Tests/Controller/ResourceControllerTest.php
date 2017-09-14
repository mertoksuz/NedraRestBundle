<?php
namespace Nedra\RestBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nedra\RestBundle\Controller\ResourceController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ResourceControllerTest extends TestCase
{
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
        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->setMethods(['find', 'findAll'])->getMock();
        $repository->expects($this->any())->method('find')->willReturn($entity);

        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->attributes = $parameterBag;
        $this->assertEquals($entity, $controller->findOr404($request, 1));
    }

    public function test_find_or_404_findAll()
    {
        $controller = new ResourceController();

        $entity = new \StdClass();
        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->setMethods(['find', 'findAll'])->getMock();
        $repository->expects($this->any())->method('findAll')->willReturn($entity);

        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        $controller->setEntityManager($entityManager);

        $parameterBag = $this->getParameterBag();

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->attributes = $parameterBag;
        $this->assertEquals($entity, $controller->findOr404($request));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParameterBag()
    {
        $parameterBag = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $parameterBag
            ->expects($this->any())
            ->method('get')
            ->with('_nedrarest_model')
            ->willReturn('Whatever');
        return $parameterBag;
    }
}
