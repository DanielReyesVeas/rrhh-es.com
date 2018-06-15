<?php
class UsuarioPerfilDetalle extends Eloquent {
    protected $table = 'usuarios_perfil_detalles';
    protected $connection = "principal";
 
    public function usuario(){
        return $this->belongsTo('User', 'usuario_id');
    }
    
    public function menu(){
        return $this->belongsTo('MenuSistema', 'menu_id');
    }
    
    public function empresa(){
        return $this->belongsTo('Empresa', 'empresa_id');
    }
 
}