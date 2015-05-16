<?php
/*
Plugin Name: DAWA
Plugin URI: http://minpasning.dk/
Description:Danske Adresser WordPress Plugin (efter http://digitaliser.dk).
Version: 1.0.1
Author: alstrup|next
Author URI: http://alstrupnext.com
License: GPLv2
*/

load_plugin_textdomain('dawa', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

if (!class_exists('DAWA_Plugin')) {

	class DAWA_Plugin {

		function __construct() {
			add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_and_register') );
		}


		function enqueue_and_registe() {
			$dawa_autocomplete_js = plugins_url( 'js/dawa-autocomplete.js', __FILE__ );
			wp_register_script( 'dawa_autocomplete_js', $dawa_autocomplete_js, Array(), '', false );	
		}

		function dawa_geocode ( $address ) {

            /* get geocoding with Danish Addresses Web Application */
            $address = urlencode($address);
            $cached_address = 'leaflet_' . $address;

            /* retrieve cached geocoded location */
            /*if (get_option($cached_address)) {
                return get_option($cached_address);
            }*/

            /* try geocoding */
            $dawa_geocode = 'https://dawa.aws.dk/adresser?format=json&q=';
            $geocode_url = $dawa_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json[0]->{'status'}==1) {
                
                $location = array(  'lat'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[1],
                                    'lng'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[0]
                                );

                /* add location */
                add_option($cached_address, $location);

                /* add option key to locations for clean up purposes */
                $locations = get_option('leaflet_geocoded_locations', array());
                array_push($locations, $cached_address);
                update_option('leaflet_geocoded_locations', $locations);
                
                return (Object) $location;
            }

            /* else */
            return (Object) array('lat' => 0, 'lng' => 0);
        }

	}

	$dawa_plugin = new DAWA_Plugin;

}