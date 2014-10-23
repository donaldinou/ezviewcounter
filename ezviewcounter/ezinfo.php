<?php
/**
 * Information class about ezviewcounter extension
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 * @version 0.0.1
 * @copyright Copyright (C) 2011
 * @license GNU General Public License v2.0
 * @since 2011-12-12
 * @package ezviewcounter
 * @final
 */
final class ezviewcounterInfo {

    /**
     * Return information for ezviewcounter extension
     * @access public
     * @return array
     * @static
     */
    public static function info() {
        $name             = 'eZViewCounter';
        $version          = '0.0.1';
        $copyright        = 'Copyright (C)';
        $license          = 'GNU General Public License v2.0';
        $contributors     = array(
                                array('adrien loyant <aloyant@te-laval.fr>'),
        );
        $thirdparty_software = null;

        // return information
        return array(   'Name'              => $name,
                        'Version'           => $version,
                        'Copyright'         => $copyright,
                        'License'           => $license,
                        'contributors'      => $contributors,
                        '3rdparty_software' => $thirdparty_software
        );
    }

}
