<?php namespace NetForceWS\Database\Models;

trait HelpersModel
{
    /**
     * Return if is insert mode.
     *
     * @return bool
     */
    public function isInsert()
    {
        return array_key_exists($this->primaryKey, $this->original) != true;
    }
}