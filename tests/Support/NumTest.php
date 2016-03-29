<?php

use NetForceWS\Support\Num;

class NumTest extends TestCase
{
    /**
     * Descorbir o percentual.
     */
    public function testPercentage()
    {
        $per = Num::percentage(1234, 401.05, 2);
        $this->assertEquals(32.5, $per);

        $per = Num::percentage(1234.33, 493.73, 2);
        $this->assertEquals(40, $per);

        $per = Num::percentage(1234.33, 493.73, 4);
        $this->assertEquals(39.9998, $per);
    }

    /**
     * Descobrir a parte.
     */
    public function testPercent()
    {
        $val = Num::percent(1234, 32.5, 2);
        $this->assertEquals(401.05, $val);

        $val = Num::percent(7654.33, 11, 2);
        $this->assertEquals(841.98, $val);
    }

    /**
     * Testar formatacao.
     */
    public function testFormat()
    {
        $str = Num::format(1234.334, 2);
        $this->assertEquals('1.234,33', $str);

        $str = Num::format(1234.336, 2);
        $this->assertEquals('1.234,34', $str);

        $str = Num::format(1234.336, 3);
        $this->assertEquals('1.234,336', $str);
    }

    /**
     * Testar transformacao de str para value.
     */
    public function testValue()
    {
        $val = Num::value('1234,877', 2);
        $this->assertEquals(1234.88, $val);

        $val = Num::value('1234,8778', 3);
        $this->assertEquals(1234.878, $val);
    }
}
