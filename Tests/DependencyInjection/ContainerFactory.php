<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use Nedra\RestBundle\DependencyInjection\NedraRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Class ContainerFactory
 * @package Nedra\RestBundle\Test\DependencyInjection
 */
class ContainerFactory
{
    /**
     * @param null $file
     * @param array $extensions
     * @return ContainerBuilder
     */
    public static function createContainer($file = null, $extensions = [])
    {
        $container = new ContainerBuilder(new ParameterBag(array('kernel.debug' => false)));
        $container->registerExtension(new NedraRestExtension());
        foreach ($extensions as $extension) {
            $container->registerExtension($extension);
        }
        if ($file) {
            $locator = new FileLocator(__DIR__ . '/Fixtures');
            $loader = new YamlFileLoader($container, $locator);
            $loader->load($file);
        }
        $bundle = new NedraRestBundle();
        $bundle->build($container);

        $container->compile();
        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    public static function createDummyContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array('kernel.debug' => false)));
        return $container;
    }
}