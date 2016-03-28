<?php namespace NetForceWS\Support\Console;


trait ExistsCommand
{
    /**
     * Verificar se um comando existe
     * @param $command
     * @return bool
     */
    public function exists($command)
    {
        try
        {
            $this->getApplication()->find($command);
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }
}