<?php
/**
 * @name Boundary.php
 * @company ZIP Code Download, LLC
 * @copyright 2008
 */

/**
 * Holds four points which represent the northern, southern, eastern and western boundaries.
 * @package ZIP Code Download Wizards
 * @copyright 2008
 * @access public
 */
class Boundary
{
	/**
	 * The northern-most boundary or line if latitude from the point of origin.
	 */
    private $north;

    /**
	 * The southern-most boundary or line if latitude from the point of origin.
	 */
    private $south;

    /**
	 * The eastern-most boundary or line if longitude from the point of origin.
	 */
    private $east;

    /**
	 * The western-most boundary or line if longitude from the point of origin.
	 */
    private $west;
	
	
	/**
	 * Constructs a boundary value object.
	 * @param $north The northern-most boundary or line if latitude from the point of origin.
	 * @param $south The southern-most boundary or line if latitude from the point of origin.
	 * @param $east The eastern-most boundary or line if longitude from the point of origin.
	 * @param $west The western-most boundary or line if longitude from the point of origin.
	 */
    public function Boundary($north, $south, $east, $west)
    {
        $this->north = $north;
        $this->south = $south;
        $this->east = $east;
        $this->west = $west;
    }

    /**
	 * Gets the northern-most boundary line of latitude from the point of origin.
	 * @return The northern-most boundary line of latitude from the point of origin.
	 */
    public function North()
    {
        return $this->north;
    }

    /**
	 * Gets the southern-most boundary line of latitude from the point of origin.
	 * @return The southern-most boundary line of latitude from the point of origin.
	 */
    public function South()
    {
        return $this->south;
    }

    /**
	 * Gets the eastern-most boundary line of longitude from the point of origin.
	 * @return The eastern-most boundary line of longitude from the point of origin.
	 */
    public function East()
    {
        return $this->east;
    }

    /**
	 * Gets the western-most boundary line of longitude from the point of origin.
	 * @return The western-most boundary line of longitude from the point of origin.
	 */
    public function West()
    {
        return $this->west;
    }
}
?>