<?php

class FeriadoVacaciones extends Eloquent {
    
    protected $table = 'feriados_vacaciones';
    
    public function anio(){
        return $this->belongsTo('AnioRemuneracion','anio_id');
    }   
    
    static function feriados($mes, $remuneracion=null)
    {
        $listaFeriados = array();
        if($remuneracion){
            $feriados = FeriadoVacaciones::whereBetween('fecha', [$mes, $remuneracion])->get();
        }else{
            $feriados = FeriadoVacaciones::where('fecha', '>=', $mes)->get();            
        }
        
        if($feriados){
            foreach($feriados as $feriado){
                $listaFeriados[] = $feriado['fecha'];
            }
        }
        return $listaFeriados;
    }    
    
    static function comprobar($feriados, $mesActual)
    {
        $create = array();
        $destroy = array();
        $mes = $mesActual['mes'];
        $remuneracion = $mesActual['fechaRemuneracion'];
        
        $actuales = FeriadoVacaciones::whereBetween('fecha', [$mes, $remuneracion])->get();
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
                $nuevoFeriado = new FeriadoVacaciones();
                $nuevoFeriado->sid = Funciones::generarSID();
                $nuevoFeriado->anio_id = $anio['id'];
                $nuevoFeriado->fecha = $feriado;
                $nuevoFeriado->save();            
            }
        }
        
        if($feriados['destroy']){
            foreach($feriados['destroy'] as $feriado){
                $feriadoActual = FeriadoVacaciones::find($feriado);
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