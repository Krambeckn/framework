<?php

use NetForceWS\Support\ExceptionAttributes;

class ExceptionTest extends TestCase
{
    public function testReturnGetAttrs()
    {
        $attrs = ['nome' => ['obrigatorio', '6max'], 'idade' => ['faltou']];

        $ex = new ExceptionAttributes('Teste', 2, $attrs);
        $this->assertNotEmpty($ex->getAttrs());
        $this->assertEquals($attrs, $ex->getAttrs());

        $lines = [];
        $lines[] = "nome: obrigatorio. 6max\r\n";
        $lines[] = "idade: faltou\r\n";
        $msg = sprintf("%s\r\n%s", 'Teste', implode("\r\n", $lines));

        $this->assertEquals($msg, $ex->toMessageStr());
    }

    public function testContructor()
    {
        try {
            throw new ExceptionAttributes('nome: johann', 0, []);
        } catch (Exception $e) {
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals('nome: johann', $e->getMessage());
        }
    }
}