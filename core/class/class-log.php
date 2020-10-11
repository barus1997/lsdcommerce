<?php 
namespace LSDCommerce\Logger;
/**
 * Class to Saving Log with Severity
 * 
 * You can Accesing This Class Static
 * Usage ::
 * Include this file
 * use LSDCommerce\Logger\LSDC_Logger
 * LSDC_Logger::log( 'Message Logger' );
 */
class LSDC_Logger
{
    const INFO      = 'INFO';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';

    private static $instance;

    private function __construct()
    {
        if ( !is_dir( LSDC_CONTENT) ) {
			mkdir( LSDC_CONTENT );       
		}
    }

    private static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new LSDC_Logger();
        }
        return self::$instance;
    }

    // Saving to wp-content/upload/lsdcommerce/lsdcommerce.log
    private function writeToFile( $message )
    {
        file_put_contents( LSDC_CONTENT . '/lsdcommerce.log', "$message\n", FILE_APPEND);
    }

    public static function log( $message, $level = LSDC_Logger::INFO )
    {
        $date = lsdc_date_now();
        $severity = "[$level]";
        $message = "$date $severity :: $message";
        self::getInstance()->writeToFile($message);
    }
}
?>