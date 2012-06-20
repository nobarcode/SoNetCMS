<?php

/**
 * @name DistanceWizard.php
 * @company ZIP Code Download, LLC
 * @copyright 2008
 */
 
require_once("Coordinate.php");
require_once("UnitConverter.php");
require_once("Measurement.php");
global $Measurement;

/**
 * Computes the distance between any two coordinates on a sphere.
 * @package ZIP Code Download Wizards
 * @copyright 2008
 * @access public
 */
class DistanceWizard
{
	/**
	 * The distance, in miles, from the center of the earth to sea level.
	 */
	const EARTH_RADIUS_MILES = 3963.189;
	
	/**
	 * The service used to provide unit conversion functionality.
	 */
	private $unitConverter;
	
	/**
	 * Makes an instance of this class.
	 */
	public function DistanceWizard()
	{
		$this->unitConverter = new UnitConverter();
	}
	
	/**
	 * Computes the distance between the two coordinates provided.
	 * @param $origin The point or origin coordinate from which to compute the distance.
	 * @param $relative The coordinate outlying or relative to the point of origin.
	 * @param $measure The unit of measure to be used when returning the distance (e.g. Measurement::MILES or Measurement::KILOMETERS)
	 * @return The distance between the two coordinates expressed in the desired unit of measure.
	 */
	public function CalculateDistance(Coordinate $origin, Coordinate $relative, $measure)
	{
		if (!$origin|| !$relative || $origin->Equals($relative))
        {
            // Invalid coordinate(s), no distance.
            return 0;
        }

        // Convert each coordinate from decimal degrees to radians in order to perform the geometric calculations.
        $origin = $origin->ToRadians();
        $relative = $relative->ToRadians();

        // Perform the actual distance calculation.
        $distance = sin($origin->Latitude()) * sin($relative->Latitude());
        $distance += cos($origin->Latitude())
            * cos($relative->Latitude())
            * cos($relative->Longitude() - $origin->Longitude());
        $distance = -1 * atan($distance / sqrt(1 - $distance * $distance)) + M_PI / 2;
        $distance *= self::EARTH_RADIUS_MILES;

        if ($measure == Measurement::KILOMETERS)
        {
            // Convert the distance calculated to utilize the desired unit of measure.
            return $this->unitConverter->MilesToKilometers($distance);
        }

        return $distance;
	}
	
}
?>