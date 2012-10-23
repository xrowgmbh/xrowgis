<?php

class GeoCoder
{
    public $street;
    public $zip;
    public $city;
    public $state;
    public $country;
    public $query_string;
    public $longitude;
    public $latitude;
    public $location;
    public $reverse;
    const ACCURACY_STREET = 'address';
    const ACCURACY_CITY = 'city';
    const ACCURACY_ZIP = 'zip';

    /**
     * Useage normal:
     * <code>
     * $coder = GeoCoder::getActiveGeoCoder();
     * $coder->setAddress( 'Am Lindener Berge 22', '30449', 'Hannover', 'NI', 'Germany' );
     * if ( $coder->request() )
     * {
     * //success
     * echo $coder->lat;
     * echo $coder->long;
     * }
     * else
     * {
     * //error
     * }
     * </code>
     * 
     * @return GeoCoder
     * 
     * Useage reverse:
     * <code>
     * $coder = GeoCoder::getActiveGeoCoder();
     * $coder->setLonLat( 9.8597, 52.4295 );
     * if ( $coder->request() )
     * {
     * //success
     * echo $coder->lat;
     * echo $coder->long;
     * }
     * else
     * {
     * //error
     * }
     * </code>
     * 
     * @return GeoCoder
     */
    function GeoCoder()
    {
    
    }

    /**
     * Fills the Geocoder with initial data
     *
     * @param string $street
     * @param string $zip
     * @param string $city
     * @param string $state
     * @param string $country
     * @param string $location Optional. Defines a potential location you are looking for e.g. "Central Railway Station", "Airport"
     * @param string $bounds optional defines the bounding box which should be prefered
     * @param string $query_string optional
     */
    public function setAddress( $street, $zip, $city, $state, $country, $location = false, $query_string = null )
    {
        if ( $query_string != null and ! empty( $query_string ) )
        {
            $this->query_string = $query_string;
        }
        else
        {
            if ( strlen( $street ) > 1 )
                $this->street = trim( $street );
            if ( strlen( $zip ) > 1 )
                $this->zip = trim( $zip );
            if ( strlen( $city ) > 1 )
                $this->city = trim( $city );
            if ( strlen( $state ) > 1 )
                $this->state = trim( $state );
            if ( strlen( $country ) > 1 )
                $this->country = trim( $country );
            if ( $location !== false )
                $this->location = trim( $location );
        }
    }

    /**
     * Fills the Geocoder with initial data
     *
     * @param float $lon
     * @param float $lat
     */
    public function setLonLat( $longitude, $latitude )
    {
        $this->reverse = true;
        if ( strlen( $longitude ) > 1 )
            $this->longitude = trim( $longitude );
        if ( strlen( $latitude ) > 1 )
            $this->latitude = trim( $latitude );
    }

    /**
     * This function processes the request if a faulure is noticed this function will return false.
     * 
     * This fucntion sets $long and $lat and and updates the address if needed
     * 
     * @return boolean either true or false
     */
    function request()
    {
        return true;
    }

    /**
     * get the google or yahoo coder class
     *
     * @return GeoCoder
     */
    static function getActiveGeoCoder()
    {
        $type = eZINI::instance( 'xrowgis.ini' )->variable( 'GISSettings', 'Interface' ) . "GeoCoder";
        return new $type();
    
    }
}
?>