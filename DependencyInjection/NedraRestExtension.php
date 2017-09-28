<?php

namespace Nedra\RestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NedraRestExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['active']) {
            return;
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load("services.yml");

        $container->setParameter("nedrarest.config", $config);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $append = false;
        $rules = [];
        foreach ($container->getExtensionConfig('fos_rest') as $config) {
            if (isset($config['format_listener'])) {
                foreach ($config['format_listener']['rules'] as $rule) {
                    if (!isset($rule['path'])) {
                        $rules['path'] = '^/';
                        $append = true;
                    }

                    if (!isset($rule['priorities'])) {
                        $rules['priorities'] = ['json'];
                        $append = true;
                    }

                    if (!isset($rule['fallback_format'])) {
                        $rules['fallback_format'] = "json";
                        $append = true;
                    }

                    if (!isset($rule['prefer_extension'])) {
                        $rules['prefer_extension'] = false;
                        $append = true;
                    }
                }
            } else {
                $rules['path'] = '^/';
                $rules['priorities'] = ['json'];
                $rules['fallback_format'] = "json";
                $rules['prefer_extension'] = false;
                $append = true;
            }
        }

        if ($append) {
            $container->prependExtensionConfig('fos_rest', [
                'format_listener' => [
                    'rules' => $rules
                ]
            ]);
        }
    }
}
