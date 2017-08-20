<?php

namespace Nedra\RestBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nedra\RestBundle\Component\MetadataInterface;
use Nedra\RestBundle\Metadata\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ResourceController
 * @package Nedra\RestBundle\Controller
 */
class ResourceController extends FOSRestController
{
    /** @var MetadataInterface */
    private $metadata;

    /** @var RegistryInterface */
    private $registry;

    /** @var EntityManager */
    private $entityManager;

    /** @var RequestConfigurationInterface */
    private $requestConfigurationFactory;

    /** @var RequestFormConfigurationInterface */
    private $requestFormFactory;

    public function __construct(
        RegistryInterface $registry,
        RequestConfigurationInterface $requestConfiguration,
        EntityManager $entityManager,
        RequestFormConfigurationInterface $requestFormConfiguration
    )
    {
        $this->registry = $registry;
        $this->requestConfigurationFactory = $requestConfiguration;
        $this->entityManager = $entityManager;
        $this->requestFormFactory = $requestFormConfiguration;
    }

    public function indexAction(Request $request)
    {
        $result = $this->findOr404($request);

        $view = new View();
        $view->setData($result);
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
    private function findOr404(Request $request, $id = null)
    {
        /** @var MetadataInterface $configuration */
        $configuration = $this->requestConfigurationFactory->create($this->registry, $request);
        $this->metadata = $configuration;

        $model = $configuration->getClass("model");
        if (!$id) {
            $result = $this->entityManager->getRepository($model)->findAll();
        } else {
            $result = $this->entityManager->getRepository($model)->find($id);
        }

        if (!$result) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $configuration->getHumanizedName()));
        }

        return $result;
    }
}
