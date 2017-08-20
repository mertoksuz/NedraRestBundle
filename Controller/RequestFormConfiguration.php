<?php
namespace Nedra\RestBundle\Controller;

use Doctrine\ORM\EntityManager;
use Nedra\RestBundle\Component\MetadataInterface;
use Nedra\RestBundle\Form\Type\DefaultResourceType;
use Nedra\RestBundle\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestFormConfiguration
 * @package Nedra\RestBundle\Controller
 */
class RequestFormConfiguration implements RequestFormConfigurationInterface
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RegistryInterface */
    private $registry;

    /** @var EntityManager */
    private $manager;

    public function __construct(FormFactoryInterface $formFactory, RegistryInterface $registry, EntityManager $manager)
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->manager = $manager;
    }

    public function create(MetadataInterface $metadata, Request $request, $resource)
    {
        $parameters = $request->attributes->all();
        $formClass = $parameters["_nedrarest"]["form"];

        $formOptions = [
            'data_class' => $metadata->getClass("model")
        ];

        return $this->formFactory->createNamed('', $formClass, $resource, array_merge($formOptions, ['method' => $request->getMethod(), 'csrf_protection' => false]));
    }
}