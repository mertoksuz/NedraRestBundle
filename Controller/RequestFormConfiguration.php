<?php
namespace Nedra\RestBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
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

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(Request $request, $resource)
    {
        $parameters = $request->attributes->all();
        $formClass = $parameters["_nedrarest"]["form"];
        $modelClass = $parameters["_nedrarest"]["model"];

        $formOptions = [
            'data_class' => (new ClassMetadata($formClass))->getName(),
            'model_class' => $modelClass
        ];

        return $this->formFactory->createNamed('', $formClass, $resource, array_merge($formOptions, ['method' => $request->getMethod(), 'csrf_protection' => false]));
    }
}
