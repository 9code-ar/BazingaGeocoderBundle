<?php

/**
 * This file is part of the GeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace _9Code\GeocoderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AddProvidersPass implements CompilerPassInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * Get all providers based on their tag (`geocoder_bundle.provider`) and
     * register them.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('geocoder_bundle.geocoder')) {
            return;
        }

        $providers = array();
        foreach ($container->findTaggedServiceIds('geocoder_bundle.provider') as $providerId => $attributes) {
            $providers[] = new Reference($providerId);
        }

        $geocoderDefinition = $container->getDefinition('geocoder_bundle.geocoder');
        $geocoderDefinition->addMethodCall('registerProviders', array($providers));

        if ($container->hasParameter('geocoder_bundle.default_provider')) {
            $geocoderDefinition->addMethodCall(
                'using',
                array($container->getParameter('geocoder_bundle.default_provider'))
            );
        }
    }
}
