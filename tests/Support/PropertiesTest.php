<?php

use NetForceWS\Support\Num;

class PropertiesTest extends TestCase
{
    protected function setUp()
    {
        include __DIR__ . '/Utils/Propriedades.php';
    }

    public function testAttrNome()
    {
        $values = new Propriedades();
        $this->assertEquals('johann', $values->nome);
        $this->assertEquals(null, $values->idade);//
    }
}
