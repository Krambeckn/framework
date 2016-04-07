<?php

class Propriedades
{
    use \NetForceWS\Support\Properties;

    public function getNomeAttribute()
    {
        return 'johann';
    }

    public function getDateymdAttribute()
    {
        return $this->asDateTime('2015-02-15');
    }

    public function getDatecarbonAttribute()
    {
        return $this->asDateTime(\Carbon\Carbon::createFromFormat('d/m/Y', '15/02/2015')->startOfDay());
    }

    public function getDateTimeAttribute()
    {
        return $this->asDateTime(strtotime('2015-02-15 00:00:00'));
    }

    public function getDatenumberAttribute()
    {
        return $this->asDateTime(1423958400);
    }
}