<?php
namespace base\orm;
use gamboamartin\errores\errores;

class sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    public function show_tables(): string
    {
        return "SHOW TABLES";
    }

}
