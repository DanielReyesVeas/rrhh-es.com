<?php

class Liquidacion extends Eloquent {
    
    protected $table = 'liquidaciones';
    
    public function detalles(){
        return $this->hasMany('DetalleLiquidacion','liquidacion_id');
    }
    
    public function detalleAfiliadoVoluntario(){
        return $this->hasMany('DetalleAfiliadoVoluntario','liquidacion_id');
    }
    
    public function detalleAfp(){
        return $this->hasOne('DetalleAfp','liquidacion_id');
    }
    
    public function detalleApvc(){
        return $this->hasMany('DetalleApvc','liquidacion_id');
    }
    
    public function detalleApvi(){
        return $this->hasMany('DetalleApvi','liquidacion_id');
    }
    
    public function detalleCaja(){
        return $this->hasOne('DetalleCaja','liquidacion_id');
    }
    
    public function detalleIpsIslFonasa(){
        return $this->hasOne('DetalleIpsIslFonasa','liquidacion_id');
    }
    
    public function detalleMutual(){
        return $this->hasOne('DetalleMutual','liquidacion_id');
    }
    
    public function detalleSalud(){
        return $this->hasOne('DetalleSalud','liquidacion_id');
    }
    
    public function detalleSeguroCesantia(){
        return $this->hasOne('DetalleSeguroCesantia','liquidacion_id');
    }
    
    public function detallePagadorSubsidio(){
        return $this->hasOne('DetallePagadorSubsidio','liquidacion_id');
    }
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
  
    public function documento(){
        return $this->belongsTo('Documento', 'documento_id');
    }        
	
    public function centroCosto(){
        return $this->belongsTo('CentroCosto', 'centro_costo_id');
    }  
    
    public function nombreCompleto()
    {
        $nombres = $this->trabajador_nombres;
        $apellidos = $this->trabajador_apellidos;
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
    
    public function generarCuerpo()
    {
        $mes = \Session::get('mesActivo');
        $semanaCorrida = $this->semanaCorrida();
        $tasaAfp = $this->tasaAfp();
        $detalles = $this->misDetalles();
        $empleado = $this->trabajador->ficha();
        
        $miLiquidacion = array(
            'mes' => $mes->nombre . ' del ' . $mes->anio,
            'empresa' => array(
                'razon_social' => $this->empresa_razon_social
            ),
            'rutEmpresa' => Funciones::formatear_rut($this->empresa_rut),
            'rutFormato' => Funciones::formatear_rut($this->trabajador_rut),
            'nombreCompleto' => $this->trabajador_nombres . ' ' . $this->trabajador_apellidos,
            'cargo' => array(
                'nombre' => $this->trabajador_cargo
            ),
            'seccion' => array(
                'nombre' => $this->trabajador_seccion
            ),
            'fechaIngreso' => $this->trabajador_fecha_ingreso,
            'tipoContrato' => array(
                'nombre' => $this->tipo_contrato
            ),
            'sueldoBase' => $this->sueldo_base,
            'colacion' => array(
                'monto' => $this->colacion
            ),
            'movilizacion' => array(
                'monto' => $this->movilizacion
            ),
            'viatico' => array(
                'monto' => $this->viatico
            ),
            'afp' => array(
                'nombre' => $this->detalleAfp ? $this->detalleAfp->afp->glosa : ""
            ),
            'prevision' => array(
                'id' => $this->prevision_id,
            ),
            'seguroDesempleo' => $this->seguro_cesantia ? true : false,
            'isapre' => array(
                'id' => $this->detalleSalud ? $this->detalleSalud->salud_id : 240,
                'nombre' =>  $this->detalleSalud ? $this->detalleSalud->isapre->glosa : ""
            ),
            'cotizacionIsapre' => 0,
            'diasTrabajados' => $this->dias_trabajados,
            'sueldo' => $this->sueldo,
            'gratificacion' => $this->gratificacion,
            'horasExtra' => array(
                'cantidad' => $this->horas_extra,
                'total' => $this->total_horas_extra
            ),
            'imponibles' => $this->imponibles,
            'totalHaberes' => $this->total_haberes,
            'cargasFamiliares' => array(
                'monto' => $this->total_cargas
            ),
            'noImponibles' => $this->no_imponibles,
            'semanaCorrida' => $semanaCorrida,
            'isSemanaCorrida' => $semanaCorrida ? true : false,
            'rentaImponible' => $this->renta_imponible,
            'tasaAfp' => $tasaAfp['tasaAfp'],
            'tasaSis' => $tasaAfp['tasaSis'],
            'totalAfp' => $this->detalleAfp ? $this->detalleAfp->cotizacion : 0,
            'totalSalud' => array(
                'total' => $this->detalleSalud ? ($this->detalleSalud->cotizacion_obligatoria + $this->detalleSalud->cotizacion_adicional) : 0,
                'obligatorio' => $this->detalleSalud ? $this->detalleSalud->cotizacion_obligatoria : 0,
                'adicional' => $this->detalleSalud ? $this->detalleSalud->cotizacion_adicional : 0
            ),
            'totalSeguroCesantia' => array(
                'total' => $this->detalleSeguroCesantia ? $this->detalleSeguroCesantia->aporte_trabajador : 0
            ),
            'totalDescuentosPrevisionales' => $this->total_descuentos_previsionales,
            'totalDescuentos' => $this->total_descuentos,
            'baseImpuestoUnico' => $this->base_impuesto_unico,
            'tramoImpuesto' => $this->tramo_impuesto,
            'impuestoDeterminado' => $this->impuesto_determinado,
            'totalOtrosDescuentos' => $this->total_otros_descuentos,
            'otrosImponibles' => $this->otros_imponibles,
            'apvs' => $this->apvs(),
            'haberesImponibles' => $detalles['imponibles'],
            'haberesNoImponibles' => $detalles['noImponibles'],
            'descuentos' => $detalles['descuentos'],
            'prestamos' => $detalles['prestamos'],
            'sueldoLiquidoPalabras' => strtoupper(Funciones::convertirPalabras($this->sueldo_liquido)),
            'sueldoLiquido' => $this->sueldo_liquido,
            'banco' => $empleado->banco ? $empleado->banco->nombre : "",
            'cuenta' => $empleado->numero_cuenta ? $empleado->numero_cuenta : "",
            'sis' => $tasaAfp['sis'],
            'observacion' => $this->observacion,
            'nombreDocumento' => $this->documento->nombre,
            'aliasDocumento' => $this->documento->alias,
            'a' => $detalles
        );    
        
        $view = View::make('pdf.liquidacion', [
            'liquidacion' => $miLiquidacion
        ]);
        $html = $view->render();
        
        return $html;
    }
    
    public function misDetalles()
    {
        $detalles = $this->detalles;
        $imponibles = array();
        $noImponibles = array();
        $descuentos = array();
        $prestamos = array();
        
        if($detalles->count()){
            foreach($detalles as $detalle){
                if($detalle->tipo_id==1){
                    if($detalle->tipo=='imponible'){
                        $imponibles[] = array(
                            'tipo' => array(
                                'id' => $detalle->detalle_id,
                                'nombre' => $detalle->nombre                    
                            ),
                            'montoPesos' => $detalle->valor
                        );
                    }else if($detalle->tipo=='no imponible'){
                        $noImponibles[] = array(
                            'tipo' => array(
                                'id' => $detalle->detalle_id,
                                'nombre' => $detalle->nombre                    
                            ),
                            'montoPesos' => $detalle->valor
                        );
                    }
                }else if($detalle->tipo_id==2){
                    $descuentos[] = array(
                        'tipo' => array(
                            'id' => $detalle->detalle_id,
                            'nombre' => $detalle->nombre                    
                        ),
                        'montoPesos' => $detalle->valor
                    );
                }else if($detalle->tipo_id==4){
                    $prestamos[] = array(
                        'glosa' => $detalle->nombre,                    
                        'montoCuotaPagar' => $detalle->valor
                    );
                }
            }
        }
        
        $datos = array(
            'imponibles' => $imponibles,
            'noImponibles' => $noImponibles,
            'descuentos' => $descuentos,
            'prestamos' => $prestamos
        );
        
        return $datos;
    }
    
    public function apvs()
    {
        $detalles = $this->detalleApvi;
        $apvs = array();
        
        if($detalles->count()){
            foreach($detalles as $detalle){
                $apvs[] = array(
                    'regimen' => strtoupper($detalle->regimen),
                    'afp' => array(
                        'id' => $detalle->afp_id,
                        'nombre' => $detalle->afp->glosa
                    ),
                    'montoPesos' => $detalle->monto
                );
            }
        }
        
        return $apvs;
    }
    
    public function tasaAfp()
    {
        $tasaAfp = $this->detalleAfp ? $this->detalleAfp->porcentaje_cotizacion : 0;
        $tasaSis = $this->detalleAfp ? $this->detalleAfp->porcentaje_sis : 0;
        $sis = 0;
        
        if(!$tasaAfp && !$tasaSis){
            $tasa = $this->trabajador->tasaAfp();
            $tasaAfp = $tasa['tasaTrabajador'];            
            $tasaAfp = $tasa['tasaSis'];            
        }
        
        $isSis = $this->detalleAfp ? $this->detalleAfp->paga_sis : "";
        if($isSis=='empleado'){
            $sis = $this->detalleAfp->sis;
        }
        
        $datos = array(
            'tasaAfp' => $tasaAfp,
            'tasaSis' => $tasaSis,
            'sis' => $sis
        );
        
        return $datos;
    }
    
    public function semanaCorrida()
    {
        $detalles = $this->detalles;
        $total = 0;
        
        if($detalles->count()){
            foreach($detalles as $detalle){
                if($detalle->nombre=='Semana Corrida'){
                    $total = ($total + $detalle->valor);
                }
            }
        }
        
        return $total;
    }
    
    public function cotizacionSalud()
    {
        if($this->cotizacion_salud=='$'){
            return 1;
        }else if($this->cotizacion_salud=='UF'){
            return 2;
        }else{
            return 0;
        }
    }
    
    public function cotizacionFonasa()
    {        
        $idSalud = $this->id_salud;
        $montoFonasa = 0;
        
        if($idSalud==246){
            $montoFonasa = $this->total_salud;
        }
        
        return $montoFonasa;
    }
    
    public function regimenPrevisional()
    {
        $prevision = $this->prevision_id;                
        
        if($prevision==8){
            $codigo = 'AFP';                        
        }else if($prevision==9){
            $codigo = 'INP';            
        }else{
            $codigo = 'SIP';
        }
        
        return $codigo;
    }
    
    public function sisAnterior()
    {
        $trabajador = $this->trabajador;
        $mesActual = $this->mes;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mesActual)));
        $liquidacionAnterior = Liquidacion::where('trabajador_id', $trabajador->id)->where('mes', $mesAnterior)->first();
        if($liquidacionAnterior){
            if($liquidacionAnterior->detalleAfp){
                return $liquidacionAnterior->miDetalleAfp()['sis'];
            }
        }
        
        return 0;
    }
    
    public function sisDetalleAfp()
    {
        if($this->dias_trabajados==0 && $this->movimiento_personal==3){
            return $this->sisAnterior();
        }
        
        return $this->detalleAfp->sis;
    }
    
    public function rentaNoGravada()
    {
        $detalles = $this->detalles;
        $total = 0;
        
        if($detalles->count()){
            foreach($detalles as $detalle){
                if($detalle->tipo_id==1){
                    $haber = TipoHaber::find($detalle->detalle_id);
                    if($haber){
                        if(!$haber->tributable){
                            $total += $detalle->valor;
                        }
                    }
                }
            }
        }
        
        return $total;
    }
    
    public function rebajaZona()
    {
        $rebaja = 0;
        $trabajador = $this->trabajador;
        if($trabajador){
            $rebaja = $trabajador->zonaImpuestoUnico();
        }
        
        return $rebaja;
    }
    
    public function movimientoPersonal()
    {
        $desde = $this->fecha_desde;
        $hasta = $this->fecha_hasta;
        
        $datos = array(
            'desde' => $desde,
            'hasta' => $hasta
        );
        
        return $datos;
    }
    
    public function miDetalleAfp()
    {
        $detalleAfp = $this->detalleAfp;
        $codigoAfp = 0;
        $nombreAfp = '';
        $rentaImponible = 0;
        $cotizacionAfp = 0;
        $sis = 0;
        $cuentaAhorroVoluntario = 0;
        $rentaSustitutiva = 0;
        $tasaSustitutiva = 0;
        $aporteSustitutiva = 0;
        $numeroPeriodos = 0;
        $periodoDesdeSustit = 0;
        $periodoHastaSustit = 0;
        $puestoTrabajoPesado = 0;
        $porcentajeTrabajoPesado = 0;
        $cotizacionTrabajoPesado = 0;
        
        if($detalleAfp){
            $codigoAfp = $detalleAfp->afp_id ? $detalleAfp->codigoAfp(1) : '';
            $nombreAfp = $detalleAfp->afp_id ? $detalleAfp->nombreAfp(1) : '';
            $rentaImponible = $detalleAfp->renta_imponible;
            $cotizacionAfp = $detalleAfp->cotizacion;
            //$sis = $this->sisDetalleAfp();
            $sis = $detalleAfp->sis;
            $cuentaAhorroVoluntario = $detalleAfp->cuenta_ahorro_voluntario;
            $rentaSustitutiva = $detalleAfp->renta_sustitutiva;
            $tasaSustitutiva = $detalleAfp->tasa_sustitutiva;
            $aporteSustitutiva = $detalleAfp->aporte_sustitutiva;
            $numeroPeriodos = $detalleAfp->numero_periodos;
            $periodoDesdeSustit = $detalleAfp->periodo_desde;
            $periodoHastaSustit = $detalleAfp->periodo_hasta;
            $puestoTrabajoPesado = $detalleAfp->puesto_trabajo_pesado ? $detalleAfp->puesto_trabajo_pesado : '';
            $porcentajeTrabajoPesado = $detalleAfp->porcentaje_trabajo_pesado;
            $cotizacionTrabajoPesado = $detalleAfp->cotizacion_trabajo_pesado;
        }
        
        $datos = array(
            'codigoAfp' => $codigoAfp,
            'nombreAfp' => $nombreAfp,
            'rentaImponible' => $rentaImponible,
            'cotizacionAfp' => $cotizacionAfp,
            'sis' => $sis,
            'cuentaAhorroVoluntario' => $cuentaAhorroVoluntario,
            'rentaSustitutiva' => $rentaSustitutiva,
            'tasaSustitutiva' => $tasaSustitutiva,
            'aporteSustitutiva' => $aporteSustitutiva,
            'numeroPeriodos' => $numeroPeriodos,
            'periodoDesdeSustit' => $periodoDesdeSustit,
            'periodoHastaSustit' => $periodoHastaSustit,
            'puestoTrabajoPesado' => $puestoTrabajoPesado,
            'porcentajeTrabajoPesado' => $porcentajeTrabajoPesado,
            'cotizacionTrabajoPesado' => $cotizacionTrabajoPesado
        );
        
        return $datos;
    }
    
    public function miDetalleSalud()
    {
        $detalleSalud = $this->detalleSalud;
        $codigoSalud = 0;
        $nombreSalud = '';
        $numeroFun = 0;
        $rentaImponible = 0;
        $moneda = 0;
        $cotizacionPactada = 0;
        $cotizacionObligatoria = 0;
        $cotizacionAdicional = 0;
        $ges = 0;
        
        if($detalleSalud){
            $codigoSalud = $detalleSalud->codigoSalud(1);
            $nombreSalud = $detalleSalud->salud_id ? $detalleSalud->nombreSalud(1) : '';
            $rentaImponible = $detalleSalud->renta_imponible;
            if($codigoSalud!=7){
                $numeroFun = $detalleSalud->numero_fun;
                if(strtoupper($detalleSalud->moneda)=='UF'){
                    $mon = 2;
                }else{
                    $mon = 1;
                }
                $moneda = $mon;
                $cotizacionPactada = $detalleSalud->cotizacion_pactada;
                $cotizacionObligatoria = $detalleSalud->cotizacion_obligatoria;
                $cotizacionAdicional = $detalleSalud->cotizacion_adicional;
                $ges = $detalleSalud->ges;  
                if($moneda==1){
                    $cotizacionPactada = round($cotizacionPactada);
                }
            }
        }
        
        $datos = array(
            'codigoSalud' => $codigoSalud,
            'nombreSalud' => $nombreSalud,
            'numeroFun' => $numeroFun,
            'rentaImponible' => $rentaImponible,
            'moneda' => $moneda,
            'cotizacionPactada' => $cotizacionPactada,
            'cotizacionObligatoria' => $cotizacionObligatoria,
            'cotizacionAdicional' => $cotizacionAdicional,
            'ges' => $ges
        );
        
        return $datos;
    }
    
    public function miDetalleCaja()
    {
        $detalleCaja = $this->detalleCaja;
        $codigoCaja = '00';
        $rentaImponible = 0;
        $creditosPersonales = 0;
        $descuentoDental = 0;
        $descuentosLeasing = 0;
        $descuentosSeguro = 0;
        $otrosDescuentos = 0;
        $cotizacion = 0;
        $descuentoCargas = 0;
        $otrosDescuentos1 = 0;
        $otrosDescuentos2 = 0;
        $bonosGobierno = 0;
        $codigoSucursal = '';
        
        if($detalleCaja){
            $codigoCaja = $detalleCaja->codigoCaja(1);
            $rentaImponible = $detalleCaja->renta_imponible;
            $creditosPersonales = $detalleCaja->creditos_personales;
            $descuentoDental = $detalleCaja->descuento_dental;
            $descuentosLeasing = $detalleCaja->descuentos_leasing;
            $descuentosSeguro = $detalleCaja->descuentos_seguro;
            $otrosDescuentos = $detalleCaja->otros_descuentos;
            $cotizacion = $detalleCaja->cotizacion;            
            $otrosDescuentos1 = $detalleCaja->otros_descuentos_1;            
            $otrosDescuentos2 = $detalleCaja->otros_descuentos_2;            
            $bonosGobierno = $detalleCaja->bonos_gobierno;            
            $codigoSucursal = $detalleCaja->codigo_sucursal;            
            if($this->total_cargas>0){
                $descuentoCargas = $this->total_cargas;            
            }
        }
        
        $datos = array(
            'codigoCaja' => $codigoCaja,
            'rentaImponible' => $rentaImponible,
            'creditosPersonales' => $creditosPersonales,
            'descuentoDental' => $descuentoDental,
            'descuentosLeasing' => $descuentosLeasing,
            'descuentosSeguro' => $descuentosSeguro,
            'otrosDescuentos' => $otrosDescuentos,
            'cotizacion' => $cotizacion,
            'descuentoCargas' => $descuentoCargas,
            'otrosDescuentos1' => $otrosDescuentos1,
            'otrosDescuentos2' => $otrosDescuentos2,
            'bonosGobierno' => $bonosGobierno,
            'codigoSucursal' => $codigoSucursal
        );
        
        return $datos;
    }
    
    public function miDetalleMutual()
    {
        $detalleMutual = $this->detalleMutual;
        $codigoMutual = '00';
        $rentaImponible = 0;
        $cotizacionAccidentes = 0;
        $codigoSucursal = '';
        
        if($detalleMutual){
            $codigoMutual = $detalleMutual->codigoMutual(1);
            $rentaImponible = $detalleMutual->renta_imponible;
            $cotizacionAccidentes = $detalleMutual->cotizacion_accidentes;
            $codigoSucursal = $detalleMutual->codigo_sucursal;         
        }
        
        $datos = array(
            'codigoMutual' => $codigoMutual,
            'rentaImponible' => $rentaImponible,
            'cotizacionAccidentes' => $cotizacionAccidentes,
            'codigoSucursal' => $codigoSucursal
        );
        
        return $datos;
    }
    
    public function miDetalleSeguroCesantia()
    {
        $detalleSeguroCesantia = $this->detalleSeguroCesantia;
        $rentaImponible = 0;
        $aporteTrabajador = 0;
        $aporteEmpleador = 0;
        $codigo = 0;
        
        if($detalleSeguroCesantia){
            $codigo = $detalleSeguroCesantia->codigoAfp(1);
            $rentaImponible = $detalleSeguroCesantia->renta_imponible;
            $aporteTrabajador = $detalleSeguroCesantia->aporte_trabajador;
            $aporteEmpleador = $detalleSeguroCesantia->aporte_empleador;         
        }
        
        $datos = array(
            'rentaImponible' => $rentaImponible,
            'aporteTrabajador' => $aporteTrabajador,
            'aporteEmpleador' => $aporteEmpleador,
            'codigo' => $codigo,
        );
        
        return $datos;
    }
    
    public function miDetallePagadorSubsidio()
    {
        $detallePagadorSubsidio = $this->detallePagadorSubsidio;
        $rut = '';
        $digito = '';
        
        if($detallePagadorSubsidio){
            $rut = $detallePagadorSubsidio->rut;
            $digito = $detallePagadorSubsidio->digito;
        }
        
        $datos = array(
            'rut' => $rut,
            'digito' => $digito
        );
        
        return $datos;
    }
    
    public function miDetalleIpsIslFonasa()
    {
        $detalleIpsIslFonasa = $this->detalleIpsIslFonasa;
        $empresa = \Session::get('empresa');
        $codigoExCaja = '';
        $tasa = 0;
        $rentaImponible = 0;
        $cotizacionObligatoria = 0;
        $rentaImponibleDesahucio = 0;
        $codigoExCajaDesahucio = 0;
        $tasaDesahucio = 0;
        $cotizacionDesahucio = 0;
        $cotizacionFonasa = 0;
        $cotizacionIsl = 0;
        $bonificacion = 0;
        $descuentoCargasIsl = 0;
        $bonosGobierno = 0;
        
        if($detalleIpsIslFonasa){
            $codigoExCaja = $detalleIpsIslFonasa->ex_caja_id ? $detalleIpsIslFonasa->codigoExCaja(1) : '';
            $tasa = $detalleIpsIslFonasa->tasa_cotizacion ? str_replace(".", ",", $detalleIpsIslFonasa->tasa_cotizacion) : "";
            $rentaImponible = $detalleIpsIslFonasa->renta_imponible;
            $cotizacionObligatoria = $detalleIpsIslFonasa->cotizacion_obligatoria;
            $rentaImponibleDesahucio = $detalleIpsIslFonasa->renta_imponible_desahucio;
            $codigoExCajaDesahucio = $detalleIpsIslFonasa->ex_caja_desahucio_id;
            $tasaDesahucio = $detalleIpsIslFonasa->tasa_desahucio;
            $cotizacionDesahucio = $detalleIpsIslFonasa->cotizacion_desahucio;
            $cotizacionFonasa = $detalleIpsIslFonasa->cotizacion_fonasa;
            $cotizacionIsl = $detalleIpsIslFonasa->cotizacion_isl;
            $bonificacion = $detalleIpsIslFonasa->bonificacion;
            $bonosGobierno = $detalleIpsIslFonasa->bonos_gobierno;
            if($this->total_cargas>0 && $empresa->caja_id==257){
                $descuentoCargasIsl = $this->total_cargas;            
            }
        }
        
        $datos = array(
            'codigoExCaja' => $codigoExCaja,
            'tasa' => $tasa,
            'rentaImponible' => $rentaImponible,
            'cotizacionObligatoria' => $cotizacionObligatoria,
            'rentaImponibleDesahucio' => $rentaImponibleDesahucio,
            'codigoExCajaDesahucio' => $codigoExCajaDesahucio,
            'tasaDesahucio' => $tasaDesahucio,
            'cotizacionDesahucio' => $cotizacionDesahucio,
            'cotizacionFonasa' => $cotizacionFonasa,
            'cotizacionIsl' => $cotizacionIsl,
            'bonificacion' => $bonificacion,
            'descuentoCargasIsl' => $descuentoCargasIsl,
            'bonosGobierno' => $bonosGobierno
        );
        
        return $datos;
    }
    
    public function miDetalleApvi(&$lineaAdicional)
    {
        $detalle = $this->detalleApvi;
        $codigo = 0;
        $nombreAPVI = '';
        $numeroContrato = '';
        $formaPago = 0;
        $cotizacion = 0;
        $cotizacionDepositosConvenidos = 0;

        if(count($detalle)){
            $codigo = $detalle[0]->codigoAfp(1);
            $numeroContrato = $detalle[0]->numero_contrato ? $detalle->numero_contrato : '';
            if($detalle[0]->forma_pago_id==102){
                $formaPago = 1;
            }else if($detalle[0]->forma_pago_id==103){
                $formaPago = 2;                
            }
            $formaPago = $formaPago;
            $cotizacion = round($detalle[0]->monto);
            $cotizacionDepositosConvenidos = $detalle[0]->cotizacion_depositos_convenidos;
            $nombreAPVI = $detalle[0]->afp_id ? $detalle[0]->nombreAfp(1) : '';
            if(count($detalle)>1){
                foreach($detalle as $index => $det){
                    if($index>0){
                        if($det->forma_pago_id==102){
                            $formaPago = 1;
                        }else if($det->forma_pago_id==103){
                            $formaPago = 2;                
                        }
                        $lineaAdicional[] = array(
                            'codigoAPVI' => $det->codigoAfp(1),
                            'nombreAPVI' => $det->afp_id ? $det->nombreAfp(1) : '',
                            'numeroContratoAPVI' => $det->numero_contrato ? $detalle->numero_contrato : '',
                            'formaPagoAPVI' => $formaPago,
                            'cotizacionAPVI' => round($det->monto),
                            'cotizacionDepositosConvenidosAPVI' => $det->cotizacion_depositos_convenidos,
                            'codigoAPVC' => 0,
                            'numeroContratoAPVC' => '',
                            'nombreAPVC' => '',
                            'formaPagoAPVC' => 0,
                            'cotizacionTrabajadorAPVC' => 0,
                            'cotizacionEmpleadorAPVC' => 0
                        );
                    }
                } 
            }
        }
        
        $datos = array(
            'codigoAPVI' => $codigo,
            'nombreAPVI' => $nombreAPVI,
            'numeroContratoAPVI' => $numeroContrato,
            'formaPagoAPVI' => $formaPago,
            'cotizacionAPVI' => $cotizacion,
            'cotizacionDepositosConvenidosAPVI' => $cotizacionDepositosConvenidos,
            'codigoAPVC' => 0,
            'numeroContratoAPVC' => '',
            'nombreAPVC' => '',
            'formaPagoAPVC' => 0,
            'cotizacionTrabajadorAPVC' => 0,
            'cotizacionEmpleadorAPVC' => 0
        );
        
        return $datos;
    }
    
    public function diasTotales()
    {
        if($this->movimiento_personal==3 || $this->movimiento_personal==4 || $this->movimiento_personal == 11){
            $desde = $this->fecha_desde;
            $hasta = $this->fecha_hasta;
            $dias = ((($hasta - $desde) + 1) + $this->dias_trabajados);
        }else{
            $dias = $this->dias_trabajados;
        }
        
        return $dias;
    }
    
    public function miDetalleApvc(&$lineaAdicional)
    {
        $detalle = $this->detalleApvc;
        $codigo = 0;
        $nombreAPVC = '';
        $numeroContrato = '';
        $formaPago = 0;
        $cotizacionTrabajador = 0;
        $cotizacionEmpleador = 0;

        if(count($detalle)){
            $codigo = $detalle[0]->codigoAfp(1);
            $numeroContrato = $detalle[0]->numero_contrato;
            if($detalle[0]->forma_pago_id==102){
                $formaPago = 1;
            }else if($detalle[0]->forma_pago_id==103){
                $formaPago = 2;                
            }
            $cotizacionTrabajador = $detalle[0]->monto;
            $cotizacionEmpleador = $detalle[0]->cotizacion_empleador;
            $nombreAPVC = $detalle[0]->afp_id ? $detalle[0]->nombreAfp(1) : '';
            if(count($detalle)>1){
                foreach($detalle as $index => $det){
                    if($index>0){
                        if($det->forma_pago_id==102){
                            $formaPago = 1;
                        }else if($det->forma_pago_id==103){
                            $formaPago = 2;                
                        }
                        $lineaAdicional[] = array(
                            'codigoAPVC' => $det->codigoAfp(1),
                            'numeroContratoAPVC' => $det->numero_contrato ? $detalle->numero_contrato : '',
                            'nombreAPVC' => $det->afp_id ? $det->nombreAfp(1) : '',
                            'formaPagoAPVC' => $formaPago,
                            'cotizacionTrabajadorAPVC' => round($det->monto),
                            'cotizacionEmpleadorAPVC' => $det->cotizacion_empleador,
                            'codigoAPVI' => 0,
                            'nombreAPVI' => '',
                            'numeroContratoAPVI' => '',
                            'formaPagoAPVI' => 0,
                            'cotizacionAPVI' => 0,
                            'cotizacionDepositosConvenidosAPVI' => 0
                        );
                    }
                }
            }
        }
        
        $datos = array(
            'codigoAPVC' => $codigo,
            'numeroContratoAPVC' => $numeroContrato,
            'nombreAPVC' => $nombreAPVC,
            'formaPagoAPVC' => $formaPago,
            'cotizacionTrabajadorAPVC' => $cotizacionTrabajador,
            'cotizacionEmpleadorAPVC' => $cotizacionEmpleador,
            'codigoAPVI' => 0,
            'nombreAPVI' => '',
            'numeroContratoAPVI' => '',
            'formaPagoAPVI' => 0,
            'cotizacionAPVI' => 0,
            'cotizacionDepositosConvenidosAPVI' => 0
        );
        
        return $datos;
    }
    
    public function detallesLiquidacion($bd, $listaCuentas, $centroCostoId)
    {
        $detalles = $this->detalles;
        $detalleAfp = $this->detalleAfp;
        $detalleSeguroCesantia = $this->detalleSeguroCesantia;
        $detalleIpsIslFonasa = $this->detalleIpsIslFonasa;
        $detalleMutual = $this->detalleMutual;
        $detalleSalud = $this->detalleSalud;
        $detallesApvi = $this->detalleApvi;
        $detallesApvc = $this->detalleApvc;
        $detalleCaja = $this->detalleCaja;
        $listaHaberes = array();
        $listaDescuentos = array();
        $ap = array();

        $cuentasCodigo = array();
        if(count($listaCuentas)){
            foreach ($listaCuentas as $key => $value) {
                $cuentasCodigo[$value['id']]=$value;
            }
        }
        
        if($this->sueldo>0){
            $sueldo = TipoHaber::find(12);
            $codigo = $sueldo->cuenta($cuentasCodigo, $centroCostoId);
            
            if($sueldo) {
                $listaHaberes[] = array(
                    'nombre' => 'Sueldo Base',
                    'monto' => $this->sueldo,
                    'idCuenta' => $codigo
                );
            }
        }
        
        if($this->impuesto_determinado>0){
            $impuesto = TipoDescuento::find(1);
            $codigo = $impuesto->cuenta($cuentasCodigo, $centroCostoId);

            $listaDescuentos[] = array(
                'nombre' => 'Impuesto Único al Trabajo',
                'monto' => $this->impuesto_determinado,
                'idCuenta' => $codigo
            );
        }
        
        if($this->gratificacion>0){
            $gratificacion = TipoHaber::find(1);
            $codigo = $gratificacion->cuenta($cuentasCodigo, $centroCostoId);

            $listaHaberes[] = array(
                'nombre' => 'Gratificación',
                'monto' => $this->gratificacion,
                'idCuenta' => $codigo
            );
        }
        
        if($this->total_horas_extra>0){
            $horasExtra = TipoHaber::find(7);
            $codigo = $horasExtra->cuenta($cuentasCodigo, $centroCostoId);
            
            $listaHaberes[] = array(
                'nombre' => 'Horas Extras',
                'monto' => $this->total_horas_extra,
                'idCuenta' => $codigo
            );
        }
        
        if($this->total_cargas>0){
            $cargas = TipoHaber::find(2);
            $codigo = $cargas->cuenta($cuentasCodigo, $centroCostoId);
            
            $listaHaberes[] = array(
                'nombre' => 'Asignación Familiar',
                'monto' => $this->total_cargas,
                'idCuenta' => $codigo
            );
        }
        
        if($this->asignacion_retroactiva>0){
            $cargas = TipoHaber::find(10);
            $codigo = $cargas->cuenta($cuentasCodigo, $centroCostoId);
            
            $listaHaberes[] = array(
                'nombre' => 'Asignación Familiar Retroactiva',
                'monto' => $this->asignacion_retroactiva,
                'idCuenta' => $codigo
            );
        }
        
        if($this->reintegro_cargas>0){
            $cargas = TipoHaber::find(11);
            $codigo = $cargas->cuenta($cuentasCodigo, $centroCostoId);
            
            $listaHaberes[] = array(
                'nombre' => 'Reintegro Cargas Familiares',
                'monto' => $this->reintegro_cargas,
                'idCuenta' => $codigo
            );
        }
        
        if($detalleAfp){
            if($detalleAfp->afp_id!=35){
                $aporteEmpresa = 0;
                $cotizacionTrabajador = $detalleAfp->cotizacion;
                if($detalleAfp->paga_sis=='empleado'){
                    $cotizacionTrabajador = ($cotizacionTrabajador + $detalleAfp->sis);
                }else{
                    $aporteEmpresa = $detalleAfp->sis;
                }
                $listaDescuentos[] = array(
                    'nombre' => 'AFP ' . $detalleAfp->afp->glosa,
                    'monto' => $cotizacionTrabajador,
                    'idCuenta' => $detalleAfp->cuenta($cuentasCodigo, $centroCostoId)
                );
                if($aporteEmpresa>0){
                    $ap[] = array(
                        'nombre' => 'Aporte Empleador AFP ' . $detalleAfp->afp->glosa,
                        'monto' => $aporteEmpresa,
                        'idCuenta' => $detalleAfp->cuentaSis($cuentasCodigo, $centroCostoId)
                    );
                }
                /*if($detalleAfp->cuenta_ahorro_voluntario>0){
                    $listaDescuentos[] = array(
                        'nombre' => 'Cuenta de Ahorro AFP ' . $detalleAfp->afp->glosa,
                        'monto' => $detalleAfp->cuenta_ahorro_voluntario,
                        'idCuenta' => $detalleAfp->cuentaAhorro($cuentasCodigo, $centroCostoId)
                    );
                }*/
            }
        }
        
        if($detalleSeguroCesantia){
            if($detalleSeguroCesantia->afp_id!=35){
                if($detalleSeguroCesantia->aporte_trabajador>0){
                    $listaDescuentos[] = array(
                        'nombre' => 'Seguro Cesantía Trabajador AFP ' . $detalleSeguroCesantia->afp->glosa,
                        'monto' => $detalleSeguroCesantia->aporte_trabajador,
                        'idCuenta' => $detalleSeguroCesantia->cuenta($cuentasCodigo, $centroCostoId)
                    );
                }
                if($detalleSeguroCesantia->aporte_empleador>0){
                    $ap[] = array(
                        'nombre' => 'Aporte Empleador Seg de Cesant AFP ' . $detalleSeguroCesantia->afp->glosa,
                        'monto' => $detalleSeguroCesantia->aporte_empleador,
                        'idCuenta' => $detalleSeguroCesantia->cuentaEmpleador($cuentasCodigo, $centroCostoId)
                    );
                }
            }
        }
        
        if($detalleIpsIslFonasa){
            if($detalleIpsIslFonasa->cotizacion_fonasa>0){
                $listaDescuentos[] = array(
                    'nombre' => 'Cotización salud Fonasa',
                    'monto' => $detalleIpsIslFonasa->cotizacion_fonasa,
                    'idCuenta' => $detalleIpsIslFonasa->cuenta($cuentasCodigo, $centroCostoId)
                );
            }
            if($detalleIpsIslFonasa->cotizacion_isl>0){
                $ap[] = array(
                    'nombre' => 'ISL',
                    'monto' => $detalleIpsIslFonasa->cotizacion_isl,
                    'idCuenta' => $detalleIpsIslFonasa->cuentaIsl($cuentasCodigo,$centroCostoId)
                );
            }
        }
        
        if($detalleSalud){
            if( $detalleSalud->isapre->glosa != "Sin Isapre" && $detalleSalud->isapre->glosa != "Fonasa" ) {
                $listaDescuentos[] = array(
                    'nombre' => $detalleSalud->isapre->glosa,
                    'monto' => ($detalleSalud->cotizacion_obligatoria + $detalleSalud->cotizacion_adicional),
                    'idCuenta' => $detalleSalud->cuenta($cuentasCodigo, $centroCostoId)
                );
            }
        }
        
        if($detallesApvi->count()){
            foreach($detallesApvi as $detalleApvi){
                $nombre = 'APV $ (Régimen ' . strtoupper($detalleApvi->regimen) . ' Individual) AFP ' . $detalleApvi->afp->glosa;
                if(strlen($nombre)>50){
                    $nombre = substr($nombre, 0, 50);
                }
                $listaDescuentos[] = array(
                    'nombre' => $nombre,
                    'monto' => $detalleApvi->monto,
                    'idCuenta' => $detalleApvi->cuenta($cuentasCodigo, $centroCostoId)
                );
            }
        }
        
        if($detallesApvc->count()){
            foreach($detallesApvc as $detalleApvc){
                $listaDescuentos[] = array(
                    'nombre' => 'APVC AFP ' . $detalleApvc->afp->glosa,
                    'monto' => $detalleApvc->monto,
                    'idCuenta' => $detalleApvc->cuenta($cuentasCodigo, $centroCostoId)
                );
            }
        }
        
        if($detalleCaja){
            if($detalleCaja->cotizacion>0){
                $listaDescuentos[] = array(
                    'nombre' => 'Aporte Fonasa a C.C.A.F.',
                    'monto' => $detalleCaja->cotizacion,
                    'idCuenta' => $detalleCaja->cuenta($cuentasCodigo, $centroCostoId)
                );
            }
        }
        
        if($detalleMutual){
            $ap[] = array(
                'nombre' => 'Mutual de Seguridad',
                'monto' => $detalleMutual->cotizacion_accidentes,
                'idCuenta' => $detalleMutual->cuenta($cuentasCodigo, $centroCostoId)
            );
        }
        
        if($detalles->count()){
            foreach($detalles as $detalle){
                if($detalle->tipo_id==1){
                    $haber = TipoHaber::find($detalle->detalle_id);
                    $codigo = $haber->cuenta($cuentasCodigo, $centroCostoId);

                    $listaHaberes[] = array(
                        'nombre' => $detalle->nombre,
                        'monto' => $detalle->valor,
                        'idCuenta' => $codigo
                    );
                }else if($detalle->tipo_id==2){
                    $descuento = TipoDescuento::find($detalle->detalle_id);
                    $codigo = $descuento->cuenta($cuentasCodigo, $centroCostoId);

                    $listaDescuentos[] = array(
                        'nombre' => $detalle->nombre,
                        'monto' => $detalle->valor,
                        'idCuenta' => $codigo
                    );                  
                }else if($detalle->tipo_id==4){
                    $descuento = TipoDescuento::find($detalle->detalle_id);
                    $codigo = $descuento->cuenta($cuentasCodigo, $centroCostoId);

                    $listaDescuentos[] = array(
                        'nombre' => 'Préstamo',
                        'codigo' => $detalle->tipo,
                        'glosa' => $detalle->nombre,
                        'monto' => $detalle->valor,
                        'idCuenta' => $codigo
                    );                  
                }
            }
        }

        $datos = array(
            'haberes' => $listaHaberes,
            'descuentos' => $listaDescuentos,
            'aportes' => $ap
        );
        
        return $datos;
    }
    
    static function comprobarCuentas($liquidaciones)
    {
        $listaAportes = array();
        $listaAfps = array();
        $listaExCajas = array();
        $listaApvs = array();
        $listaApvcs = array();
        $listaCCAF = array();
        $listaSalud = array();
        $listaHaberes = array();
        $listaDescuentos = array();
        if($liquidaciones->count()){
            foreach($liquidaciones as $liquidacion){
                $detalleAfp = $liquidacion->detalleAfp;
                if($detalleAfp){
                    $listaAfps[] = $detalleAfp->afp_id;
                }
                $detallesAPVC = $liquidacion->detalleApvc;
                if($detallesAPVC->count()){
                    foreach($detallesAPVC as $detalleAPVC){
                        $listaApvcs[] = $detalleAPVC->afp_id;   
                    }
                }
                $detallesAPVI = $liquidacion->detalleApvi;
                if($detallesAPVI->count()){
                    foreach($detallesAPVI as $detalleAPVI){
                        $listaApvs[] = $detalleAPVI->afp_id;   
                    }
                }
                $detalleCaja = $liquidacion->detalleCaja;
                if($detalleCaja){
                    $listaCCAF[] = $detalleCaja->caja_id;   
                }
                $detalleIpsIslFonasa = $liquidacion->detalleIpsIslFonasa;
                if($detalleIpsIslFonasa){
                    if($detalleIpsIslFonasa->cotizacion_fonasa>0){
                        $listaAportes[] = $detalleIpsIslFonasa->cotizacion_fonasa;   
                    }
                    if($detalleIpsIslFonasa->tasa_cotizacion>0){
                        $listaExCajas[] = $detalleIpsIslFonasa->ex_caja_id;   
                    }
                    if($detalleIpsIslFonasa->cotizacion_isl>0){
                        $listaAportes[] = $detalleIpsIslFonasa->cotizacion_isl;   
                    }
                }
                $detalleSalud = $liquidacion->detalleSalud;
                if($detalleSalud){
                    $listaSalud[] = $detalleSalud->salud_id;   
                }
                $detalleSeguroCesantia = $liquidacion->detalleSeguroCesantia;
                if($detalleSeguroCesantia){
                    if($detalleSeguroCesantia->aporte_empleador>0){
                        $listaAportes[] = $detalleSeguroCesantia->aporte_empleador; 
                    }
                    if($detalleSeguroCesantia->aporte_trabajador>0){
                        $listaAportes[] = $detalleSeguroCesantia->aporte_trabajador; 
                    }
                }
                $detalles = $liquidacion->detalles;
                if($detalles->count()){
                    foreach($detalles as $detalle){
                        if($detalle->tipo_id==1){
                            $listaHaberes[] = $detalle->detalle_id;   
                        }else if($detalle->tipo_id==2){
                            $listaDescuentos[] = $detalle->detalle_id;   
                        }
                    }
                }
                if($liquidacion->total_horas_extra>0){
                    $listaHaberes[] = 7; 
                }
            }   
        }
        $datos = array(
            'aportes' => $listaAportes,
            'afps' => $listaAfps,
            'exCajas' => $listaExCajas,
            'apvs' => $listaApvs,
            'apvcs' => $listaApvcs,
            'ccaf' => $listaCCAF,
            'salud' => $listaSalud,
            'haberes' => $listaHaberes,
            'descuentos' => $listaDescuentos
        );

        return $datos;    
    }
    
    static function remuneracionAnualDevengada()
    {
        $sum = 0;
        $mesActual = \Session::get('mesActivo')->mes;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' year', strtotime($mesActual)));
        $liquidaciones = Liquidacion::whereBetween('mes', [$mesAnterior, $mesActual])->get();
        if($liquidaciones->count()){
            $sum = $liquidaciones->sum('sueldo_liquido');
        }
        
        return $sum;
    }
    
    public function misHaberes()
    {
        $idLiquidacion = $this->id;
        $listaHaberes = array();
        $misHaberes = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('tipo_id', 1)->get();
        
        if( $misHaberes->count() ){
            foreach($misHaberes as $haber){
                $listaHaberes[] = array(
                    'id' => $haber->id,
                    'sid' => $haber->sid,
                    'moneda' => $haber->valor_3,
                    'monto' => $haber->valor_2,
                    'montoPesos' => $haber->valor,
                    'tipo' => array(
                        'nombre' => $haber->nombre,
                        'imponible' => $haber->tipo ? true : false
                    )
                );
            }
        }
        
        return $listaHaberes;
    }
    
    public function apv($recaudador=null)
    {
        $idLiquidacion = $this->id;
        $apv = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('tipo_id', 3)->first();
        $codigoAfp = '';
        $contrato = '';
        $formaPago = '';
        $monto = '';
        $depositosConvenidos = '';        
        
        if($apv){
            if(!$recaudador){
                $recaudador = 1;
            }
            $idAfp = $apv->valor_4;
            $idFormaPago = $apv->valor_5;
            if($idAfp!=0){
                $codigoAfp = Glosa::find($idAfp)->codigo($recaudador)['codigo'];
                $formaPago = Glosa::find($idFormaPago)->codigo($recaudador)['codigo'];
            }
            $contrato = '1';
            $monto = $apv->valor;
            $depositosConvenidos = '1';
        }        
        
        $detallesApv = array(
            'codigo' => $codigoAfp,
            'contrato' => $contrato,
            'formaPago' => $formaPago,
            'monto' => $monto,
            'depositosConvenidos' => $depositosConvenidos
        );
        
        return $detallesApv;
    }
    
    public function apvc($recaudador=null)
    {
        $idLiquidacion = $this->id;
        $apvc = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('valor_4', 2)->first();
        $codigoAfp = '';
        $contrato = '';
        $formaPago = '';
        $montoTrabajador = '';
        $montoEmpleador = '';
        
        if($apvc){
            if(!$recaudador){
                $recaudador = 1;
            }
            $idAfp = $apvc->valor_6;
            $idFormaPago = $apvc->valor_5;
            
            $codigoAfp = Glosa::find($idAfp)->codigo($recaudador)['codigo'];
            $formaPago = Glosa::find($idFormaPago)->codigo($recaudador)['codigo'];
            
            $contrato = '65461';
            $montoTrabajador = $apvc->valor;
            $montoEmpleador = 0;
        }        
        
        $detallesApvc = array(
            'codigo' => $codigoAfp,
            'contrato' => $contrato,
            'formaPago' => $formaPago,
            'montoTrabajador' => $montoTrabajador,
            'montoEmpleador' => $montoEmpleador
        );
        
        return $detallesApvc;
    }
    
    public function misPrestamos()
    {
        $idLiquidacion = $this->id;
        $listaPrestamos = array();
        $misPrestamos = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('tipo_id', 4)->get();
        
        if( $misPrestamos->count() ){
            foreach($misPrestamos as $prestamo){
                $listaPrestamos[] = array(
                    'moneda' => $prestamo->valor_3,
                    'monto' => $prestamo->valor_2,
                    'nombreLiquidacion' => $prestamo->nombre,
                    'cuotas' => $prestamo->valor_5,
                    'cuotaPagar' => array(
                        'monto' => $prestamo->valor,
                        'numero' => $prestamo->valor_4
                    )
                );
            }
        }
        
        return $listaPrestamos;
    }
    
    public function misApvs()
    {
        $idLiquidacion = $this->id;
        $listaApvs = array();
        $misApvs = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('tipo_id', 3)->get();
        
        if( $misApvs->count() ){
            foreach($misApvs as $apv){
                $listaApvs[] = array(
                    'moneda' => $apv->valor_3,
                    'monto' => $apv->valor_2,
                    'montoPesos' => $apv->valor,
                    'afp' => array(
                        'id' => $apv->valor_4,
                        'nombre' => $apv->nombre
                    )
                );
            }
        }
        
        return $listaApvs;
    }
    
    public function totalApvs()
    {
        $totalApvs = 0;
        $apvs = $this->detalleApvi;
        
        if($apvs){
            foreach($apvs as $apv){
                $totalApvs += ($apv['monto']);
            }
        }
        
        return $totalApvs;
    }
    public function totalApvi()
    {
        $totalApvs = 0;
        $apvs = $this->detalleApvi;
        
        if($apvs){
            foreach($apvs as $apv){
                $totalApvs += ($apv['monto']);
            }
        }
        
        return $apvs;
    }
    
    public function totalSalud()
    {
        $totalSalud = 0;
        $empresa = \Session::get('empresa');        
        
        if($this->detalleSalud){
            if($this->detalleSalud->cotizacion_obligatoria>0){
                $totalSalud = ($this->detalleSalud->cotizacion_obligatoria + $this->detalleSalud->cotizacion_adicional);
            }
        }else if($this->detalleIpsIslFonasa){
            if($this->detalleIpsIslFonasa->cotizacion_fonasa > 0){
                $caja = 0;
                if($empresa->caja_id!=257){
                    if($this->detalleCaja){
                        $caja = $this->detalleCaja->cotizacion;                        
                    }
                }
                $totalSalud = ($this->detalleIpsIslFonasa->cotizacion_fonasa + $caja);
            }
        } 
        
        return $totalSalud;
    }
    
    public function totalAnticipos()
    {
        $totalAnticipos = 0;
        $descuentos = $this->misDescuentos();
        
        if($descuentos){
            foreach($descuentos as $descuento){
                if($descuento['anticipo']=='1'){
                    $totalAnticipos += $descuento['montoPesos'];
                }
            }
        }
        
        return $totalAnticipos;
    }
    
    public function misCargas()
    {
        $cantidad = $this->cantidad_cargas;
        $monto = $this->total_cargas;
        $isCargas = false;
        
        if($cantidad>0){
            $isCargas = true;
        }
        
        $cargasFamiliares = array(
            'cantidad' => $cantidad,
            'monto' => $monto,
            'isCargas' => $isCargas
        );
        
        return $cargasFamiliares;   
    }
    
    public function misDescuentos()
    {        
        $idLiquidacion = $this->id;
        $listaDescuentos = array();
        $misDescuentos = DetalleLiquidacion::where('liquidacion_id', $idLiquidacion)->where('tipo_id', 2)->get();
        
        if( $misDescuentos->count() ){
            foreach($misDescuentos as $descuento){
                $listaDescuentos[] = array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'moneda' => $descuento->valor_3,
                    'monto' => $descuento->valor_2,
                    'montoPesos' => $descuento->valor,
                    'anticipo' => $descuento->valor_4,
                    'tipo' => array(
                        'nombre' => $descuento->nombre
                    ) 
                );
            }
        }
        
        return $listaDescuentos;
    }
    
    public function inasistenciasAtrasos()
    {
        $diasTrabajados = $this->dias_trabajados;
        $inasistenciasAtrasos = (30 - $diasTrabajados);
        
        return $inasistenciasAtrasos;
    }
    
    public function totalHaberes()
    {
        $imponibles = $this->imponibles;
        $noImponibles = $this->no_imponibles;
        $totalHaberes = ($imponibles + $noImponibles);
        
        return $totalHaberes;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'liquidacion.required' => 'Obligatorio!'
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
