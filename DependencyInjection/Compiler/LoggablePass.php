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
 * @author Michal Dabrowski <dabrowski@brillante.pl>
 */
class LoggablePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('geocoder_bundle.geocoder')) {
            return;
        }

        $definition = $container->getDefinition('geocoder_bundle.geocoder');
        $definition->setClass($container->getParameter('geocoder_bundle.geocoder.loggable_class'));
        $definition->addMethodCall('setLogger', array(new Reference('geocoder_bundle.logger')));
    }
}
