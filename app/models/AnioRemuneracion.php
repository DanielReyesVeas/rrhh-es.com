<?php

class AnioRemuneracion extends Eloquent {
    
    protected $table = 'anios_remuneraciones';
    
    public function mesesDeTrabajo(){
        return $this->hasMany('MesDeTrabajo', 'anio_id')->orderBy('mes', 'ASC');
    }
    
    public function meses()
    {
        $listaMeses = array();
        $id = $this->id;
        $meses = MesDeTrabajo::where('anio_id', $id)->orderBy('mes', 'ASC')->get();
        
        if($meses->count()){
            foreach($meses as $mes){
                $listaMeses[] = array(
                    'id' => $mes->id,
                    'sid' => $mes->sid,
                    'nombre' => $mes->nombre,
                    'mes' => $mes->mes,
                    'fechaRemuneracion' => $mes->fecha_remuneracion
                );
            }
        }
        
        return $listaMeses;        
    }
    
    public function misMeses()
    {
        $listaMeses = array();
        $meses = $this->mesesDeTrabajo;
        
        if($meses->count()){
            foreach($meses as $mes){
                $listaMeses[$mes->mes] = array(
                    'id' => $mes->id,
                    'sid' => $mes->sid,
                    'nombre' => $mes->nombre,
                    'mes' => $mes->mes,
                    'indicadores' => $mes->indicadores,
                    'fechaRemuneracion' => $mes->fecha_remuneracion
                );
            }
        }
        
        return $listaMeses;        
    }
    
    public function isNuevoAnio()
    {
        $empresa =  \Session::get('empresa');
        Config::set('database.default', 'admin' );                
        $isIngresado = DB::table('meses')->where('mes', '2018-01-01')->first();
        Config::set('database.default', $empresa->base_datos );
        
        if($isIngresado){
            return true;
        }
        
        return $isIngresado;
    }
    
    public function mesesFestivos()
    {
        $listaMeses = array();
        $anio = $this->anio;
        $meses = array();
        
        for($i=1; $i<=12; $i++){
            if($i<10){
                $index = '0' . $i;
            }else{
                $index = $i;                
            }
            $nombre = Funciones::obtenerMesTexto($index);
            $mes = $anio . "-" . $index . "-01";
            $remuneracion = Funciones::obtenerFechaRemuneracion($nombre, $anio);
            $meses[] = array(
                'nombre' => $nombre,
                'mes' => $mes,
                'fechaRemuneracion' => $remuneracion,
                'feriados' => FeriadoVacaciones::feriados($mes, $remuneracion)
            );
        }
        
        return $meses;        
    }
    
    static function aniosF1887()
    {
        $lista = array();
        $anios = AnioRemuneracion::orderBy('anio', 'DESC')->get();
                
        if( $anios->count() ){
            foreach( $anios as $anio ){
                $lista[]=array(
                    'id' => $anio->id,
                    'sid' => $anio->sid,
                    'nombre' => $anio->anio,
                    'isDiciembre' => $anio->isDiciembre()
                );
            }
        }
        
        return $lista;
    }
    
    public function isDiciembre()
    {
        $diciembre = MesDeTrabajo::where('anio_id', $this->id)->where('nombre', 'Diciembre')->first();
        if($diciembre){
            return true;
        }
        
        return false;
    }
    
    public function mesesFestivosVacaciones()
    {
        $listaMeses = array();
        $anio = $this->anio;
        $meses = array();
        
        for($i=1; $i<=12; $i++){
            if($i<10){
                $index = '0' . $i;
            }else{
                $index = $i;                
            }
            $nombre = Funciones::obtenerMesTexto($index);
            $mes = $anio . "-" . $index . "-01";
            $remuneracion = Funciones::obtenerFechaRemuneracion($nombre, $anio);
            $meses[] = array(
                'nombre' => $nombre,
                'mes' => $mes,
                'fechaRemuneracion' => $remuneracion,
                'feriados' => FeriadoVacaciones::feriados($mes, $remuneracion)
            );
        }
        
        return $meses;        
    }
    
    static function isMesAbierto()
    {
        $mes = \Session::get('mesActivo');
        $nombre = strtolower($mes->nombre);
        $idAnio = $mes->idAnio;
        $abierto = AnioRemuneracion::find($idAnio)->$nombre ? true : false;
        
        return $abierto;
    }
    
    static function isLiquidaciones($mes=null)
    {
        if(!$mes){
            $mesDeTrabajo = \Session::get('mesActivo')->id;
        }else{
            $mesDeTrabajo = MesDeTrabajo::where('mes', $mes)->first();            
            $mesDeTrabajo = $mesDeTrabajo['id'];            
        }
        $bool = Trabajador::isAllLiquidados($mesDeTrabajo);
        
        return $bool;    
    }
    
    static function isCuentas()
    {
        $bool = Aporte::isCuentas();
        
        return $bool;    
    }
    
    public function isDisponible($mes)
    {
        $empresa =  \Session::get('empresa');
        
        Config::set('database.default', 'admin' );                
        $isDisponible = DB::table('meses')->where('mes', $mes)->first();
        Config::set('database.default', $empresa->base_datos );
        
        if($isDisponible){
            return true;
        }
        
        return false;
    }
    
    public function isCentralizado($mes)
    {
        $centralizacion = ComprobanteCentralizacion::where('mes', $mes)->first();
        if($centralizacion){
            return true;
        }
        
        return false;
    }
    
    public function estadoMeses()
    {
        $estadoMeses = array();
        $anio = $this->anio;
        $meses = Funciones::listaMeses($anio);
        $misMeses = $this->misMeses();
        
        foreach($meses as $mes){
            $isIniciado = isset($misMeses[$mes['mes']]);
            $isDisponible = true;
            $isIndicadores = false;
            $disponibleSinIndicadores = false;            
            $var = strtolower($mes['nombre']);
            
            if($isIniciado){                
                $isIndicadores = $misMeses[$mes['mes']]['indicadores'] ? true : false;
                if(!$isIndicadores){
                    $isDisponible = $this->isDisponible($mes['mes']);    
                }
            }else{
                $isDisponible = $this->isDisponible($mes['mes']);
                $disponibleSinIndicadores = (!$isDisponible);
            }
            
            $estadoMeses[] = array(
                'nombre' => $mes['nombre'],
                'abierto' => $this->$var ? true : false,
                'iniciado' => isset($misMeses[$mes['mes']]),
                'disponible' => $isDisponible, 
                'indicadores' => $isIndicadores, 
                'mes' => $mes['mes'],
                'fechaRemuneracion' => $mes['fechaRemuneracion'],
                'isCentralizado' => $this->isCentralizado($mes['mes']),
                'disponibleSinIndicadores' => $disponibleSinIndicadores
            );            
        }
        /*
        foreach($meses as $mes){
            $nombre = strtolower($mes['value']);
            $isIniciado = $this->isIniciado($mes['value']);
            $isDisponible = true;
            $isIndicadores = false;
            $fecha = Funciones::obtenerFechaMes($mes['value'], $anio);
            if(!$isIniciado){
                $isDisponible = $this->isDisponible($mes['value']);
            }else{
                $isIndicadores = $this->isIndicadores($fecha);    
                $isDisponible = $this->isDisponible($mes['value']);
            }
            $estadoMeses[] = array(
                'nombre' => $mes['value'],
                'abierto' => $this->$nombre ? true : false,
                'iniciado' => $isIniciado,
                'disponible' => $isDisponible, 
                'indicadores' => $isIndicadores, 
                'mes' => $fecha,
                'fechaRemuneracion' => Funciones::obtenerFechaRemuneracion($mes['value'], $anio),
                'isCentralizado' => AnioRemuneracion::isCentralizado(Funciones::obtenerFechaMes($mes['value'], $anio)),
                'disponibleSinIndicadores' => $this->isDisponibleSinIndicadores($mes['value'])
            );            
        } */           

        return $estadoMeses;
    }
    
    static function errores($datos){
         
        $rules = array(
        );

        $message = array(
            'anioRemuneracion.required' => 'Obligatorio!'
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