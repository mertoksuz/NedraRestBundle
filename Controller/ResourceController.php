<?php

namespace MertOksuz\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use MertOksuz\ApiBundle\Component\MetadataInterface;
use MertOksuz\ApiBundle\Metadata\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceController extends FOSRestController
{
    /** @var RegistryInterface */
    private $registry;

    /** @var EntityManager */
    private $entityManager;

    /** @var RequestConfigurationInterface */
    private $requestConfigurationFactory;

    public function __construct(
        RegistryInterface $registry,
        RequestConfigurationInterface $requestConfiguration,
        EntityManager $entityManager)
    {
        $this->registry = $registry;
        $this->requestConfigurationFactory = $requestConfiguration;
        $this->entityManager = $entityManager;
    }

    public function indexAction(Request $request)
    {
        /** @var MetadataInterface $configuration */
        $configuration = $this->requestConfigurationFactory->create($this->registry, $request);

        $model = $configuration->getClass("model");
        $result = $this->entityManager->getRepository($model)->findAll();

        if (!$result) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $configuration->getHumanizedName()));
        }

        $view = new View();
        $view->setData($result);
        return $this->handleView($view);
    }

    public function showAction($id, Request $request)
    {
        /** @var MetadataInterface $configuration */
        $configuration = $this->requestConfigurationFactory->create($this->registry, $request);

        $model = $configuration->getClass("model");
        $result = $this->entityManager->getRepository($model)->find($id);

        if (!$result) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $configuration->getHumanizedName()));
        }

        $view = new View();
        $view->setData($result);
        return $this->handleView($view);
    }
}
