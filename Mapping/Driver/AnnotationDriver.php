<?php

namespace _9Code\GeocoderBundle\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use _9Code\GeocoderBundle\Mapping\Exception;
use _9Code\GeocoderBundle\Mapping\Annotations;
use _9Code\GeocoderBundle\Mapping\ClassMetadata;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class AnnotationDriver implements DriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function isGeocodeable($object)
    {
        $reflection = new \ReflectionObject($object);

        return !!$this->reader->getClassAnnotation($reflection, '_9Code\\GeocoderBundle\\Mapping\\Annotations\\Geocodeable');
    }

    public function loadMetadataFromObject($object)
    {
        $reflection = new \ReflectionObject($object);
        if (!$annotation = $this->reader->getClassAnnotation($reflection, '_9Code\\GeocoderBundle\\Mapping\\Annotations\\Geocodeable')) {
            throw new Exception\MappingException(sprintf(
                'The class %s is not geocodeable', get_class($object)
            ));
        }

        $metadata = new ClassMetadata();

        foreach ($reflection->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof Annotations\Latitude) {
                    $property->setAccessible(true);
                    $metadata->latitudeProperty = $property;
                } elseif ($annotation instanceof Annotations\Longitude) {
                    $property->setAccessible(true);
                    $metadata->longitudeProperty = $property;
                } elseif ($annotation instanceof Annotations\Address) {
                    $property->setAccessible(true);
                    $metadata->addressProperty = $property;
                }
            }
        }

        return $metadata;
    }
}
