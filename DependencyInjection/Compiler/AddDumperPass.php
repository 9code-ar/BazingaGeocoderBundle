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

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class AddDumperPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('geocoder_bundle.dumper_manager')) {
            return;
        }

        $manager = $container->findDefinition('geocoder_bundle.dumper_manager');

        $dumpers = array();
        foreach ($container->findTaggedServiceIds('geocoder_bundle.dumper') as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new \RuntimeException(sprintf('No alias for service "%s" provided. Please set a alias!', $id));
            }

            $dumpers[$attributes[0]['alias']] = $container->getDefinition($id);
        }

        $manager->setArguments(array($dumpers));
    }
}
