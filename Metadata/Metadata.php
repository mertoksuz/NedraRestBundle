<?php
namespace Nedra\RestBundle\Metadata;

use Doctrine\Common\Inflector\Inflector;

final class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $applicationName;

    /**
     * @param string $name
     * @param string $applicationName
     * @param array  $parameters
     */
    private function __construct($name, $applicationName, array $parameters)
    {
        $this->name = $name;
        $this->applicationName = $applicationName;
    }

    /**
     * @param string $alias
     * @param array  $parameters
     *
     * @return self
     */
    public static function fromAliasAndConfiguration($alias, array $parameters)
    {
        list($applicationName, $name) = self::parseAlias($alias);

        return new self($name, $applicationName, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralName()
    {
        return Inflector::pluralize($this->name);
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    private static function parseAlias($alias)
    {
        if (false === strpos($alias, '.')) {
            throw new \InvalidArgumentException('Invalid alias supplied, it should conform to the following format "<applicationName>.<name>".');
        }

        return explode('.', $alias);
    }
}
