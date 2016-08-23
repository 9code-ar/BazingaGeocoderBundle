<?php

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
namespace _9Code\GeocoderBundle\Mapping\Driver;

interface DriverInterface
{
    public function isGeocodeable($object);

    public function loadMetadataFromObject($object);
}
