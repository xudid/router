<?php
namespace Router;

use Exception;

class RouterException extends Exception
{

  function __construct(string $message = '')
  {
      parent::__construct($message);
  }
}
