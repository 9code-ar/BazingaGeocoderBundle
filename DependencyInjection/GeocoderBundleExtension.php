<?php

/**
 * This file is part of the GeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace _9Code\GeocoderBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * William Durand <william.durand1@gmail.com>.
 */
class GeocoderBundleExtension extends Extension
{
    protected $container;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['default_provider']) {
            $container->setParameter('geocoder_bundle.default_provider', $config['default_provider']);
        }

        if (!empty($config['fake_ip']) && true === $config['fake_ip']['enabled']) {
            $definition = $container->getDefinition('geocoder_bundle.event_listener.fake_request');
            $definition->replaceArgument(0, $config['fake_ip']['ip']);

            $tag = current($definition->getTag('kernel.event_listener'));
            $tag['priority'] = $config['fake_ip']['priority'];
            $tags = array('kernel.event_listener' => array($tag));
            $definition->setTags($tags);
        } else {
            $container->removeDefinition('geocoder_bundle.event_listener.fake_request');
        }

        $container->setAlias('geocoder_bundle.geocoder.adapter', $config['adapter']);

        if (isset($config['providers']['free_geo_ip'])) {
            $this->addProvider('free_geo_ip');
        }

        if (isset($config['providers']['host_ip'])) {
            $this->addProvider('host_ip');
        }

        if (isset($config['providers']['bing_maps'])) {
            $bingMapsParams = $config['providers']['bing_maps'];

            $this->addProvider('bing_maps', array(
                $bingMapsParams['api_key'],
                $bingMapsParams['locale'],
            ));
        }

        if (isset($config['providers']['ip_info_db'])) {
            $ipInfoDbParams = $config['providers']['ip_info_db'];

            $this->addProvider('ip_info_db', array(
                $ipInfoDbParams['api_key'],
            ));
        }

        if (isset($config['providers']['google_maps'])) {
            $googleMapsParams = $config['providers']['google_maps'];

            $this->addProvider('google_maps', array(
                $googleMapsParams['locale'],
                $googleMapsParams['region'],
                $googleMapsParams['use_ssl'],
                $googleMapsParams['api_key'],
            ));
        }

        if (isset($config['providers']['google_maps_business'])) {
            $googleMapsBusinessParams = $config['providers']['google_maps_business'];

            $this->addProvider('google_maps_business', array(
                $googleMapsBusinessParams['client_id'],
                $googleMapsBusinessParams['api_key'],
                $googleMapsBusinessParams['locale'],
                $googleMapsBusinessParams['region'],
                $googleMapsBusinessParams['use_ssl'],
            ));
        }

        if (isset($config['providers']['openstreetmap'])) {
            $openstreetMapsParams = $config['providers']['openstreetmap'];

            $this->addProvider('openstreetmap', array(
                $openstreetMapsParams['locale'],
            ));
        }

        if (isset($config['providers']['geoip'])) {
            $this->addProvider('geoip');
        }

        if (isset($config['providers']['mapquest'])) {
            $mapQuestParams = $config['providers']['mapquest'];

            $this->addProvider('mapquest', array(
                $mapQuestParams['api_key'],
            ));
        }

        if (isset($config['providers']['yandex'])) {
            $yandexParams = $config['providers']['yandex'];

            $this->addProvider('yandex', array(
                $yandexParams['locale'],
                $yandexParams['toponym'],
            ));
        }

        if (isset($config['providers']['geo_ips'])) {
            $geoIpsParams = $config['providers']['geo_ips'];

            $this->addProvider('geo_ips', array(
                $geoIpsParams['api_key'],
            ));
        }

        if (isset($config['providers']['geo_plugin'])) {
            $this->addProvider('geo_plugin');
        }

        if (isset($config['providers']['maxmind'])) {
            $maxmindParams = $config['providers']['maxmind'];

            $this->addProvider('maxmind', array(
                $maxmindParams['api_key'],
            ));
        }

        if (isset($config['providers']['maxmind_binary'])) {
            $maxmindBinaryParams = $config['providers']['maxmind_binary'];

            $provider = new Definition(
                '%geocoder_bundle.geocoder.provider.maxmind_binary.class%',
                array(
                    $maxmindBinaryParams['binary_file'],
                    $maxmindBinaryParams['open_flag'],
                )
            );

            $provider
                ->setPublic(false)
                ->addTag('geocoder_bundle.provider');

            $container->setDefinition('geocoder_bundle.provider.maxmind_binary', $provider);
        }

        if (isset($config['providers']['opencage'])) {
            $openCageParams = $config['providers']['opencage'];

            $this->addProvider('opencage', array(
                $openCageParams['api_key'],
                $openCageParams['use_ssl'],
                $openCageParams['locale'],
            ));
        }

        if (isset($config['providers']['cache'])) {
            $params = $config['providers']['cache'];
            $cache = new Reference($params['adapter']);
            $fallback = new Reference('geocoder_bundle.provider.'.$params['provider']);

            $provider = new Definition(
                '%geocoder_bundle.geocoder.provider.cache.class%',
                array($cache, $fallback, $params['lifetime'])
            );

            if (isset($params['locale'])) {
                $provider->addArgument($params['locale']);
            }

            $provider
                ->setPublic(false)
                ->addTag('geocoder_bundle.provider');

            $container->setDefinition('geocoder_bundle.provider.cache', $provider);
        }

        if (isset($config['providers']['chain'])) {
            $chainProvider = new Definition(
                '%geocoder_bundle.geocoder.provider.chain.class%'
            );

            $this->container->setDefinition('geocoder_bundle.provider.chain', $chainProvider);

            $chainProvider
                ->setPublic(false)
                ->addTag('geocoder_bundle.provider');

            if (isset($config['providers']['chain']['providers'])) {
                foreach ($config['providers']['chain']['providers'] as $name) {
                    if ($this->container->hasDefinition('geocoder_bundle.provider.'.$name)) {
                        $chainProvider->addMethodCall('add', array(
                            $this->container->getDefinition('geocoder_bundle.provider.'.$name),
                        ));
                    } else {
                        $chainProvider->addMethodCall('add', array(new Reference($name)));
                    }
                }
            }
        }
    }

    protected function addProvider($name, array $arguments = array())
    {
        $provider = new Definition(
            '%geocoder_bundle.geocoder.provider.'.$name.'.class%',
            array_merge(
                array(new Reference('geocoder_bundle.geocoder.adapter')),
                $arguments
            )
        );

        $provider
            ->setPublic(false)
            ->addTag('geocoder_bundle.provider');

        $this->container->setDefinition('geocoder_bundle.provider.'.$name, $provider);
    }
}
