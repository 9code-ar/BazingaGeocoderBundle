<?php

namespace _9Code\GeocoderBundle\Tests\DependencyInjection\Compiler;

use _9Code\GeocoderBundle\DependencyInjection\Compiler\AddDumperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class AddDumperPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinition('geocoder_bundle.dumper_manager', new Definition('_9Code\GeocoderBundle\DumperManager'));

        $dumper = new Definition('Geocoder\Dumper\GeoJson');
        $dumper->addTag('geocoder_bundle.dumper', array('alias' => 'geojson'));

        $builder->setDefinition('geocoder_bundle.dumper.geojson', $dumper);

        $compiler = new AddDumperPass();
        $compiler->process($builder);

        $manager = $builder->get('geocoder_bundle.dumper_manager');

        $this->assertTrue($manager->has('geojson'));
    }

    public function testProcessWithoutManager()
    {
        $builder = new ContainerBuilder();
        $compiler = new AddDumperPass();

        $this->assertNull($compiler->process($builder));
    }
}
