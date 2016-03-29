<?php namespace NetForceWS\IO;

class Template
{
    protected $filters = [];
    protected $outpath = '';

    /**
     * Aplicar template.
     *
     * @param $template
     * @param $outpath
     * @param array $renames
     *
     * @return $this
     */
    public function make($template, $outpath, $renames = [])
    {
        // Verificar se template existe na lista
        if (\File::exists($template) != true) {
            error('Template %s not found', $template);
        }

        $this->outpath = $outpath;

        $renames = array_merge(['.php.txt' => '.php'], $renames);

        // Sincronizar arquivos
        \File::synchronize($template, $outpath, $renames);

        return $this;
    }

    /**
     * Aplicar template de um arquivo único.
     *
     * @param $file
     * @param $target
     */
    public function file($file, $target)
    {
        // Verificar se template existe na lista
        if (\File::exists($file) != true) {
            error('Template %s não foi encontrado', $file);
        }

        $this->outpath = '';
        $this->filters = [$target];

        // Criar diretório destino
        $path = \File::path($target);
        \File::force($path);

        // Copiar arquivo
        \File::copy($file, $target);

        return $this;
    }

    /**
     * Registrar filtro do template.
     */
    public function filter($filter)
    {
        if (is_array($filter) != true) {
            $filter = [$filter];
        }

        foreach ($filter as $item) {
            $this->filters[] = str_replace('./', $this->outpath . '/', $item);
        }

        return $this;
    }

    /**
     * Registrar parametro do template.
     */
    public function param($name, $value = '')
    {
        // Se foi informado um array
        if (is_array($name)) {
            foreach ($name as $n => $v) {
                $this->param($n, $v);
            }

            return $this;
        }

        // Aplicar parâmetro nos filtros
        foreach ($this->filters as $filter_file) {
            if (\File::exists($filter_file) != true) {
                error('File %s not found', $filter_file);
            }

            $buffer = file_get_contents($filter_file);
            $buffer = str_replace('{{' . $name . '}}', $value, $buffer);
            file_put_contents($filter_file, $buffer);
        }

        return $this;
    }
}