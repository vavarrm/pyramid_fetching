<?php
namespace App\Exceptions;
use Exception;
class MyException  extends Exception
{
    public const  GPFetchingClassDoesNotExist  = "GP Fetching Class Does Not Exist";
    public const  ParameterIsRequired  = "Parameter %s Provider Is Required";
}

