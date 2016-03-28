<?php

use NetForceWS\Support\Str;

class StringTest extends TestCase
{
    public function testStartWith()
    {
        $this->assertFalse(Str::getStartsWith('netforce sistema', 'etforce'));
        $this->assertEquals('netforce', Str::getStartsWith('netforce sistema', 'netforce'));
    }

    public function testFormat()
    {
        $str = Str::format('netforce', 'nome: %s');
        $this->assertEquals('nome: netforce', $str);
    }

    public function testValues()
    {
        $str = Str::values('nome: {nome}', ['nome' => 'netforce']);
        $this->assertEquals('nome: netforce', $str);
    }

    public function testBefore()
    {
        $str = Str::before('NetForce/Sistemas/Ultima');
        $this->assertEquals('NetForce/Sistemas', $str);
    }

    public function testLast()
    {
        $str = Str::last('NetForce/Sistemas/Ultima');
        $this->assertEquals('Ultima', $str);
    }

    public function testComErro()
    {
        $this->assertTrue(false);
    }
}
