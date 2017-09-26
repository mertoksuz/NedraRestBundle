<?php
namespace Nedra\RestBundle\Controller;

use Doctrine\ORM\EntityManager;
use Nedra\RestBundle\Metadata\MetadataInterface;
use Nedra\RestBundle\Form\Type\DefaultResourceType;
use Nedra\RestBundle\Component\RegistryInterface;
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


    public function __construct(FormFactoryInterface $formFactory, RegistryInterface $registry)
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
    }

    public function create(\Doctrine\ORM\Mapping\ClassMetadata $metadata, Request $request, $resource)
    {
        $parameters = $request->attributes->all();
        $formClass = $parameters["_nedrarest"]["form"];

        $formOptions = [
            'data_class' => $metadata->fullyQualifiedClassName($formClass)
        ];

        return $this->formFactory->createNamed('', $formClass, $resource, array_merge($formOptions, ['method' => $request->getMethod(), 'csrf_protection' => false]));
    }
}
