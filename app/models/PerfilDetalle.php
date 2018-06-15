<?php
class PerfilDetalle extends Eloquent {

    protected $table = 'perfiles_detalles';
    protected $connection = "principal";
 
    public function perfil(){
        return $this->belongsTo('Perfil', 'perfil_id');
    }
    public function empresa(){
        return $this->belongsTo('Empresa', 'empresa_id');
    }
    public function menu(){
        return $this->belongsTo('MenuSistema', 'menu_id');
    }
    static function detalles($perfil_id){
        $detalle = PerfilDetalle::where('perfil_id','=', $perfil_id)->get();
        $lista=array();
        if($detalle->count()){
            foreach($detalle as $item){
                $lista[ $item->menu_id ] = $item->tipo_acceso;
            }
        }
        return $lista;
    }
}