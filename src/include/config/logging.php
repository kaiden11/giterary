<?php

global $application_name;
global $instance_name;

class FileLogger {

    private $file;
    function __construct( $_file ) {

        $this->file = $_file;
    }

    public function log( $context, $message ) {

        if( is_writable( $this->file ) || is_writable( dirname( $this->file ) ) ) {

            $t = strftime('%Y-%m-%dT%H:%M:%S%z');

            file_put_contents( 
                $this->file,
                "$t $context $message\n",
                FILE_APPEND
            );
        }
        return true;
    }
}



$registered_loggers = array();


$registered_loggers[] = array(
    new FileLogger( 
        "/var/lib/$application_name/logs/$instance_name.log" 
    ),
    "log"
);

?>
