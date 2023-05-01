<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Router\Route;
use Router\UrlGenerator;

class UrlGeneratorTest extends TestCase
{
    public function testConstruct()
    {
        $routes = [];
        $generator = new UrlGenerator($routes);
        $this->assertInstanceOf(UrlGenerator::class, $generator);
    }

    public function testGenerateRelativeUrl()
    {
        $routes = [
            'GET' => ['recover_password' => new Route('/password/recover/:token', 'recover_password',[])]
        ];
        $generator = new UrlGenerator($routes);
        $result = $generator->generate(
            'recover_password',
            ['token' => 'aze']
        );

        $this->assertEquals('/password/recover/aze', $result);
    }

    public function testGenerateAbsoluteUrl()
    {
        $routes = [
            'GET' => ['recover_password' => new Route('/password/recover/:token', 'recover_password',[])]
        ];
        $generator = new UrlGenerator($routes);
        $result = $generator->generate(
            'recover_password',
            ['token' => 'aze'],
            'GET',
            'http://aze.fr:80808'
        );

        $this->assertEquals('http://aze.fr:80808/password/recover/aze', $result);
    }
}
