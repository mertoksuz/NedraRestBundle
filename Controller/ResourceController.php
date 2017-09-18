<?php

namespace Nedra\RestBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Metadata\ClassMetadata;
use Nedra\RestBundle\Metadata\MetadataInterface;
use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ResourceController
 * @package Nedra\RestBundle\Controller
 */
class ResourceController extends FOSRestController
{
    /** @var EntityManager */
    private $entityManager;

    /** @var RequestFormConfigurationInterface */
    private $requestFormFactory;

    /** @var \Doctrine\ORM\Mapping\ClassMetadata */
    private $classMetaData;

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetaData
     */
    public function setClassMetaData($classMetaData)
    {
        $this->classMetaData = $classMetaData;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param RequestFormConfigurationInterface $requestFormFactory
     */
    public function setRequestFormFactory($requestFormFactory)
    {
        $this->requestFormFactory = $requestFormFactory;
    }

    public function indexAction(Request $request)
    {
        $result = $this->findAllOr404($request);

        $view = new View();
        $view->setData($result);
        $view->setFormat('json');

        return $this->handleView($view);
    }

    public function showAction($id, Request $request)
    {
        $result = $this->findOr404($request, $id);

        $view = new View();
        $view->setData($result);
        return $this->handleView($view);
    }

    public function deleteAction($id, Request $request)
    {
        $result = $this->findOr404($request, $id);

        $this->entityManager->remove($result);
        $this->entityManager->flush();

        $view = new View();
        $view->setData(null);
        $view->setStatusCode(Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }

    public function updateAction($id, Request $request)
    {
        $result = $this->findOr404($request, $id);

        /** @var FormInterface $form */
        $form = $this->requestFormFactory->create($this->metadata, $request, $result);

        $form->handleRequest($request);

        $this->entityManager->flush();
        $this->entityManager->refresh($result);

        $view = new View();
        $view->setData($result);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return array|null|object
     */
    public function findOr404(Request $request, $id = null)
    {
        $model = $request->attributes->get("_nedrarest_model");
        $result = $this->entityManager->getRepository($model)->find($id);

        if (!$result) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $model));
        }

        return $result;
    }

    public function findAllOr404(Request $request)
    {
        $model = $request->attributes->get("_nedrarest_model");
        $result = $this->entityManager->getRepository($model)->findAll();

        if (!$result) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $model));
        }

        return $result;
    }
}
