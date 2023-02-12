<?php


use Router\Parameter;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
	public function testINT()
	{
		preg_match('/' . Parameter::INT . '/', '123',$matches);
		$this->assertCount(1, $matches);
		$this->assertEquals(123, $matches[0]);
	}

	public function testALPHA()
	{
		preg_match('/' . Parameter::ALPHA . '/', 'AaZ',$matches);
		$this->assertCount(1, $matches);
		$this->assertEquals('AaZ', $matches[0]);
	}

	public function testWORD()
	{
		preg_match('/' . Parameter::WORD . '/', 'AaZ132',$matches);
		$this->assertCount(1, $matches);
		$this->assertEquals('AaZ132', $matches[0]);
	}
}
