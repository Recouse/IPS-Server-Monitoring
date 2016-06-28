<?php
namespace Monitoring\Exception;

class QueryServerException extends \Exception
{

    private $szMessage;

    function __construct( $szMessage )
    {
        $this->szMessage = $szMessage;
    }


    function toString( )
    {
        return $this->szMessage;
    }
}