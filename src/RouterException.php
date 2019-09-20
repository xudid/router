<?php
namespace Brick\Router;

/**
 *
 */
class RouterException extends \Exception
{

  function __construct($message)
  {
    echo "RouterException :".$message;
  }
}
