<?php

class FichaTrabajador extends Eloquent {
    
    protected $table = 'fichas_trabajadores';
    
    public function trabajador(){
		return $this->belongsTo('Trabajador', 'trabajador_id');
	}
    
    public function afp(){
        return $this->belongsTo('Glosa', 'afp_id');
    }
    
    public function zonaImpuestoUnico(){
        return $this->belongsTo('ZonaImpuestoUnico', 'zona_id');
    }
    
    public function prevision(){
        return $this->belongsTo('Glosa', 'prevision_id');
    }
    
    public function afpApv(){
        return $this->belongsTo('Glosa', 'afp_id');
    }
    
    public function cargo(){
		return $this->belongsTo('Cargo', 'cargo_id');
	}
    
    public function tipoContrato(){
		return $this->belongsTo('TipoContrato', 'tipo_contrato_id');
	}
    
    public function nacionalidad(){
		return $this->belongsTo('Glosa', 'nacionalidad_id');
	}
    
    public function estadoCivil(){
		return $this->belongsTo('EstadoCivil', 'estado_civil_id');
	}
    
    public function comuna(){
        return $this->belongsTo('Comuna', 'comuna_id');
    }  
    
    public function seccion(){
		return $this->belongsTo('Seccion', 'seccion_id');
	}
    
    public function titulo(){
		return $this->belongsTo('Titulo', 'titulo_id');
	}
    
    public function tienda(){
		return $this->belongsTo('Tienda', 'tienda_id');
	}
    
    public function centroCosto(){
		return $this->belongsTo('CentroCosto', 'centro_costo_id');
	}
    
    public function banco(){
		return $this->belongsTo('Banco', 'banco_id');
	}
    
    public function tipoCuenta(){
		return $this->belongsTo('TipoCuenta', 'tipo_cuenta_id');
	}
    
    public function tipoJornada(){
		return $this->belongsTo('Jornada', 'tipo_jornada_id');
	}
    
    public function isapre(){
		return $this->belongsTo('Glosa', 'isapre_id');
	}
    
    public function tipo(){
		return $this->belongsTo('Glosa', 'tipo_id');
	}
    
    public function afpSeguro(){
        return $this->belongsTo('Glosa', 'afp_seguro_id');
    }           
    
    public function tramo(){
		return $this->belongsTo('AsignacionFamiliar', 'tramo_id');
	}            
    
    public function resumen($anio)
    {
        $desde = $anio . '-01-01';
        $hasta = $anio . '-12-01';
        $datos = array();
        $id = $this->trabajador->id;
        $liquidaciones = Liquidacion::where('trabajador_id', $id)->where('mes', '<=', $hasta)->where('mes', '>=', $desde)->get();
        $lista = array();
        $meses = Funciones::crearMesesAnio($anio);
        $factores = FactorActualizacion::listaFactores($anio);
        $totalSueldo = 0;
        $totalCotizacionPrevisional = 0;
        $totalRentaImponible = 0;
        $totalImpuestoUnico = 0;
        $totalMayorRetencion = 0;
        $totalRentaTotal = 0;
        $totalRentaNoGravada = 0;
        $totalRebaja = 0;
        $totalFactor = 0;
        $totalRentaAfecta = 0;
        $totalImpuestoUnicoRetenido = 0;
        $totalMayorRetencionImpuesto = 0;
        $totalRentaTotalExenta = 0;
        $totalRentaTotalNoGravada = 0;
        $totalRebajaZonasExtremas = 0;
        $totalRentaImponibleActualizada = 0;
        $actividad = '';
    
        if($liquidaciones->count()){
            foreach($liquidaciones as $liquidacion){
                $lista[$liquidacion['mes']] = $liquidacion;
            }
        }
        
        for($i=0; $i<12; $i++){     
            $mes = '0';
            $index = $meses[$i];
            $miLiquidacion = isset($lista[$index]);
            $sueldo = 0;
            
            $cotizacionPrevisional = 0;
            $rentaImponible = 0;
            $impuestoUnico = 0;
            $mayorRetencion = 0;
            $rentaTotal = 0;
            $rentaNoGravada = 0;
            $rebaja = 0;
            $factor = 0;
            $rentaAfecta = 0;
            $impuestoUnicoRetenido = 0;
            $mayorRetencionImpuesto = 0;
            $rentaTotalExenta = 0;
            $rentaTotalNoGravada = 0;
            $rebajaZonasExtremas = 0;    
            $rentaImponibleActualizada = 0;    
            
            if($miLiquidacion){
                $mes = '1';
                $cotizacion = ($lista[$index]['imponibles'] - $lista[$index]['base_impuesto_unico']);
                $sueldo = $lista[$index]['imponibles'];
                $cotizacionPrevisional = $cotizacion;
                $rentaImponible = $lista[$index]['base_impuesto_unico'];
                $impuestoUnico = $lista[$index]['impuesto_determinado'];
                $mayorRetencion = 0;
                $rentaTotal = 0;
                $rentaNoGravada = $lista[$index]['no_imponibles'];
                $rebaja = $lista[$index]['rebaja_zona'];
                $factor = $factores[$i]['factor'];
                $rentaAfecta = round($factor * $lista[$index]['base_impuesto_unico']);
                $impuestoUnicoRetenido = round($factor * $lista[$index]['impuesto_determinado']);
                $mayorRetencionImpuesto = 0;
                $rentaTotalExenta = 0;
                $rentaTotalNoGravada = round($factor * $lista[$index]['no_imponibles']);
                $rebajaZonasExtremas = round($factor * $lista[$index]['rebaja_zona']);
                $rentaImponibleActualizada = round($factor * $lista[$index]['imponibles']);
            }
            
            $datos[$i] = array(                
                'sueldo' => $sueldo,
                'cotizacionPrevisional' => $cotizacionPrevisional,
                'rentaImponible' => $rentaImponible,
                'impuestoUnico' => $impuestoUnico,
                'mayorRetencion' => $mayorRetencion,
                'rentaTotal' => $rentaTotal,
                'rentaNoGravada' => $rentaNoGravada,
                'rebaja' => $rebaja,
                'factor' => $factor,
                'rentaAfecta' => $rentaAfecta,
                'impuestoUnicoRetenido' => $impuestoUnicoRetenido,
                'mayorRetencionImpuesto' => $mayorRetencionImpuesto,
                'rentaTotalExenta' => $rentaTotalExenta,
                'rentaTotalNoGravada' => $rentaTotalNoGravada,
                'rebajaZonasExtremas' => $rebajaZonasExtremas,
                'rentaImponibleActualizada' => $rentaImponibleActualizada,
                'liquidacion' => $lista,
                'meses' => $meses,
                'index' => $meses[$i]
            );            
            
            $totalSueldo += $sueldo;
            $totalCotizacionPrevisional += $cotizacionPrevisional;
            $totalRentaImponible += $rentaImponible;
            $totalImpuestoUnico += $impuestoUnico;
            $totalMayorRetencion += $mayorRetencion;
            $totalRentaTotal += $rentaTotal;
            $totalRentaNoGravada += $rentaNoGravada;
            $totalRebaja += $rebaja;
            $totalFactor += $factor;
            $totalRentaAfecta += $rentaAfecta;
            $totalImpuestoUnicoRetenido += $impuestoUnicoRetenido;
            $totalMayorRetencionImpuesto += $mayorRetencionImpuesto;
            $totalRentaTotalExenta += $rentaTotalExenta;
            $totalRentaTotalNoGravada += $rentaTotalNoGravada;
            $totalRebajaZonasExtremas += $rebajaZonasExtremas;     
            $totalRentaImponibleActualizada += $rentaImponibleActualizada;     
            
            $actividad = $actividad . $mes;
        }
        
        $datos[12] = array(
            'sueldo' => $totalSueldo,
            'cotizacionPrevisional' => $totalCotizacionPrevisional,
            'rentaImponible' => $totalRentaImponible,
            'impuestoUnico' => $totalImpuestoUnico,
            'mayorRetencion' => $totalMayorRetencion,
            'rentaTotal' => $totalRentaTotal,
            'rentaNoGravada' => $totalRentaNoGravada,
            'rebaja' => $totalRebaja,
            'factor' => $totalFactor,
            'rentaAfecta' => $totalRentaAfecta,
            'impuestoUnicoRetenido' => $totalImpuestoUnicoRetenido,
            'mayorRetencionImpuesto' => $totalMayorRetencionImpuesto,
            'rentaTotalExenta' => $totalRentaTotalExenta,
            'rentaTotalNoGravada' => $totalRentaTotalNoGravada,
            'rebajaZonasExtremas' => $totalRebajaZonasExtremas,
            'rentaImponibleActualizada' => $totalRentaImponibleActualizada,
            'actividad' => $actividad
        );
        
        return $datos;
    }
    
    static function isGratificacionAnual()
    {
        $empresa = \Session::get('empresa');
        if($empresa->gratificacion=='e'){
            if($empresa->tipo_gratificacion=='a'){
                return true;
            }
        }else{
            $trabajadores = FichaTrabajador::where('gratificacion', 'a')->get();
            if($trabajadores->count()){
                return true;
            }
        }
        
        return false;
    }      
    
    public function vigenciaContrato()
    {
        $inicio = Funciones::obtenerFechaTexto($this->fecha_reconocimiento);
        if($this->tipo_contrato_id==1){
            $vigencia = "desde el día " . $inicio;
        }else{            
            $fin = Funciones::obtenerFechaTexto($this->fecha_vencimiento); 
            $vigencia = "desde el día " . $inicio . " hasta el día " . $fin;
        }
        
        return $vigencia;
    }
    
    public function tramoAsignacionFamiliar()
    {
        $tramo = $this->tramo_id ? $this->tramo_id : 'd';
        
        return strtoupper($tramo);
    }
    
    static function calcularTramo($monto)
    {
        $miTramo = null;
        $mes = \Session::get('mesActivo')->mes;
        $tramos = AsignacionFamiliar::where('mes', $mes)->orderBy('tramo')->get();
        foreach($tramos as $tramo){
            if($monto > $tramo['renta_menor'] && $monto <= $tramo['renta_mayor']){
                $miTramo = $tramo['tramo'];
                break;
            }
        }
        return $miTramo;
    }
                    
    public function nombreCompleto()
    {
        $nombres = $this->nombres;
        $apellidos = $this->apellidos;
        $empresa = \Session::get('empresa');
        $apellidoNombre = Empresa::variableConfiguracion('apellido_nombre');
        
        if($apellidoNombre){
            if($apellidos && $nombres){
                $nombreCompleto = $apellidos . ", " . $nombres;            
            }else{
                $nombreCompleto = $apellidos . " " . $nombres;                            
            }
        }else{
            $nombreCompleto = $nombres . " " . $apellidos;            
        }
        
        return $nombreCompleto;
    }
    
    public function apellidoPaterno()
    {
        $apellidos = $this->apellidos;                
        $ape = explode(" ", $apellidos);

        return $ape[0];
    }
    
    public function apellidoMaterno()
    {
        $apellidos = $this->apellidos;                
        $ape = explode(" ", $apellidos);
        
        if(isset($ape[1])){
            return $ape[1];
        }else{
            return "";
        }
    }
    
    public function codigoNacionalidad()
    {
        $nacionalidad = $this->nacionalidad->id;                
        
        if($nacionalidad==3){
            $codigo = 0;
        }else if($nacionalidad==4){
            $codigo = 1;            
        }else{
            $codigo = "";            
        }
        
        return $codigo;
    }        
    
    public function miSeccion()
    {
        $seccion = $this->seccion;
        if($seccion){
            if($seccion->codigo){
                return $seccion->codigo;
            }else{
                return $seccion->nombre;
            }
        }
        
        return "";
    }
    
    public function miTienda()
    {
        $tienda = $this->tienda;
        if($tienda){
            if($tienda->codigo){
                return $tienda->codigo;
            }else{
                return $tienda->nombre;
            }
        }
        
        return null;
    }
    
    public function hasta($fichas)
    {
        $fichas = array_reverse($fichas->toArray());
        $ultimaFicha = null;
        
        if($this->id==$fichas[0]['id']){
            return null;
        }
        
        foreach($fichas as $ficha){
            if($this->id == $ficha['id']){
                $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($ultimaFicha['fecha'])));
                return $fecha;
            }
            $ultimaFicha = $ficha;
        }
    }
    
    public function periodo($index, $desde, $hasta)
    {
        if($desde==$hasta){
            return ($index + 1) . '° - ' . Funciones::obtenerMesAnioTextoAbr($desde);
        }
        if($hasta){
            $hasta = Funciones::obtenerMesAnioTextoAbr($hasta);
        }else{
            $hasta = '∞';
        }
        return ($index + 1) . '° - ' . Funciones::obtenerMesAnioTextoAbr($desde) . ' - ' . $hasta;
    }
    
    public function tipoTrabajador($recaudador=null)
    {        
        if(!$recaudador){
            $recaudador = 1;
        }
        $tipo = $this->tipo_id;
        $codigo = Glosa::find($tipo)->codigo($recaudador)['codigo'];
        
        return $codigo;
    }                
    
    public function mesesAntiguedad()
    {
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $fechaIngreso = new DateTime($this->fecha_reconocimiento);
        $fecha = new DateTime($finMes);
        $diff = $fechaIngreso->diff($fecha);
        $meses = (($diff->y * 12) + $diff->m);
        
        return $meses;
    }
    
    public function domicilio()
    {
        $direccion = $this->direccion;
        $comuna = $this->comuna->comuna;
        $provincia = $this->comuna->provincia->provincia;
        $domicilio = $direccion . ', comuna de ' . $comuna . ', de la ciudad de ' . $provincia;
        
        return $domicilio;
    }

    static function errores($datos){
         
        $rules = array(

            
        );

        $message = array(
            'fichaTrabajador.required' => 'Obligatorio!'
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