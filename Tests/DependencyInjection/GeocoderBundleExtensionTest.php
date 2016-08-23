<?php

namespace _9Code\GeocoderBundle\Tests\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use _9Code\GeocoderBundle\DependencyInjection\GeocoderBundleExtension;
use _9Code\GeocoderBundle\DependencyInjection\Compiler\AddDumperPass;
use _9Code\GeocoderBundle\DependencyInjection\Compiler\AddProvidersPass;
use _9Code\GeocoderBundle\DependencyInjection\Compiler\LoggablePass;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class GeocoderBundleExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $configs = Yaml::parse(file_get_contents(__DIR__.'/Fixtures/config.yml'));
        unset($configs['geocoder_bundle']['default_provider']);

        $container = new ContainerBuilder();
        $extension = new GeocoderBundleExtension();

        $container->setParameter('fixtures_dir', __DIR__.'/Fixtures');

        $container->set('doctrine.apc.cache', new ArrayCache());

        $container->addCompilerPass(new AddDumperPass());
        $container->addCompilerPass(new AddProvidersPass());
        $container->addCompilerPass(new LoggablePass());

        $extension->load($configs, $container);
        $container->compile();

        $this->assertInstanceOf(
            '_9Code\GeocoderBundle\EventListener\FakeRequestListener',
            $container->get('geocoder_bundle.event_listener.fake_request')
        );
        $this->assertNotNull(
            $container->get('geocoder_bundle.event_listener.fake_request')->getFakeIp()
        );

        $dumperManager = $container->get('geocoder_bundle.dumper_manager');

        foreach (array('geojson', 'gpx', 'kmp', 'wkb', 'wkt') as $name) {
            $this->assertTrue($dumperManager->has($name));
        }

        $this->assertFalse($container->hasParameter('geocoder_bundle.default_provider'));

        $geocoder = $container->get('geocoder_bundle.geocoder');
        $providers = $geocoder->getProviders();
        foreach (array(
            'bing_maps' => 'Geocoder\\Provider\\BingMaps',
            'cache' => '_9Code\\GeocoderBundle\\Provider\\Cache',
            'ip_info_db' => 'Geocoder\\Provider\\IpInfoDb',
            'google_maps' => 'Geocoder\\Provider\\GoogleMaps',
            'google_maps_business' => 'Geocoder\\Provider\\GoogleMapsBusiness',
            'openstreetmap' => 'Geocoder\\Provider\\OpenStreetMap',
            'host_ip' => 'Geocoder\\Provider\\HostIp',
            'free_geo_ip' => 'Geocoder\\Provider\\FreeGeoIp',
            'map_quest' => 'Geocoder\\Provider\\MapQuest',
            'yandex' => 'Geocoder\\Provider\\Yandex',
            'geo_ips' => 'Geocoder\\Provider\\GeoIps',
            'geo_plugin' => 'Geocoder\\Provider\\GeoPlugin',
            'maxmind' => 'Geocoder\\Provider\\Maxmind',
            'chain' => 'Geocoder\\Provider\\Chain',
            'maxmind_binary' => 'Geocoder\\Provider\\MaxmindBinary',
            'opencage' => 'Geocoder\\Provider\\OpenCage',
        ) as $name => $class) {
            $this->assertInstanceOf($class, $providers[$name], sprintf('-> Assert that %s is instance of %s', $name, $class));
        }
    }

    public function testDefaultProvider()
    {
        $configs = Yaml::parse(file_get_contents(__DIR__.'/Fixtures/config.yml'));
        $container = new ContainerBuilder();
        $extension = new GeocoderBundleExtension();

        $container->setParameter('fixtures_dir', __DIR__.'/Fixtures');

        $container->set('doctrine.apc.cache', new ArrayCache());

        $container->addCompilerPass(new AddProvidersPass());
        $extension->load($configs, $container);

        $container->compile();

        $this->assertEquals('bing_maps', $container->getParameter('geocoder_bundle.default_provider'));
    }

    public function testLoadingFakeIpOldWay()
    {
        $configs = Yaml::parse(file_get_contents(__DIR__.'/Fixtures/old_fake_ip.yml'));
        $container = new ContainerBuilder();
        $extension = new GeocoderBundleExtension();

        $container->setParameter('fixtures_dir', __DIR__.'/Fixtures');

        $container->set('doctrine.apc.cache', new ArrayCache());

        $container->addCompilerPass(new AddDumperPass());
        $container->addCompilerPass(new AddProvidersPass());

        $extension->load($configs, $container);
        $container->compile();

        $this->assertInstanceOf(
            '_9Code\GeocoderBundle\EventListener\FakeRequestListener',
            $container->get('geocoder_bundle.event_listener.fake_request')
        );

        $this->assertNotNull(
            $container->get('geocoder_bundle.event_listener.fake_request')->getFakeIp()
        );
    }
}
