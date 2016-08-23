<?php

/**
 * This file is part of the GeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace _9Code\GeocoderBundle\Tests\Command;

use _9Code\GeocoderBundle\Command\GeocodeCommand;
use _9Code\Geocoder\Model\Address;
use _9Code\Geocoder\Model\AddressCollection;
use _9Code\Geocoder\Model\Bounds;
use _9Code\Geocoder\Model\Coordinates;
use _9Code\Geocoder\Model\Country;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class GeocodeCommandTest extends \PHPUnit_Framework_TestCase
{
    private static $address = '10 rue Gambetta, Paris, France';

    public function testExecute()
    {
        $coordinates = new Coordinates(1, 2);
        $bounds = new Bounds(1, 2, 3, 4);
        $country = new Country('France', 'FR');
        $address = new Address($coordinates, $bounds, '10', 'rue Gambetta', '75020', 'Paris', null, null, $country);

        $geocoder = $this->getMock('Geocoder\\ProviderAggregator');
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with(self::$address)
            ->will($this->returnValue(new AddressCollection(array($address))));

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\Container');
        $container->expects($this->once())
            ->method('get')
            ->with('geocoder_bundle.geocoder')
            ->will($this->returnValue($geocoder));

        $kernel = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Kernel')
            ->disableOriginalConstructor()
            ->getMock();

        $kernel->expects($this->once())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $app = new Application($kernel);
        $app->add(new GeocodeCommand());

        $command = $app->find('geocoder:geocode');

        $tester = new CommandTester($command);
        $tester->execute(array(
            'command' => 'geocoder:geocode',
            'address' => self::$address,
        ));
    }
}
