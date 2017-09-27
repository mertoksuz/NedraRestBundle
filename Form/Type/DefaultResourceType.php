<?php
namespace Nedra\RestBundle\Form\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class DefaultResourceType extends AbstractType
{
    /** @var RegistryInterface */
    private $metadataRegistry;

    /** @var EntityManager */
    private $manager;

    /**
     * @param RegistryInterface $metadataRegistry
     * @param EntityManager $manager
     */
    public function __construct(RegistryInterface $metadataRegistry, EntityManager $manager)
    {
        $this->metadataRegistry = $metadataRegistry;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metadata = $this->metadataRegistry->getByClass($options['data_class']);

        $classMetadata = $this->manager->getClassMetadata($metadata->getClass('model'));

        if (1 < count($classMetadata->identifier)) {
            throw new \RuntimeException('The default form factory does not support entity classes with multiple primary keys.');
        }

        $fields = (array) $classMetadata->fieldNames;

        if (!$classMetadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $classMetadata->identifier);
        }

        foreach ($fields as $fieldName) {
            $options = [];

            if (in_array($fieldName, ['createdAt', 'updatedAt'])) {
                continue;
            }

            if (Type::DATETIME === $classMetadata->getTypeOfField($fieldName)) {
                $options = ['widget' => 'single_text'];
            }

            $builder->add($fieldName, null, $options);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $associationMapping) {
            if (ClassMetadataInfo::ONE_TO_MANY !== $associationMapping['type']) {
                $builder->add($fieldName, null, ['property' => 'id']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nedrarest_resource';
    }
}
