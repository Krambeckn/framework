<?php

use \NetForceWS\Support\Arr;

class ArrPhpFileTest extends TestCase
{
    protected $config = null;

    protected function setUp()
    {
        // Gerar array
        $args            = [];
        $args['empresa'] = 'netforce';
        $args['dono']    = [
            'nome' => 'bruno',
            'site' => 'www.netforce.com.br',
        ];

        // Gerar arquivo
        $file = __DIR__ . '/tmp.php';
        file_put_contents($file, Arr::toPhpFile($args));

        // Carregar arquivo
        $this->config = require $file;
        unlink($file);
    }

    /**
     * verificar se o key empresa foi criado
     */
    public function testTemEmpresa()
    {
        $this->assertArrayHasKey('empresa', $this->config);
        $this->assertInternalType('string', $this->config['empresa']);
        $this->assertEquals('netforce', $this->config['empresa']);
    }

    /**
     * verificar se o key dono foi criado
     */
    public function testTemDono()
    {
        $this->assertArrayHasKey('dono', $this->config);
        $this->assertInternalType('array', $this->config['dono']);
    }

    /**
     * verificar se o key dono foi criado
     */
    public function testQualDono()
    {
        $dono = $this->config['dono'];

        $this->assertArrayHasKey('nome', $dono);
        $this->assertInternalType('string', $dono['nome']);
        $this->assertEquals('bruno', $dono['nome']);

        $this->assertArrayHasKey('site', $dono);
        $this->assertInternalType('string', $dono['site']);
        $this->assertEquals('www.netforce.com.br', $dono['site']);
    }
}
