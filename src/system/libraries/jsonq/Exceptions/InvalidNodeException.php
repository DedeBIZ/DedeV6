<?php
if (!defined('DEDEINC')) exit('dedebiz');
class InvalidNodeException extends \Exception
{
    public function __construct($message = "Invalid JSON node exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>