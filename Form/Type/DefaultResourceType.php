<?php
namespace Nedra\RestBundle\Form\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Nedra\RestBundle\Component\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class DefaultResourceType extends AbstractType
{
    /** @var EntityManager */
    private $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $classMetadata = $this->manager->getClassMetadata($options['data_class']);

        if (1 < count($classMetadata->getIdentifier())) {
            throw new \RuntimeException('The default form factory does not support entity classes with multiple primary keys.');
        }

        $fields = (array) $classMetadata->getFieldNames();

        if (!$classMetadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $classMetadata->getIdentifier());
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
