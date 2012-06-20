<?php

/**
 * @name Coordinate.php
 * @company ZIP Code Download, LLC
 * @copyright 2008
 */

require_once("UnitConverter.php");

/**
 * Represents a particular location on the planet using the decimal degree coordinate system.
 * @package ZIP Code Download Wizards
 * @copyright 2008
 * @access public
 */
class Coordinate
{
	/**
	 * The minimum possible value for decimal degrees of a half circle.
	 */
	const MIN_DEGREES = -180.0;
	
	/**
	 * The maximum possible value for decimal degrees of a half circle.
	 */
	const MAX_DEGREES = 180.0;
	
	/**
	 * The service used to provide unit conversion functionality.
	 */
    private $unitConverter;

    /**
	 * The line north (+) or south (-) of the equator in decimal degrees.
	 */
    private $latitude;
    
    /**
     * The line east (+) or west (-) of the prime meridian in decimal degrees.
     */
    private $longitude;
	
	/**
	 * Initializes a new instance of the Coordinate class.
	 * @param $latitude The line north (+) or south (-) of the equator in decimal degrees.
	 * @param $longitude The line east (+) or west (-) of the prime meridian in decimal degrees.
	 */
    public function Coordinate($latitude, $longitude)
    {
        if ($latitude > self::MAX_DEGREES || $latitude < self::MIN_DEGREES)
        {
            die("<!-- The latitude value provided ($latitude) is out of bounds (max ".self::MAX_DEGREES."; min ".self::MIN_DEGREES."). -->");
        }
        else if ($longitude > self::MAX_DEGREES || $longitude < self::MIN_DEGREES)
        {
            die("<!-- The longitude value provided ($longitude) is out of bounds (max ".self::MAX_DEGREES."; min ".self::MIN_DEGREES."). -->");
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->unitConverter = new UnitConverter();
    }
    
    /**
     * Gets the line north (+) or south (-) of the equator in decimal degrees.
     * @return The line north (+) or south (-) of the equator in decimal degrees.
	 */
 	public function Latitude()
	{
	    return $this->latitude;
	}
	
	/**
	 * Gets the line east (+) or west (-) of the prime meridian in decimal degrees.
	 * @return The line east (+) or west (-) of the prime meridian in decimal degrees.
	 */
	public function Longitude()
	{
		return $this->longitude;
	}
	
	/**
	 * Gets a value which indicates if the coordinate provided is identical in value to the current object.
	 * @param $coordinate The coordinate to be compared to the this coordinate.
	 * @return bool A value which indicates if the coordinate provided is identical in value to the current object.
	 */
	public function Equals(Coordinate $coordinate)
	{
	    return null != $coordinate
	        && $this->latitude == $coordinate->Latitude()
	        && $this->longitude == $coordinate->Longitude();
	}
	
	/**
	 * Expresses the coordinate in radians instead of decimal degrees.
	 * @return The coordinate expressed in radians.
	 */
    public function ToRadians()
    {
        return new Coordinate(
            $this->unitConverter->DegreesToRadians($this->latitude),
            $this->unitConverter->DegreesToRadians($this->longitude));
    }
}

?>