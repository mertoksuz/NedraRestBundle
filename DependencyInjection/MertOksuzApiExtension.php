<?php

namespace MertOksuz\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class MertOksuzApiExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load("services.yml");

        $container->setParameter("mertoksuz_api.config", $config);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig("framework");
        $param = $container->getParameter("kernel.project_dir");
        $default_resource = $configs[0]["router"]["resource"];
        $resource = str_replace("%kernel.project_dir%", $param, $default_resource);

        $routing = [
            'test_mert' => ['resource' => '.', 'type' => 'mert_oksuz.api']
        ];

        $yaml = Yaml::dump($routing);

        file_put_contents($resource, $yaml, FILE_APPEND);
    }
}
