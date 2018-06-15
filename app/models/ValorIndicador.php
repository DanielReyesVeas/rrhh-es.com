<?php

class ValorIndicador extends Eloquent {
    
    protected $table = 'valores_indicadores';
    protected $connection = "principal";
    
    public function indicador(){
		return $this->belongsTo('Indicador', 'indicador_id');
	}
    
    static function crearIndicadores($mes, $fechaRemuneracion)
    {   
        $indicadores = ValorIndicador::where('mes', $mes)->get();        
        $rentasTopesImponibles = RentaTopeImponible::where('mes', $mes)->get();
        $rentasMinimasImponibles = RentaMinimaImponible::where('mes', $mes)->get();
        $ahorrosPrevisionalesVoluntarios = AhorroPrevisionalVoluntario::where('mes', $mes)->get();
        $depositosConvenidos = DepositoConvenido::where('mes', $mes)->get();
        $segurosDeCesantia = SeguroDeCesantia::where('mes', $mes)->get();
        $tasasCotizacionObligatorioAfp = TasaCotizacionObligatorioAfp::where('mes', $mes)->get();
        $asignacionesFamiliares = AsignacionFamiliar::where('mes', $mes)->get();
        $cotizacionesTrabajosPesados = CotizacionTrabajoPesado::where('mes', $mes)->get();
        $tablaImpuestoUnico = TablaImpuestoUnico::where('mes', $mes)->get();
        
        Config::set('database.default', 'admin');
        
        if(!$indicadores->count()){
            $indicadores = DB::table('valores_indicadores')->where('mes', $mes)->get();
            foreach($indicadores as $indicador){
                $nuevoIndicador = new ValorIndicador();
                $nuevoIndicador->mes = $mes;
                $nuevoIndicador->fecha = $fechaRemuneracion;
                $nuevoIndicador->indicador_id = $indicador->indicador_id;
                $nuevoIndicador->valor = $indicador->valor;
                $nuevoIndicador->save();            
            }
        }
        
        if(!$rentasTopesImponibles->count()){
            $rentasTopesImponibles = DB::table('rentas_topes_imponibles')->where('mes', $mes)->get();
            foreach($rentasTopesImponibles as $rti){
                $rentaTopeImponible = new RentaTopeImponible();
                $rentaTopeImponible->mes = $mes;
                $rentaTopeImponible->nombre = $rti->nombre;
                $rentaTopeImponible->valor = $rti->valor;
                $rentaTopeImponible->save();            
            }            
        }
        
        if(!$rentasMinimasImponibles->count()){
            $rentasMinimasImponibles = DB::table('rentas_minimas_imponibles')->where('mes', $mes)->get();
            foreach($rentasMinimasImponibles as $rmi){
                $rentaMinimaImponible = new RentaMinimaImponible();
                $rentaMinimaImponible->mes = $mes;
                $rentaMinimaImponible->nombre = $rmi->nombre;
                $rentaMinimaImponible->valor = $rmi->valor;
                $rentaMinimaImponible->save();            
            }
        }
        
        if(!$ahorrosPrevisionalesVoluntarios->count()){
            $ahorrosPrevisionalesVoluntarios = DB::table('ahorro_previsional_voluntario')->where('mes', $mes)->get();
            foreach($ahorrosPrevisionalesVoluntarios as $apv){
                $ahorroPrevisionalVoluntario = new AhorroPrevisionalVoluntario();
                $ahorroPrevisionalVoluntario->mes = $mes;
                $ahorroPrevisionalVoluntario->nombre = $apv->nombre;
                $ahorroPrevisionalVoluntario->valor = $apv->valor;
                $ahorroPrevisionalVoluntario->save();            
            }
        }
        
        if(!$depositosConvenidos->count()){
            $depositosConvenidos = DB::table('deposito_convenido')->where('mes', $mes)->get();
            foreach($depositosConvenidos as $dc){
                $depositoConvenido = new DepositoConvenido();
                $depositoConvenido->mes = $mes;
                $depositoConvenido->nombre = $dc->nombre;
                $depositoConvenido->valor = $dc->valor;
                $depositoConvenido->save();            
            }
        }
        
        if(!$segurosDeCesantia->count()){
            $segurosDeCesantia = DB::table('seguro_de_cesantia')->where('mes', $mes)->get();
            foreach($segurosDeCesantia as $sc){
                $seguroDeCesantia = new SeguroDeCesantia();
                $seguroDeCesantia->mes = $mes;
                $seguroDeCesantia->tipo_contrato = $sc->tipo_contrato;
                $seguroDeCesantia->financiamiento_empleador = $sc->financiamiento_empleador;
                $seguroDeCesantia->financiamiento_trabajador = $sc->financiamiento_trabajador; 
                $seguroDeCesantia->save();            
            }
        }
        
        if(!$tasasCotizacionObligatorioAfp->count()){
            $tasasCotizacionObligatorioAfp = DB::table('tasa_cotizacion_obligatorio_afp')->where('mes', $mes)->get();
            foreach($tasasCotizacionObligatorioAfp as $tcoa){
                $tasaCotizacionObligatorioAfp = new TasaCotizacionObligatorioAfp();
                $tasaCotizacionObligatorioAfp->mes = $mes;
                $tasaCotizacionObligatorioAfp->afp_id = $tcoa->afp_id;
                $tasaCotizacionObligatorioAfp->tasa_afp = $tcoa->tasa_afp;
                $tasaCotizacionObligatorioAfp->sis = $tcoa->sis;
                $tasaCotizacionObligatorioAfp->tasa_afp_independientes = $tcoa->tasa_afp_independientes;      
                $tasaCotizacionObligatorioAfp->save();            
            }
        }
        
        if(!$asignacionesFamiliares->count()){
            $asignacionesFamiliares = DB::table('asignacion_familiar')->where('mes', $mes)->get();
            foreach($asignacionesFamiliares as $af){
                $asignacionFamiliar = new AsignacionFamiliar();
                $asignacionFamiliar->mes = $mes;
                $asignacionFamiliar->tramo = $af->tramo;
                $asignacionFamiliar->monto = $af->monto;
                $asignacionFamiliar->renta_menor = $af->renta_menor;
                $asignacionFamiliar->renta_mayor = $af->renta_mayor;                
                $asignacionFamiliar->save();            
            }
        }
        
        if(!$cotizacionesTrabajosPesados->count()){
            $cotizacionesTrabajosPesados = DB::table('cotizacion_trabajos_pesados')->where('mes', $mes)->get();
            foreach($cotizacionesTrabajosPesados as $ctp){
                $cotizacionTrabajosPesados = new CotizacionTrabajoPesado();
                $cotizacionTrabajosPesados->mes = $mes;
                $cotizacionTrabajosPesados->trabajo = $ctp->trabajo;
                $cotizacionTrabajosPesados->valor = $ctp->valor;
                $cotizacionTrabajosPesados->financiamiento_empleador = $ctp->financiamiento_empleador;
                $cotizacionTrabajosPesados->financiamiento_trabajador = $ctp->financiamiento_trabajador;               
                $cotizacionTrabajosPesados->save();            
            }
        }
            
        if(!$tablaImpuestoUnico->count()){
            $tablaImpuestoUnico = DB::table('tabla_impuesto_unico')->where('fecha_desde', '<=', $mes)->where('fecha_hasta', '>=', $mes)->orderBy('tramo')->get();
            foreach($tablaImpuestoUnico as $tabla){
                $nuevaTabla = new TablaImpuestoUnico();
                $nuevaTabla->mes = $mes;
                $nuevaTabla->tramo = $tabla->tramo;
                $nuevaTabla->imponible_mensual_desde = $tabla->imponible_mensual_desde;
                $nuevaTabla->imponible_mensual_hasta = $tabla->imponible_mensual_hasta;
                $nuevaTabla->factor = $tabla->factor;
                $nuevaTabla->cantidad_a_rebajar = $tabla->cantidad_a_rebajar;               
                $nuevaTabla->save();            
            }
        }
        
        return;
    }
    
    static function valorFecha($fecha)
    {
        $mes = Session::get('mesActivo');
        if(!$mes->indicadores){
            $m = date('m', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
            $y = date('Y', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
            $fecha = Funciones::obtenerFechaRemuneracionMes($m, $y);
        }
        $valoresIndicadores = ValorIndicador::where('fecha', $fecha)->get();
        $datosValoresIndicadores = new stdClass();
        
        if($valoresIndicadores->count()){
            foreach($valoresIndicadores as $valorIndicador){
                $nombre = $valorIndicador->indicador->nombre;
                $datosValoresIndicadores->$nombre = array(
                    'id' => $valorIndicador->id,
                    'valor' => $valorIndicador->valor,
                    'indicador' => $valorIndicador->indicador->nombre,
                    'fecha' => $valorIndicador->fecha
                );        
            }
        
            return $datosValoresIndicadores;
        }else{
            return null;
        }                        
    }
    
    static function ufAnterior()
    {
        $mes = \Session::get('mesActivo')->mes;
        $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
        $ufAnterior = ValorIndicador::where('indicador_id', 1)->where('mes', $fecha)->first();
        
        return $ufAnterior;                        
    }
    
    static function errores($datos){
         
        $rules = array(
            'indicador_id' => 'required',
            'valor' => 'required',
            'fecha' => 'required'
        );

        $message = array(
            'valorIndicador.required' => 'Obligatorio!'
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