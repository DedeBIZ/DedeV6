<?php
if (!defined('DEDEINC')) exit('dedebiz');
class NullValueException extends \Exception
{
    public function __construct($message = "Null value exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>