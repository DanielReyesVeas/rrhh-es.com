<?php

class F1887 extends Eloquent {
    
    protected $table = 'f1887';
    
    public function detalles(){
        return $this->hasMany('DetalleF1887','f1887_id')->orderBy('id');
    }
    
    static function obtenerFolio()
    {
        $folio = (DB::table('f1887')->max('folio') + 1);    
        
        $len = strlen($folio);
        for($i=$len; $i<7; $i++){
            $folio = '0' . $folio;
        }
        
        return $folio;
    }
    
    static function comprobarDeclaracion($anio)
    {
        $f1887 = F1887::where('anio', $anio)->first();
        
        if($f1887){
            DB::table('detalle_f1887')->where('f1887_id', $f1887->id)->delete();
            $f1887->delete();
        }
        
        return;
    }
    
    static function isDeclaracion($anio)
    {
        $f1887 = F1887::where('anio', $anio)->first();
        
        if($f1887){
            return true;
        }
        
        return false;
    }
    
}