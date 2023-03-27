<?php
if (!defined('DEDEINC')) exit('dedebiz');
class ConditionNotAllowedException extends \Exception
{
    public function __construct($message = "Condition not allowed exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>