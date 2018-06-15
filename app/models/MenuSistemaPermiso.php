<?php

class MenuSistemaPermiso extends \Eloquent {
    protected $table = "menu_permisos";
    protected $connection = "principal";

    public function menu(){
        return $this->belongsTo('MenuSistema', 'menu_id');
    }
}