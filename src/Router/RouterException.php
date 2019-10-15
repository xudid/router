<?php
namespace Router;


use Exception;

/**
 *
 */
class RouterException extends Exception
{

  function __construct($message)
  {
      parent::__construct($message);

  }
}
