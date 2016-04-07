<?php

use Carbon\Carbon;

class PropertiesTest extends TestCase
{
    protected $proper;

    protected function setUp()
    {
        include_once __DIR__ . '/Utils/Propriedades.php';

        $this->proper = new Propriedades();
    }

    public function testAttrNome()
    {
        $this->assertEquals('johann', $this->proper->nome);
        $this->assertEquals(null, $this->proper->idade);
    }

    public function testFromJson()
    {
        $obj = new stdClass();
        $obj->nome = 'Teste';
        $this->assertEquals($obj, $this->proper->fromJson('{"nome": "Teste"}', true));
        $this->assertEquals((array) $obj, $this->proper->fromJson('{"nome": "Teste"}', false));
    }

    public function testAsDateTime()
    {
        $carbon = Carbon::createFromFormat('d/m/Y', '15/02/2015')->startOfDay();

        $this->assertEquals($carbon, $this->proper->datecarbon);
        $this->assertEquals($carbon, $this->proper->dateymd);
        $this->assertEquals($carbon, $this->proper->datetime);
        $this->assertEquals($carbon, $this->proper->datenumber);
    }
}
