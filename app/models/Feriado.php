<?php

class Feriado extends Eloquent {
    
    protected $table = 'feriados';
    
    public function anio(){
        return $this->belongsTo('AnioRemuneracion','anio_id');
    }   
    
    static function feriados($mes, $remuneracion=null)
    {
        $listaFeriados = array();
        if($remuneracion){
            $feriados = Feriado::whereBetween('fecha', [$mes, $remuneracion])->get();
        }else{
            $feriados = Feriado::where('fecha', '>=', $mes)->get();            
        }
        
        if($feriados){
            foreach($feriados as $feriado){
                $listaFeriados[] = $feriado['fecha'];
            }
        }
        return $listaFeriados;
    }
    
    static function totalFeriados($mes, $remuneracion)
    {
        $listaFeriados = array();
        $feriados = DB::table('feriados')->whereBetween('fecha', [$mes, $remuneracion])->count();

        return $feriados;
    }
    
    static function comprobar($feriados, $mesActual)
    {
        $create = array();
        $destroy = array();
        $mes = $mesActual['mes'];
        $remuneracion = $mesActual['fechaRemuneracion'];
        
        $actuales = Feriado::whereBetween('fecha', [$mes, $remuneracion])->get();
        if($actuales){
            foreach($actuales as $actual){
                if(in_array($actual->fecha, $feriados)){
                    $key = array_search($actual->fecha, $feriados);
                    if(false !== $key) {
                        unset($feriados[$key]);
                    }
                }else{
                    $destroy[] = $actual->id;                        
                }
            }
        }
        
        $datos = array(
            'create' => $feriados,
            'destroy' => $destroy
        );
                    
        return $datos;
    }
    
    static function masivo($feriados, $anio)
    {        
        if($feriados['create']){
            foreach($feriados['create'] as $feriado){
                $nuevoFeriado = new Feriado();
                $nuevoFeriado->sid = Funciones::generarSID();
                $nuevoFeriado->anio_id = $anio['id'];
                $nuevoFeriado->fecha = $feriado;
                $nuevoFeriado->save();            
            }
        }
        
        if($feriados['destroy']){
            foreach($feriados['destroy'] as $feriado){
                $feriadoActual = Feriado::find($feriado);
                $feriadoActual->delete();            
            }
        }
        
        return;
    }
    
    static function errores($datos){
         
        $rules = array(
        );

        $message = array(
            'feriado.required' => 'Obligatorio!'
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->messages();
        }else{
            return false;
        }
    }
}