<?php

class Vacaciones extends Eloquent {
    
    protected $table = 'vacaciones';

    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function miMes(){
        return $this->belongsTo('MesDeTrabajo','mes', 'mes');
    }
    
    public function tomaVacaciones(){
        $tomasVacaciones = TomaVacaciones::where('trabajador_id', $this->trabajador_id)->where('mes', $this->mes)->get();
        return $tomasVacaciones;
    }
    
    public function isTomaVacaciones()
    {
        $tomasVacaciones = $this->tomaVacaciones();        
        
        if($tomasVacaciones->count()){
            return true;    
        }
        
        return false;
    }
    
    public function totalTomaVacaciones()
    {
        $tomasVacaciones = $this->tomaVacaciones();
        $total = 0;
        
        if($tomasVacaciones->count()){
            foreach($tomasVacaciones as $tomaVacaciones){
                $total = ($total + $tomaVacaciones->dias);
            }            
        }
        
        return $total;
    }
    
    public function sumaTomaVacaciones()
    {
        $tomasVacaciones = TomaVacaciones::where('trabajador_id', $this->trabajador_id)->where('mes', '<=', $this->mes)->get();
        $total = 0;
        foreach($tomasVacaciones as $toma){
            $total += $toma->dias;
        }
        
        return $total;
    }
    
    public function tomaVacacionesMes()
    {
        $tomasVacaciones = $this->tomaVacaciones();
        $detalle = array();
        
        if($tomasVacaciones->count()){
            foreach($tomasVacaciones as $tomaVacaciones){
                $detalle[] = array(
                    'id' => $tomaVacaciones->id,
                    'sid' => $tomaVacaciones->sid,
                    'mes' => $tomaVacaciones->mes,
                    'desde' => $tomaVacaciones->desde,
                    'hasta' => $tomaVacaciones->hasta,
                    'dias' => $tomaVacaciones->dias,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($tomaVacaciones->created_at))
                );
            }    
        }
        
        return $detalle;
    }    
    
    public function totalDevengadas()
    {
        $tomasVacaciones = $this->tomaVacaciones();
        $total = 0;
        
        if($tomasVacaciones->count()){
            foreach($tomasVacaciones as $tomaVacaciones){
                $total = ($total + $tomaVacaciones->dias);
            }    
        }
        
        return $total;
    }
    
    static function calcularVacaciones($trabajador, $empleado, $mesActual)
    {
        $idTrabajador = $trabajador->id;        
        $i = 0;
        do{
            $i = ($i + 1);
            if(!$mesActual){
                $mesActual = \Session::get('mesActivo');
            }
            $mes = $mesActual->mes;
            $idMesActual = $mesActual->id;
            $fecha = date('Y-m-d', strtotime('-' . $i . ' month', strtotime($mes)));
            $idMesAnterior = MesDeTrabajo::where('mes', $fecha)->first()->id;

            $misVacaciones = Vacaciones::where('trabajador_id', $idTrabajador)->where('mes', $fecha)->first();
            
        }while(!$misVacaciones && $idMesAnterior!=1);
        
        if($misVacaciones){
            $vacas = ($misVacaciones->dias + (1.25 * $i));
        }else{
            $vacas = 0;
        }
        
        $vacaciones = new Vacaciones();
        $vacaciones->sid = Funciones::generarSID();;
        $vacaciones->trabajador_id = $idTrabajador;
        $vacaciones->mes = $mesActual->mes;
        $vacaciones->dias = $vacas;
        $vacaciones->save(); 
        
        return;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'vacaciones.required' => 'Obligatorio!'
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