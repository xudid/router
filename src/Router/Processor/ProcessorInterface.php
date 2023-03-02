<?php

namespace Router\Processor;

use Psr\Http\Message\ResponseInterface;

interface ProcessorInterface
{
    public function setParams(array $params): ProcessorInterface;

    public function execute(): ResponseInterface;

    public function setCallable($callable): ProcessorInterface;
}