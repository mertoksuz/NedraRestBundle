<?php
namespace Nedra\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use Nedra\RestBundle\NedraRestBundle;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use PHPUnit\Framework\TestCase;

class ResourceControllerTest extends TestCase
{
    public function test_no_nedra_rest_bundle_when_active_is_false()
    {
        $container = ContainerFactory::createContainer("disabled.yml");
        $this->assertFalse($container->has(NedraRestBundle::REGISTRY_ID));
    }

    public function test_if_no_fos_rest_bundle_then_no_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, []);
        $this->assertFalse($container->hasExtension("fos_rest"));
    }

    public function test_if_yes_fos_rest_bundle_then_yes_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, [new FOSRestExtension()]);
        $this->assertTrue($container->hasExtension("fos_rest"));
    }

    public function test_if_no_cmf_routing_bundle_then_no_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, []);
        $this->assertFalse($container->hasExtension("cmf"));
    }

    public function test_if_yes_cmf_routing_bundle_then_yes_nedra_rest()
    {
        $container = ContainerFactory::createContainer(null, [new CmfRoutingExtension()]);
        $this->assertTrue($container->hasExtension("cmf_routing"));
    }

}