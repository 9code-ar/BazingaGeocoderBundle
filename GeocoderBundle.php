<?php

/**
 * This file is part of the GeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace _9Code\GeocoderBundle;

use _9Code\GeocoderBundle\DependencyInjection\Compiler\AddDumperPass;
use _9Code\GeocoderBundle\DependencyInjection\Compiler\AddProvidersPass;
use _9Code\GeocoderBundle\DependencyInjection\Compiler\LoggablePass;
use _9Code\GeocoderBundle\DependencyInjection\GeocoderBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GeocoderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddProvidersPass());
        $container->addCompilerPass(new AddDumperPass());
        $container->addCompilerPass(new LoggablePass());
    }
    
    public function getContainerExtension()
    {
        return new GeocoderBundleExtension();
    }
}
