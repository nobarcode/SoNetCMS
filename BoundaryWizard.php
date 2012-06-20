<?php

/**
 * @name BoundaryWizard.php
 * @company ZIP Code Download, LLC
 * @copyright 2008
 */

require_once("Boundary.php");
require_once("Coordinate.php");
require_once("Measurement.php");
global $Measure;

/**
 *  Helps determine which coordinates fall outside a particular boundary by computing a loose range around a center coordinate.
 * @package ZIP Code Download Wizards
 * @copyright 2008
 * @access public
 */
class BoundaryWizard
{
	/**
	 * The distance, in miles, from the center of the earth to sea level.
	 */
	const EARTH_RADIUS_MILES = 3963.189;
	
	/**
	 * The distance, in miles, a single degree of longitude is at the earth's equator.
	 */
	const EQUATOR_MILES_PER_LONGITUDE_DEGREE = 69.172;
	
	/**
	 * The service used to provide unit conversion functionality.
	 */
	private $unitConverter;
	
	/**
	 * Insantiates a new BoundaryWizard object.
	 */
	public function BoundaryWizard()
	{
		$this->unitConverter = new UnitConverter();
	}
	
	/**
	 * Finds the boundary around a point of origin.
	 * @param $origin The specific coordinate or location around which the boundary should be computed.
	 * @param $distance The distance, in the specified unit of measure, around which the boundary will be computed.
	 * @param $measure The unit of measure for distance provided (Measurement::MILES or Measurement::KILOMETERS)
	 * @return The boundary around the point of origin (as a Boundary object).
	 */
	 public function CalculateBoundary(Coordinate $origin, $distance, $measure)
	 {
	 	if (!$origin)
        {
            // rather than throw an exception, just return a blank/empty radius.
            return new Boundary(0, 0, 0, 0);
        }
        else if ($distance < 0)
        {
            // Negative distances are impossible, invert the value.
            $distance *= -1.0;
        }

        if ($measure == Measurement::KILOMETERS)
        {
            // Convert the distance to miles internally.  Note the values computed will be
            // the same regardless of the unit of measure used.  This is similar to
            // measuring the temperature outside - the actual temperature doesn't change
            // just because it's measured in Celsius or Fahrenheit.
            $distance = $this->unitConverter->KilometersToMiles($distance);
        }

        // Convert the origin to radians in order to perform the geometric computations.
        $originAsRadians = $origin->ToRadians();

        // Compute the southern and northern boundaries
        $north = $origin->Latitude() + $distance / self::EQUATOR_MILES_PER_LONGITUDE_DEGREE;
        $south = $origin->Latitude() - $distance / self::EQUATOR_MILES_PER_LONGITUDE_DEGREE;

        // Compute the eastern and western boundaries
        $east = sin((-1 * $distance / self::EARTH_RADIUS_MILES) + M_PI / 2);
        $east -= sin($originAsRadians->Latitude()) * sin($originAsRadians->Latitude());
        $east /= cos($originAsRadians->Latitude()) * cos($originAsRadians->Latitude());
        $east = acos($east) + $originAsRadians->Longitude();
        $east = $this->unitConverter->RadiansToDegrees($east);

        $west = $origin->Longitude() - ($east - $origin->Longitude());

        return new Boundary($north, $south, $east, $west);
	 }
}
?>