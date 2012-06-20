<?php

/**
 * @name UnitConverter.php
 * @company ZIP Code Download, LLC
 * @copyright 2008
 */

/**
 * A service used to facilitate unit conversion.
 * @package ZIP Code Download Wizards
 * @copyright 2008
 * @access public
 */
class UnitConverter
{
	/**
	 * The number of kilometers in one mile.
	 */
    const KILOMETERS_PER_MILE = 1.609344;
    
    /**
     * The number of degrees contained in a half circle.
     */
 	const SEMICIRCLE_DEGREES = 180.0;
 	
 	/**
 	 * Converts radians to its corresponding value in degrees.
 	 * @param $value The value to convert.
 	 * @return double The number of degrees in the value provided.
 	 */
 	public function RadiansToDegrees($value)
 	{
 		return $value * self::SEMICIRCLE_DEGREES / M_PI;
 	}
 	
 	/**
 	 * Converts degrees to its corresponding value in radians.
 	 * @param $value The value to convert.
 	 * @return The number of radians in the value provided.
 	 */
 	public function DegreesToRadians($value)
 	{
 		return $value * M_PI / self::SEMICIRCLE_DEGREES;
 	}
 	
 	/** 
 	 * Converts miles to its corresponding value in kilometers.
 	 * @param $value The number of miles to be converted.
 	 * @return The number of kilometers in the value provided.
 	 */
    public function MilesToKilometers($value)
    {
        return $value * self::KILOMETERS_PER_MILE;
    }
    
    /**
     * Converts kilometers to its corresponding value in miles.
     * @param $value The number of kilometers to be converted.
     * @return The number of miles in the value provided.
     */
    public function KilometersToMiles($value)
    {
    	return $value / self::KILOMETERS_PER_MILE;
    }
}
?>