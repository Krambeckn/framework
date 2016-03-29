<?php

class HelpersTest extends TestCase
{
    /**
     * Geracao de erro com codigo.
     */
    public function testGerarErrorSemCodigo()
    {
        try {
            error('nome: %s', 'bruno');
            $this->assertTrue(false, 'Nao deveria passar por aqui');
        } catch (Exception $e) {
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals('nome: bruno', $e->getMessage());
        }
    }

    /**
     * Geracao de erro com codigo.
     */
    public function testGerarErrorComCodigo()
    {
        try {
            error('nome: %s', 'bruno', 123);
            $this->assertTrue(false, 'Nao deveria passar por aqui');
        } catch (Exception $e) {
            $this->assertEquals(123, $e->getCode());
            $this->assertEquals('nome: bruno', $e->getMessage());
        }
    }
}
