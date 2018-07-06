<?php

//use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Trabajador extends Eloquent {
    
    //use SoftDeletingTrait;
    
    protected $table = 'trabajadores';
    //protected $dates = array('deleted_at');
    protected $miFicha = null;
    protected $miRentaImponible=null;
    protected $miTotalSalud=null;
    protected $miSueldo = null;
    protected $miGratificacion = null;
    protected $miMiSemanaCorrida = null;
    protected $miMisDescuentos = null;
    protected $miMisHaberes = null;

    public function contrato(){
        return $this->hasMany('Contrato', 'trabajador_id');
    }        
    
    public function rut_formato(){
        return Funciones::formatear_rut($this->rut);
    }
    
    public function rut_sin_digito(){
        return Funciones::formatear_rut_sin_digito($this->rut);
    }
    
    public function rut_digito(){
        return Funciones::formatear_rut_digito($this->rut);
    }
    
    public function inasistencias(){
        return $this->hasMany('Inasistencia','trabajador_id');
    
    
    }public function atrasos(){
        return $this->hasMany('Atrasos','trabajador_id');
    }
    
    public function licencias(){
        return $this->hasMany('Licencia','trabajador_id');
    }
    
    public function horasExtra(){
        return $this->hasMany('HoraExtra','trabajador_id');
    }
        
    public function vacaciones(){
        return $this->hasMany('Vacaciones','trabajador_id');
    }
    
    public function haberes(){
        return $this->hasMany('Haber','trabajador_id');
    }
    
    public function descuentos(){
        return $this->hasMany('Descuento','trabajador_id');
    }
    
    public function prestamos(){
        return $this->hasMany('Prestamo','trabajador_id');
    }
    
    public function documentos(){
        return $this->hasMany('Documento','trabajador_id');
    }        
    
    public function secciones(){
        return $this->hasMany('Seccion','encargado_id');
    }
    
    public function liquidaciones(){
        return $this->hasMany('Liquidacion','trabajador_id');
    }
    
    public function cartasNotificacion(){
        return $this->hasMany('CartaNotificacion','trabajador_id');
    }
    
    public function certificados(){
        return $this->hasMany('Certificado','trabajador_id');
    }
    
    public function finiquito(){
        return $this->hasMany('Finiquito','trabajador_id');
    }
    
    public function cargas(){
        return $this->hasMany('Carga','trabajador_id');
    }
    
    public function apvs(){
        return $this->hasMany('Apv','trabajador_id');
    }
    
    public function fichaTrabajador()
    {
        return $this->hasMany('FichaTrabajador', 'trabajador_id')->orderBy('fecha');
    }
    
    public function fichaTrabajodorUltima()
    {
        return $this->fichaTrabajadorUltima();
    }
    
    public function fichaTrabajadorUltima()
    {
        return $this->hasOne('FichaTrabajador', 'trabajador_id')->orderBy('fecha', 'DESC');
    }
    
    /*static function centralizar($fecha, $empresa_id)
    {
        $meses = Config::get('constants.meses');
        $empresa = Empresa::find($empresa_id);
        Config::set('database.default', $empresa->base_datos);
        $mes = MesDeTrabajo::where('mes', $fecha)->first();
        $liquidaciones = Liquidacion::where('mes', $mes['mes'])->orderBy('trabajador_apellidos')->get();
        $lista = array();
        $listaCuentas = array();

        $isCME = \Session::get('empresa')->isCME;
        if(!$isCME) {
            $cuentas = Cuenta::all();

            if ($cuentas->count()) {
                foreach ($cuentas as $cuenta) {
                    $listaCuentas[] = array(
                        'id' => $cuenta->id,
                        'codigo' => $cuenta->codigo,
                        'nombre' => $cuenta->nombre,
                        'comportamiento' => $cuenta->comportamiento
                    );
                }
            }
        }else{
            $listaCuentas = Cuenta::listaCuentas();
        }
        
        foreach($liquidaciones as $liquidacion){
            $descuentos = array();
            $detalles = $liquidacion->detallesLiquidacion($empresa->base_datos, $listaCuentas);

            $lista[] = array(
                'idTrabajador' => $liquidacion->trabajador_id,
                'rut' => $liquidacion->trabajador_rut,
                'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                'sueldoBase' => $liquidacion->sueldo_base,
                'sueldo' => $liquidacion->sueldo,
                'rentaImponible' => $liquidacion->renta_imponible,
                'sueldoLiquido' => $liquidacion->sueldo_liquido,
                'haberes' => $detalles['haberes'],
                'descuentos' => $detalles['descuentos'],
                'aportes' => $detalles['aportes'],
                'pais' => 'CHILE',
                'canal' => $liquidacion->trabajador_seccion? $liquidacion->trabajador_seccion : '-',
                'tienda' => $liquidacion->trabajador_tienda ? $liquidacion->trabajador_tienda : '-'
            );
        }
        $mes = $meses[ intval(date("m", strtotime($fecha)))-1 ]['value'];
        $datos = array(
            'general' => array(
                'rut' => '112223334',
                'nombre' => 'Usuario Admin',
                'periodo' => $fecha,
                'cuentas' => $listaCuentas,
                'comentario' => 'Centralización '.$mes.' '.date("Y", strtotime($fecha))
            ),
            'detalle' => $lista                
        );
        
        return $datos;
    }*/
    
    static function centralizar($fecha, $empresa_id)
    {
        $meses = Config::get('constants.meses');
        $empresa = Empresa::find($empresa_id);
        Config::set('database.default', $empresa->base_datos);
        $mes = MesDeTrabajo::where('mes', $fecha)->first();
        $liquidaciones = Liquidacion::where('mes', $mes['mes'])->orderBy('trabajador_apellidos')->get();
        $lista = array();
        $totalLiquido = 0;

        $listaCuentas = Cuenta::listaCuentas();
        
        foreach($liquidaciones as $liquidacion){
            $descuentos = array();
            $empleado = $liquidacion->trabajador->ficha();
            $detalles = $liquidacion->detallesLiquidacion($empresa->base_datos, $listaCuentas, $empleado->centro_costo_id );
            $totalLiquido += $liquidacion->sueldo_liquido;
            $lista[] = array(
                'idTrabajador' => $liquidacion->trabajador_id,
                'rut' => $liquidacion->trabajador_rut,
                'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                'sueldoBase' => $liquidacion->sueldo_base,
                'sueldo' => $liquidacion->sueldo,
                'rentaImponible' => $liquidacion->renta_imponible,
                'sueldoLiquido' => $liquidacion->sueldo_liquido,
                'haberes' => $detalles['haberes'],
                'descuentos' => $detalles['descuentos'],
                'aportes' => $detalles['aportes'],
                'centroCosto' => $empleado->centro_costo_id,
                'nombreCentroCosto' => $empleado->centroCosto ? $empleado->centroCosto->nombre : ""
            );
        }
        $mes = $meses[ intval(date("m", strtotime($fecha)))-1 ]['value'];
        $datos = array(
            'general' => array(
                'rut' => '112223334',
                'nombre' => 'Usuario Admin',
                'periodo' => $fecha,
                'cuentas' => $listaCuentas,
                'totalLiquido' => $totalLiquido,
                'comentario' => 'Centralización '.$mes.' '.date("Y", strtotime($fecha))
            ),
            'detalle' => $lista                
        );
        
        return $datos;
    }
    
    public function ficha()
    {
        if(!$this->miFicha){
            $idMes = \Session::get('mesActivo')->id;
            $mes = \Session::get('mesActivo')->mes;
            $idTrabajador = $this->id;
            $ficha = FichaTrabajador::where('trabajador_id', $idTrabajador)->where('fecha', '<=', $mes)->orderBy('fecha', 'DESC')->first();
            if(!$ficha){
                $ficha = FichaTrabajador::where('trabajador_id', $idTrabajador)->where('fecha', '<=', $mes)->orderBy('fecha', 'DESC')->first();
            }
            if(!$ficha){
                $ficha = null;
            }

            $this->miFicha = $ficha;
        }
        
        return $this->miFicha;
    }     
    
    public function ultimaFicha($ingresado=false)
    {
        $idTrabajador = $this->id;
        $ficha = FichaTrabajador::where('trabajador_id', $idTrabajador)->orderBy('id', 'DESC')->first();
        if($ficha){
            if($ficha->estado=='Ingresado' || !$ingresado){
                return $ficha;
            }
        }

        return null;
    } 
    
    public function fichaAnual($anio)
    {
        $mes = $anio . '-12-01';
        $idTrabajador = $this->id;        
        $ficha = FichaTrabajador::where('trabajador_id', $idTrabajador)->where('fecha', '<=', $mes)->orderBy('fecha', 'DESC')->first();
        
        return $ficha;
    }
    
    public function fechaFicha($fecha)
    {
        $mes = \Session::get('mesActivo');
        $empresa = \Session::get('empresa');
        if($mes->mes > $fecha){
            $primerMes = MesDeTrabajo::orderBy('mes')->first();            
            if($primerMes->mes > $fecha){
                $fecha = $primerMes->mes;
            }else{
                $m = date("m", strtotime($fecha));
                $y = date("Y", strtotime($fecha));
                $fecha = $y . '-' . $m . '-01';
            }
        }else{
            $m = date("m", strtotime($fecha));
            $y = date("Y", strtotime($fecha));
            $fecha = $y . '-' . $m . '-01';
        }

        return $empresa;
    }
    
    public function crearUser()
    {
        $empresa = \Session::get('empresa');
        if($empresa->portal){
            $usuario = Usuario::where('funcionario_id', $this->id)->first();
            if(!$usuario){
                $user = new Usuario();
                $user->sid = Funciones::generarSID();
                $user->funcionario_id = $this->id;
                $user->username = $this->rut;
                $pass = $user->generarClave();
                $user->password = Hash::make($pass);
                $user->activo = 2;
                $user->save();

                $permiso = new Permiso();
                $permiso->usuario_id = $user->id;
                $permiso->documentos_empresa = 1;
                $permiso->cartas_notificacion = 1;
                $permiso->certificados = 1;
                $permiso->liquidaciones = 1;
                $permiso->solicitudes = 1;
                $permiso->save();
            }
        }
        
        return;
    }    
    
    public function totalCargasFamiliares()
    {        
        $totalCargasFamiliares = 0;
        $cargas = $this->cargas;
        if($cargas->count()){
            foreach($cargas as $carga){
                if($carga->es_carga){
                    $totalCargasFamiliares++;
                }
            }
        }
        
        return $totalCargasFamiliares;
    }
    
    public function totalCargasAutorizadas()
    {        
        $totalCargasFamiliares = 0;
        $cargas = $this->cargas;
        
        if($cargas->count()){
            foreach($cargas as $carga){
                if($carga->es_carga && $carga->es_autorizada==1){
                    $totalCargasFamiliares++;
                }
            }
        }
        
        return $totalCargasFamiliares;
    }
    
    public function numeroCargasAutorizadas()
    {        
        $totalCargasFamiliares = $this->totalCargasAutorizadas();
        
        if($totalCargasFamiliares==0){
            return '';
        }
        
        return $totalCargasFamiliares;
    }
    
    public function totalCargasPagar()
    {        
        $cargasFamiliares = array();
        $mes = \Session::get('mesActivo');
        
        if($this->cargas->count()){
            foreach($this->cargas as $carga){
                if($carga->es_carga){
                    if(!$carga->fecha_pago_desde && !$carga->fecha_pago_hasta || 
                       !$carga->fecha_pago_hasta && $carga->fecha_pago_desde && $carga->fecha_pago_desde <= $mes->mes || 
                       !$carga->fecha_pago_desde && $carga->fecha_pago_hasta && $carga->fecha_pago_hasta >= $mes->mes || 
                       $carga->fecha_pago_desde && $carga->fecha_pago_hasta && $carga->fecha_pago_desde <= $mes->mes && $carga->fecha_pago_hasta >= $mes->mes){
                            $cargasFamiliares[] = $carga;
                    }
                }
            }
        }
        
        return $cargasFamiliares;
    }
    
    public function totalGrupoFamiliar()
    {        
        $totalGrupoFamiliar = 0;
        $cargas = $this->cargas;
        
        if($cargas->count()){
            $totalGrupoFamiliar = $cargas->count();
        }
        
        return $totalGrupoFamiliar;
    }
    
    public function misCargas()
    {        
        $misCargas = $this->cargas;
        $listaCargas = array();
        
        if( $misCargas ){
            foreach($misCargas as $carga){
                $listaCargas[] = array(
                    'id' => $carga->id,
                    'sid' => $carga->sid,
                    'created_at' => date('Y-m-d H:i:s', strtotime($carga->created_at)),
                    'parentesco' => $carga->parentesco,
                    'tipo' => $carga->tipoCarga,
                    'esCarga' => $carga->es_carga ? true : false,
                    'esAutorizada' => $carga->es_autorizada ? true : false,
                    'rut' => $carga->rut,
                    'rutFormato' => Funciones::formatear_rut($carga->rut),
                    'nombreCompleto' => $carga->nombre_completo,
                    'fechaNacimiento' => $carga->fecha_nacimiento,
                    'fechaAutorizacion' => $carga->fecha_autorizacion,
                    'sexo' => $carga->sexo
                );
            }
        }
        
        return $listaCargas;
    }
    
    public function cargasFamiliares()
    {        
        $cargas = $this->totalCargasPagar();
        $mes = \Session::get('mesActivo');
        $monto = 0;
        $cargasSimples = 0;
        $cargasMaternales = 0;
        $cargasInvalidas = 0;
        $isCargas = false;
        $empleado = $this->ficha();
        $idTramo = $empleado->tramo_id;
        $diasTrabajados = $this->diasTrabajados();
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
        if($idTramo && $cargas){
            $tramo = AsignacionFamiliar::where('tramo', $idTramo)->where('mes', $fecha)->first();
            $monto = $tramo->monto;
            $monto = ( count($cargas) * $monto );
            if($diasTrabajados < 25){
                $monto = (($monto / 30) * $diasTrabajados);
            }
            $isCargas = true;
        }
        foreach($cargas as $carga){
            if($carga->tipo_carga_id==3){
                $cargasInvalidas++;
            }else if($carga->tipo_carga_id==2){
                $cargasMaternales++;
            }else if($carga->tipo_carga_id==1){
                $cargasSimples++;
            }
        }
        
        $cargasFamiliares = array(
            'cantidad' => count($cargas),
            'cantidadSimples' => $cargasSimples,
            'cantidadMaternales' => $cargasMaternales,
            'cantidadInvalidas' => $cargasInvalidas,
            'monto' => $monto,
            'isCargas' => $isCargas
        );
        
        return $cargasFamiliares;
    }
    
    public function asignacionRetroactiva()
    {
        $monto = 0;
        $mes = \Session::get('mesActivo')->id;
        $id = $this->trabajador_id;
        $asignacionesRetroactivas = Haber::where('trabajador_id', $id)->where('tipo_haber_id', 10)->where('mes_id', $mes)->get();
        
        if($asignacionesRetroactivas->count()){
            foreach($asignacionesRetroactivas as $asignacionRetroactiva){
                $monto = ($monto + Funciones::convertir($asignacionRetroactiva->monto, $asignacionRetroactiva->moneda));
            }
        }
        
        return $monto;
    }
    
    public function reintegroCargasFamiliares()
    {
        return '';
    }
    
    public function solicitudTrabajadorJoven()
    {
        return 'N';
    }
    
    public function asignacionFamiliar()
    {
        $monto = $this->cargasFamiliares()['monto'];
        if($monto==0){
            return '';
        }
        
        return $monto;
    }
    
    public function isCargas()
    {
        $isCargas = false;
        $cargas = $this->misCargas();
        if($cargas){
            $isCargas = true;
        }
        
        return $isCargas;
    }      
    
    public function miGrupoFamiliar()
    {        
        $misCargas = Carga::where('trabajador_id', $this->id)->orderBy('es_carga', 'DESC')->get();
        $listaCargas = array();
        
        if( $misCargas->count() ){
            foreach($misCargas as $carga){
                if($carga->rut){
                    $listaCargas[] = array(
                        'id' => $carga->id,
                        'sid' => $carga->sid,
                        'created_at' => date('Y-m-d H:i:s', strtotime($carga->created_at)),
                        'parentesco' => $carga->parentesco,
                        'tipoCarga' => array(
                            'id' => $carga->tipoCarga->id,
                            'nombre' => $carga->tipoCarga->nombre
                        ),
                        'esCarga' => $carga->es_carga ? true : false,
                        'esAutorizada' => $carga->es_autorizada ? true : false,
                        'rut' => $carga->rut,
                        'rutFormato' => Funciones::formatear_rut($carga->rut),
                        'nombreCompleto' => $carga->nombre_completo,
                        'fechaNacimiento' => $carga->fecha_nacimiento,
                        'fechaAutorizacion' => $carga->fecha_autorizacion,
                        'fechaPagoDesde' => $carga->fecha_pago_desde,
                        'fechaPagoHasta' => $carga->fecha_pago_hasta,
                        'sexo' => $carga->sexo
                    );
                }
            }
        }
        
        return $listaCargas;
    }
    
    public function miGrupo()
    {        
        $misCargas = Carga::where('trabajador_id', $this->id)->orderBy('es_carga', 'DESC')->get();
        $listaCargas = array();
        
        if( $misCargas->count() ){
            foreach($misCargas as $carga){
                if(!$carga->rut){
                    $listaCargas[] = array(
                        'id' => $carga->id,
                        'sid' => $carga->sid,
                        'fechaAutorizacion' => $carga->fecha_autorizacion,
                        'fechaPagoDesde' => $carga->fecha_pago_desde,
                        'fechaPagoHasta' => $carga->fecha_pago_hasta,
                        'tipoCarga' => array(
                            'id' => $carga->tipoCarga->id,
                            'nombre' => $carga->tipoCarga->nombre
                        ),
                        'created_at' => date('Y-m-d H:i:s', strtotime($carga->created_at)),
                        'esCarga' => $carga->es_carga ? true : false
                    );
                }
            }
        }
        
        return $listaCargas;
    }
    
    public function misApvs()
    {        
        $misApvs = $this->apvs;
        $listaApvs = array();
        
        if( $misApvs ){
            foreach($misApvs as $apv){
                $listaApvs[] = array(
                    'id' => $apv->id,
                    'sid' => $apv->sid,
                    'moneda' => $apv->moneda,
                    'numeroContrato' => $apv->numero_contrato,
                    'monto' => $apv->monto,
                    'regimen' => strtoupper($apv->regimen),
                    'fechaPagoDesde' => $apv->fecha_pago_desde ? Funciones::obtenerMesAnioTextoAbr($apv->fecha_pago_desde) : '',
                    'fechaPagoHasta' => $apv->fecha_pago_hasta ? Funciones::obtenerMesAnioTextoAbr($apv->fecha_pago_hasta) : '',
                    'montoPesos' => Funciones::convertir($apv->monto, $apv->moneda),
                    'afp' => array(
                        'id' => $apv->afp->id,
                        'nombre' => $apv->afp->glosa
                    ),
                    'formaPago' => array(
                        'id' => $apv->formaPago->id,
                        'nombre' => $apv->formaPago->glosa
                    )
                );
            }
        }
        
        return $listaApvs;
    }
    
    public function misApvsPagar()
    {        
        $misApvs = $this->apvs;
        $listaApvs = array();
        
        if( $misApvs ){
            $mes = \Session::get('mesActivo');
            foreach($misApvs as $apv){
                if(!$apv->fecha_pago_desde && !$apv->fecha_pago_hasta || 
                   !$apv->fecha_pago_hasta && $apv->fecha_pago_desde && $apv->fecha_pago_desde <= $mes->mes || 
                   !$apv->fecha_pago_desde && $apv->fecha_pago_hasta && $apv->fecha_pago_hasta >= $mes->mes || 
                    $apv->fecha_pago_desde && $apv->fecha_pago_hasta && $apv->fecha_pago_desde <= $mes->mes && $apv->fecha_pago_hasta >= $mes->mes){
                    $listaApvs[] = array(
                        'id' => $apv->id,
                        'sid' => $apv->sid,
                        'moneda' => $apv->moneda,
                        'numeroContrato' => $apv->numero_contrato,
                        'monto' => $apv->monto,
                        'regimen' => strtoupper($apv->regimen),
                        'fechaPagoDesde' => $apv->fecha_pago_desde ? Funciones::obtenerMesAnioTextoAbr($apv->fecha_pago_desde) : '',
                        'fechaPagoHasta' => $apv->fecha_pago_hasta ? Funciones::obtenerMesAnioTextoAbr($apv->fecha_pago_hasta) : '',
                        'montoPesos' => Funciones::convertir($apv->monto, $apv->moneda),
                        'afp' => array(
                            'id' => $apv->afp->id,
                            'nombre' => $apv->afp->glosa
                        ),
                        'formaPago' => array(
                            'id' => $apv->formaPago->id,
                            'nombre' => $apv->formaPago->glosa
                        )
                    );
                }
            }
        }
        
        return $listaApvs;
    }
    
    public function misRegimenes()
    {        
        $misApvs = $this->apvs;
        $listaApvs = array();
        $regimen = '';
        $regimenes = array();
        
        if( $misApvs ){
            foreach($misApvs as $apv){
                $regimenes[] = strtoupper($apv->regimen);
            }
            $regimen = implode(' , ', $regimenes);
        }
        
        return $regimen;
    }
    
    public function totalApv()
    {
        $apvs = $this->misApvs();
        $monto = 0;
        
        if($apvs){
            foreach($apvs as $apv){
                $monto += $apv['montoPesos'];
            }
        }
        
        return $monto;
    }
    
    public function totalApvPagar()
    {
        $apvs = $this->misApvsPagar();
        $monto = 0;
        
        if($apvs){
            foreach($apvs as $apv){
                $monto += $apv['montoPesos'];
            }
        }
        
        return $monto;
    }
    
    static function activos()
    {
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'apellidos' => $empleado->apellidos,
                            'nombreCompleto' => $empleado->nombreCompleto()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');        
        
        return $listaTrabajadores;        
    }
    
    static function activosFiniquitados()
    {
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito < $finMes && $empleado->fecha_finiquito >= $mes->mes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'apellidos' => $empleado->apellidos,
                            'nombreCompleto' => $empleado->nombreCompleto()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');        
        
        return $listaTrabajadores;        
    }
    
    public function semanaCorrida()
    {
        $mes = \Session::get('mesActivo')->mes;
        $semanaCorrida = SemanaCorrida::where('trabajador_id', $this->id)->where('mes', $mes)->first();
        if(!$semanaCorrida){
            $this->crearSemanaCorrida();
            $semanaCorrida = SemanaCorrida::where('trabajador_id', $this->id)->where('mes', $mes)->first();
        }
        $semanas = MesDeTrabajo::semanas();
        $datos = array();
        $datos[] = array('semana' => '1°', 'alias' => 'semana_1', 'comision' => $semanaCorrida->semana_1);
        $datos[] = array('semana' => '2°', 'alias' => 'semana_2', 'comision' => $semanaCorrida->semana_2);
        $datos[] = array('semana' => '3°', 'alias' => 'semana_3', 'comision' => $semanaCorrida->semana_3);
        $datos[] = array('semana' => '4°', 'alias' => 'semana_4', 'comision' => $semanaCorrida->semana_4);
        
        if($semanas>4){
            $datos[] = array('semana' => '5°', 'alias' => 'semana_5', 'comision' => $semanaCorrida->semana_5);
        }
        
        $semana = array(
            'id' => $semanaCorrida->id,
            'semanas' => $datos
        );

        return $semana;
    }
    
    public function misHaberes()
    {
        if(!$this->miMisHaberes){
            $idTrabajador = $this->id;
            $listaHaberes = array();
            $idMes = \Session::get('mesActivo')->id;
            $mes = \Session::get('mesActivo')->mes;      
            
            $misHaberes = Haber::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)
                ->orWhere('permanente', 1)->where('trabajador_id', $idTrabajador)
                ->orWhere('hasta', '>=', $mes)->where('trabajador_id', $idTrabajador)->get();               
            
            $diasTrabajados = $this->diasTrabajados();
            $empleado = $this->ficha();

            if($misHaberes->count()){
                if($diasTrabajados<30){
                    foreach($misHaberes as $haber){
                        if($haber->permanente && !$haber->desde && !$haber->hasta 
                           || $haber->permanente && !$haber->desde && $haber->hasta && $haber->hasta >= $mes 
                           || $haber->permanente && !$haber->hasta && $haber->desde && $haber->desde <= $mes 
                           || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes 
                           || !$haber->permanente){
                            
                            $monto = Funciones::convertir($haber->monto, $haber->moneda);                                        
                            if($haber->tipoHaber->id==2){
                                if($diasTrabajados<25){
                                    $diasTrabajados = $this->diasTrabajados();
                                    $monto = (($monto / 30) * $diasTrabajados);                       
                                }
                            }else{
                                if($haber->tipoHaber->nombre=='Colación' || $haber->tipoHaber->nombre=='Movilización' || $haber->tipoHaber->nombre=='Viático'){                                    
                                    if($haber->proporcional==1){
                                        $diasTrabajados = $this->diasTrabajados();
                                        $monto = (($monto / 30) * $diasTrabajados); 
                                    }
                                }else{
                                    if($haber->tipoHaber->proporcional_dias_trabajados){
                                        $diasTrabajados = $this->diasTrabajados();
                                        $monto = (($monto / 30) * $diasTrabajados);                      
                                    }
                                }
                            }

                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => round($monto),
                                'mes' => Funciones::obtenerMesAnioTexto($haber->mes),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false,
                                    'idCuenta' => $haber->tipoHaber->cuenta_id
                                ),
                                'desde' => Funciones::obtenerMesAnioTexto($haber->desde),
                                'hasta' => Funciones::obtenerMesAnioTexto($haber->hasta),
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }else{
                    foreach($misHaberes as $haber){
                        if($haber->permanente && !$haber->desde && !$haber->hasta 
                           || $haber->permanente && !$haber->desde && $haber->hasta && $haber->hasta >= $mes 
                           || $haber->permanente && !$haber->hasta && $haber->desde && $haber->desde <= $mes 
                           || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes 
                           || !$haber->permanente){
                            
                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => Funciones::convertir($haber->monto, $haber->moneda),
                                'mes' => Funciones::obtenerMesAnioTexto($haber->mes),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false,
                                    'idCuenta' => $haber->tipoHaber->cuenta_id
                                ),
                                'desde' => Funciones::obtenerMesAnioTexto($haber->desde),
                                'hasta' => Funciones::obtenerMesAnioTexto($haber->hasta),
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }
            }
            $this->miMisHaberes = $listaHaberes;
        }
        
        return $this->miMisHaberes;
    }
    
    public function todosMisHaberes()
    {
        $listaHaberes = array();
        $misHaberes = $this->haberes;
        
        if( $misHaberes->count() ){
            foreach($misHaberes as $haber){
                $listaHaberes[] = array(
                    'id' => $haber->id,
                    'sid' => $haber->sid,
                    'moneda' => $haber->moneda,
                    'monto' => $haber->monto,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                    'tipo' => array(
                        'id' => $haber->tipoHaber->id,
                        'nombre' => $haber->tipoHaber->nombre,
                        'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                        'imponible' => $haber->tipoHaber->imponible ? true : false,
                        'tributable' => $haber->tipoHaber->tributable ? true : false,
                        'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                        'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                        'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false,
                        'idCuenta' => $haber->tipoHaber->cuenta_id
                    ),
                    'mes' => $haber->mes ? Funciones::obtenerMesAnioTextoAbr($haber->mes) : '',
                    'desde' => $haber->desde ? Funciones::obtenerMesAnioTextoAbr($haber->desde) : '',
                    'hasta' => $haber->hasta ? Funciones::obtenerMesAnioTextoAbr($haber->hasta) : '',
                    'porMes' => $haber->por_mes ? true : false,
                    'rangoMeses' => $haber->rango_meses ? true : false,
                    'permanente' => $haber->permanente ? true : false,
                    'todosAnios' => $haber->todos_anios ? true : false
                );
            }
        }
        
        return $listaHaberes;
    }
  
    public function haberesImponibles()
    {
        $idTrabajador = $this->id;
        $listaHaberes = array();
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $misHaberes = Haber::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)->orWhere('permanente', 1)->where('trabajador_id', $idTrabajador)->orWhere('rango_meses', 1)->where('desde', '<=', $mes)->where('hasta', '>=', $mes)->where('trabajador_id', $idTrabajador)->get();
        $diasTrabajados = $this->diasTrabajados();
        
        if( $misHaberes->count() ){
            if($diasTrabajados<30){
                foreach($misHaberes as $haber){
                    if($haber->permanente && !$haber->desde && !$haber->hasta || $haber->permanente && $haber->hasta && $haber->hasta >= $mes && !$haber->desde || $haber->permanente && $haber->desde && $haber->desde <= $mes && !$haber->hasta || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes || !$haber->permanente){
                        if($haber->tipoHaber->imponible){
                            $monto = Funciones::convertir($haber->monto, $haber->moneda);

                            if($haber->tipoHaber->proporcional_dias_trabajados){
                                $diasTrabajados = $this->diasTrabajados();
                                $monto = (($monto / 30) * $diasTrabajados);  
                            }
                            
                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => $monto,
                                'mes' => array(
                                    'id' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->id : "",
                                    'nombre' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->nombre : ""
                                ),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false
                                ),
                                'desde' => $haber->desde,
                                'hasta' => $haber->hasta,
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }
            }else{
                foreach($misHaberes as $haber){
                    if($haber->permanente && !$haber->desde && !$haber->hasta || $haber->permanente && $haber->hasta && $haber->hasta >= $mes && !$haber->desde || $haber->permanente && $haber->desde && $haber->desde <= $mes && !$haber->hasta || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes || !$haber->permanente){
                        if($haber->tipoHaber->imponible){
                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => Funciones::convertir($haber->monto, $haber->moneda),
                                'mes' => array(
                                    'id' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->id : "",
                                    'nombre' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->nombre : ""
                                ),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false
                                ),
                                'desde' => $haber->desde,
                                'hasta' => $haber->hasta,
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }
            }
        }
        
        return $listaHaberes;
    }
  
  	public function haberesNoImponibles()
    {
        $idTrabajador = $this->id;
        $listaHaberes = array();
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $misHaberes = Haber::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)->orWhere('permanente', 1)->where('trabajador_id', $idTrabajador)->orWhere('rango_meses', 1)->where('desde', '<=', $mes)->where('hasta', '>=', $mes)->where('trabajador_id', $idTrabajador)->get();
        $diasTrabajados = $this->diasTrabajados();
        
        if( $misHaberes->count() ){
            if($diasTrabajados<30){
                foreach($misHaberes as $haber){
                    if($haber->permanente && !$haber->desde && !$haber->hasta || $haber->permanente && $haber->hasta && $haber->hasta >= $mes && !$haber->desde || $haber->permanente && $haber->desde && $haber->desde <= $mes && !$haber->hasta || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes || !$haber->permanente){
                        if(!$haber->tipoHaber->imponible){
                            $monto = Funciones::convertir($haber->monto, $haber->moneda);

                            if($haber->tipoHaber->id==2){
                                if($diasTrabajados<25){
                                    $diasTrabajados = $this->diasTrabajados();
                                    $monto = (($monto / 30) * $diasTrabajados);                       
                                }
                            }else{
                                if($haber->tipoHaber->nombre=='Colación' || $haber->tipoHaber->nombre=='Movilización' || $haber->tipoHaber->nombre=='Viático'){                                    
                                    if($haber->proporcional==1){
                                        $diasTrabajados = $this->diasTrabajados();
                                        $monto = (($monto / 30) * $diasTrabajados);   
                                    }
                                }else{
                                    if($haber->tipoHaber->proporcional_dias_trabajados){
                                        $diasTrabajados = $this->diasTrabajados();
                                        $monto = (($monto / 30) * $diasTrabajados);                     
                                    }
                                }
                            }

                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => $monto,
                                'mes' => array(
                                    'id' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->id : "",
                                    'nombre' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->nombre : ""
                                ),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false
                                ),
                                'desde' => $haber->desde,
                                'hasta' => $haber->hasta,
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }
            }else{
                foreach($misHaberes as $haber){
                    if($haber->permanente && !$haber->desde && !$haber->hasta || $haber->permanente && $haber->hasta && $haber->hasta >= $mes && !$haber->desde || $haber->permanente && $haber->desde && $haber->desde <= $mes && !$haber->hasta || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes || !$haber->permanente){
                        if(!$haber->tipoHaber->imponible){
                            $listaHaberes[] = array(
                                'id' => $haber->id,
                                'sid' => $haber->sid,
                                'moneda' => $haber->moneda,
                                'monto' => $haber->monto,
                                'montoPesos' => Funciones::convertir($haber->monto, $haber->moneda),
                                'mes' => array(
                                    'id' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->id : "",
                                    'nombre' => $haber->mesDeTrabajo ? $haber->mesDeTrabajo->nombre : ""
                                ),
                                'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at)),
                                'tipo' => array(
                                    'id' => $haber->tipoHaber->id,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                                    'imponible' => $haber->tipoHaber->imponible ? true : false,
                                    'tributable' => $haber->tipoHaber->tributable ? true : false,
                                    'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                                    'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                                    'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false
                                ),
                                'desde' => $haber->desde,
                                'hasta' => $haber->hasta,
                                'porMes' => $haber->por_mes ? true : false,
                                'rangoMeses' => $haber->rango_meses ? true : false,
                                'permanente' => $haber->permanente ? true : false,
                                'todosAnios' => $haber->todos_anios ? true : false
                            );
                        }
                    }
                }
            }
        }
        
        return $listaHaberes;
    }
    
    public function misHaberesPermanentes()
    {
        $listaHaberes = array();
        $misHaberes = Haber::where('trabajador_id', $this->id)->where('permanente', 1)->get();
        
        if( $misHaberes->count() ){
            foreach($misHaberes as $haber){
                $monto = $haber->monto;
                if($haber->moneda=='$'){
                    $monto = (int) $monto;
                }
                $listaHaberes[] = array(
                    'id' => $haber->id,
                    'sid' => $haber->sid,
                    'moneda' => $haber->moneda,
                    'monto' => $monto,
                    'montoPesos' => Funciones::convertir($haber->monto, $haber->moneda),
                    'tipo' => array(
                        'id' => $haber->tipoHaber->id,
                        'nombre' => $haber->tipoHaber->nombre,
                        'gratificacion' => $haber->tipoHaber->gratificacion ? true : false,
                        'imponible' => $haber->tipoHaber->imponible ? true : false,
                        'tributable' => $haber->tipoHaber->tributable ? true : false,
                        'proporcional' => $haber->tipoHaber->proporcional_dias_trabajados ? true : false,
                        'horasExtra' => $haber->tipoHaber->calcula_horas_extra ? true : false,
                        'semanaCorrida' => $haber->tipoHaber->calcula_semana_corrida ? true : false
                    ),
                    'proporcional' => $haber->proporcional ? true : false
                );
            }
        }
        
        return $listaHaberes;
    }
    
    public function misVacaciones()
    {
        $mes = \Session::get('mesActivo')->mes;
        $vacaciones = Vacaciones::where('trabajador_id', $this->id)->where('mes', $mes)->first();
        
        if($vacaciones){
            return $vacaciones->dias;
        }else{
            return null;
        }
    }
    
    public function misVacacionesFiniquito()
    {
        $mes = \Session::get('mesActivo');
        $hasta = $mes->mes;
        $ficha = $this->ficha();
        $dias = $ficha->vacaciones;
        $calcularDesde = $ficha->calculo_vacaciones;                
        $i = 0;
            
        if($calcularDesde=='p'){
            $primerMes = MesDeTrabajo::orderBy('mes')->first();
            $desde = $primerMes->mes;
            if($dias==0){
                $dias = 1.25;   
            }
        }else{
            if($dias==0){
                $dias = $this->diasInicialesVacaciones();   
            }
            $desde = Funciones::primerDia($ficha->fecha_ingreso);
        }
        
        $fecha = $desde;

        while($fecha!=$hasta){            
            $fecha = date('Y-m-d', strtotime('+' . $i . ' month', strtotime($desde)));
            $diasVacaciones = $this->diasVacaciones($fecha);
            if($diasVacaciones>0){
                $dias = ($dias - $diasVacaciones);
            }
            $dias = ($dias + 1.25);
            $i++;            
        }
        
        return $dias;
    }
    
    public function provisionVacaciones()
    {
        $mes = \Session::get('mesActivo')->mes;
        $ficha = $this->ficha();
        $vacaciones = Vacaciones::where('trabajador_id', $this->id)->where('mes', $mes)->first();
        $sueldoDiario = $this->sueldoDiario();
        $sueldoBase = $this->sueldoBase();
        $tomadas = $vacaciones->sumaTomaVacaciones();
        $diasMes = 1.25;
        $factor = 1.4;
        
        $datos = array(
            'saldo' => ($tomadas + $vacaciones->dias - $diasMes),
            'dias' => $diasMes,
            'diasHabiles' => $vacaciones->dias,
            'tomadas' => $tomadas,
            'sueldoDiario' => round($sueldoDiario),
            'sueldoBase' => $sueldoBase,
            'tomadas' => $tomadas,
            'diasCorridos' => ($factor * $vacaciones->dias),
            'acumuladas' => ($tomadas + $vacaciones->dias),
            'provision' => round($sueldoDiario * $vacaciones->dias)
        );
        
        return $datos;
    }
    
    public function misVacacionesFiniquitos()
    {
        $mes = \Session::get('mesActivo');
        $hasta = $mes->mes;
        $ficha = $this->ficha();
        $dias = $ficha->vacaciones;
        $calcularDesde = $ficha->calculo_vacaciones;                
        $i = 0;
            
        if($calcularDesde=='p'){
            $primerMes = MesDeTrabajo::orderBy('mes')->first();
            $desde = $primerMes->mes;
            if($dias==0){
                $dias = 1.25;   
            }
        }else{
            if($dias==0){
                $dias = $this->diasInicialesVacaciones();   
            }
            $desde = Funciones::primerDia($ficha->fecha_ingreso);
        }
        
        $fecha = $desde;

        while($fecha!=$hasta){            
            $fecha = date('Y-m-d', strtotime('+' . $i . ' month', strtotime($desde)));
            $diasVacaciones = $this->diasVacaciones($fecha);
            if($diasVacaciones>0){
                $dias = ($dias - $diasVacaciones);
            }
            $dias = ($dias + 1.25);
            $i++;            
        }
        $datos = array(
            'dias' => $dias,
            'hasta' => $hasta,
            'calcularDesde' => $calcularDesde,
            'desde' => $desde
        );
        return $datos;
    }
    
    public function mesActualVacaciones()
    {
        $mes = \Session::get('mesActivo')->mes;
        $vacaciones = Vacaciones::where('trabajador_id', $this->id)->where('mes', $mes)->first();
        $detalleVacaciones = array();
        
        if($vacaciones){
            $detalleVacaciones = array(
                'id' => $vacaciones->id,
                'sid' => $vacaciones->sid,
                'dias' => $vacaciones->dias,
                'mes' => $vacaciones->mes,
                'nombre' => $vacaciones->miMes->nombre . ' ' . $vacaciones->miMes->anioRemuneracion->anio
            );
        }
        
        return $detalleVacaciones;
    }
    
    public function miHistorialVacaciones()
    {
        $listaVacaciones = array();
        $mes = \Session::get('mesActivo');        
        $vacaciones = Vacaciones::where('trabajador_id', $this->id)->where('mes', '<=', $mes->mes)->orderBy('mes')->get();
        
        if($vacaciones->count()){
            foreach($vacaciones as $vacacion){
                if($vacacion->miMes){
                    $listaVacaciones[] = array(
                        'id' => $vacacion->id,
                        'sid' => $vacacion->sid,
                        'mes' => array(
                            'id' => $vacacion->miMes->id,
                            'mes' => $vacacion->miMes->mes,
                            'nombre' => $vacacion->miMes->nombre . ' ' . $vacacion->miMes->anioRemuneracion->anio
                        ),
                        'dias' => $vacacion->dias,
                        'tomaVacaciones' => $vacacion->tomaVacacionesMes(),
                        'totalDevengadas' => $vacacion->totalDevengadas()
                    );
                }
            }
        }
       
        return $listaVacaciones;
    }
    
    static function calcularVacaciones($mes=null)
    {
        $trabajadores = Trabajador::all();
        if(!$mes){
            $mes = \Session::get('mesActivo');
            $finMes = $mes->fechaRemuneracion;
        }else{
            $finMes = $mes->fecha_remuneracion;
        }
        
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        Vacaciones::calcularVacaciones($trabajador, $empleado, $mes);   
                    }
                }
            }
        }
        
        return;
    }
    
    public function diasInicialesVacaciones()
    {
        $dias = 1.25;
        $ficha = $this->ficha();
        $m = date('m', strtotime('-' . 1 . ' month', strtotime($ficha->fecha_ingreso)));
        $y = date('Y', strtotime('-' . 1 . ' month', strtotime($ficha->fecha_ingreso)));
        $fecha = Funciones::obtenerFechaRemuneracionMes($m, $y);
        $diasMes = (int) date('d', strtotime($fecha));
        $diaIngreso = (( (int) date('d', strtotime($ficha->fecha_ingreso) )) - 1);
        $diasTrabajados = ($diasMes - $diaIngreso);
        if($diasTrabajados > 30){
            $diasTrabajados = 30;
        }
        $dias = (($dias / 30) * $diasTrabajados);
        
        return $dias;
    }
    
    public function recalcularVacaciones($dias, $desde=null)
    {
        $mes = \Session::get('mesActivo');
        $id = $this->id;       
        $fichas = FichaTrabajador::where('trabajador_id', $id)->orderBy('fecha', 'DESC')->first();        
        $fin = MesDeTrabajo::orderBy('mes', 'DESC')->first();
        
        if($desde){
            /*$mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($desde)));
            $dias = (Vacaciones::where('trabajador_id', $id)->where('mes', $mesAnterior)->first()['dias'] + 1.25);*/
            $inicio = $desde;
            DB::table('vacaciones')->where('trabajador_id', $id)->delete();
        }else{
            $inicio = $fichas['fecha_ingreso'];          
            DB::table('vacaciones')->where('trabajador_id', $id)->delete();
        }        
        
        if(count($fichas)){            
            $inicio = Funciones::primerDia($inicio);
            $this->recalcularMisVacaciones($inicio, $fin->mes, $dias);
            
            return;
        }
        
        return;
    }
    
    public function diasVacaciones($mes)
    {
        $dias = TomaVacaciones::where('trabajador_id', $this->id)->where('mes', $mes)->get();
        $diasVacaciones = 0;
        
        if($dias->count()){
            foreach($dias as $dia){
                $diasVacaciones = ($diasVacaciones + $dia->dias);        
            }
        }
        
        return $diasVacaciones;
    }
    
    public function recalcularMisVacaciones($desde, $hasta, $dias)
    {
        $i = 0;
        $fecha = date('Y-m-d', strtotime('+' . $i . ' month', strtotime($desde)));
        $primerMes = MesDeTrabajo::orderBy('mes')->first();
        do{            
            $fecha = date('Y-m-d', strtotime('+' . $i . ' month', strtotime($desde)));
            $diasVacaciones = $this->diasVacaciones($fecha);
            if($diasVacaciones>0){
                $dias = ($dias - $diasVacaciones);
            }
            if(true){
                $vacaciones = new Vacaciones();
                $vacaciones->sid = Funciones::generarSID();
                $vacaciones->trabajador_id = $this->id;
                $vacaciones->mes = $fecha;
                $vacaciones->dias = $dias;
                $vacaciones->save();
                $dias = ($dias + 1.25);
            }
            $i++;
            
        }while($fecha!=$hasta); 
        
        return $dias;
    }
    
    static function crearSemanasCorridas($mes=null)
    {
        $trabajadores = Trabajador::all();
        if(!$mes){
            $mes = \Session::get('mesActivo');
            $finMes = $mes->fechaRemuneracion;
        }else{
            $finMes = $mes->fecha_remuneracion;
        }
        
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $trabajador->crearSemanaCorrida($mes);   
                    }
                }
            }
        }
        
        return;
    }
    
    public function crearSemanaCorrida($mes=null)
    {
        $id = $this->id;
        if(!$mes){
            $mes = \Session::get('mesActivo');
        }
        $semanaCorrida = SemanaCorrida::where('trabajador_id', $id)->where('mes', $mes->mes)->first();
        if(!$semanaCorrida){
            $semanaCorrida = new SemanaCorrida();
            $semanaCorrida->sid = Funciones::generarSID();
            $semanaCorrida->trabajador_id = $id;
            $semanaCorrida->mes = $mes->mes;
            $semanaCorrida->semana_1 = 0;
            $semanaCorrida->semana_2 = 0;
            $semanaCorrida->semana_3 = 0;
            $semanaCorrida->semana_4 = 0;
            $semanaCorrida->semana_5 = 0;
            $semanaCorrida->save();
        }
        
        return;
    }

    public function misDescuentos()
    {        
        if( !$this->miMisDescuentos ){
            $idTrabajador = $this->id;
            $listaDescuentos = array();
            $idMes = \Session::get('mesActivo')->id;
            $mes = \Session::get('mesActivo')->mes;
            
            $misDescuentos = Descuento::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)
                ->orWhere('permanente', 1)->where('trabajador_id', $idTrabajador)
                ->orWhere('hasta', '>=', $mes)->where('trabajador_id', $idTrabajador)->get();

            if( $misDescuentos->count() ){
                foreach($misDescuentos as $descuento){
                    if($descuento->permanente && !$descuento->desde && !$descuento->hasta 
                       || $descuento->permanente && !$descuento->desde && $descuento->hasta && $descuento->hasta >= $mes 
                       || $descuento->permanente && !$descuento->hasta && $descuento->desde && $descuento->desde <= $mes 
                       || $descuento->permanente && $descuento->desde && $descuento->desde <= $mes && $descuento->hasta && $descuento->hasta >= $mes 
                       || !$descuento->permanente){
                                                
                        if($descuento->tipoDescuento->estructuraDescuento->id==3){                        
                            $nombre = 'APVC AFP ' . $descuento->tipoDescuento->nombreAfp();
                        }else if($descuento->tipoDescuento->estructuraDescuento->id==7){                        
                            $nombre = 'Cuenta de Ahorro AFP ' . $descuento->tipoDescuento->nombreAfp();
                        }else{                    
                            $nombre = $descuento->tipoDescuento->nombre;
                        }
                        $listaDescuentos[] = array(
                            'id' => $descuento->id,
                            'sid' => $descuento->sid,
                            'moneda' => $descuento->moneda,
                            'monto' => $descuento->monto,
                            'montoPesos' => Funciones::convertir($descuento->monto, $descuento->moneda),
                            'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at)),
                            'tipo' => array(
                                'id' => $descuento->tipoDescuento->id,
                                'nombre' => $nombre,
                                'idCuenta' => $descuento->tipoDescuento->cuenta_id,
                                'estructura' => array(
                                    'id' => $descuento->tipoDescuento->estructuraDescuento->id,
                                    'nombre' => $descuento->tipoDescuento->estructuraDescuento->nombre
                                ),
                                'afp' => array(
                                    'id' => $descuento->tipoDescuento->afp_id ? $descuento->tipoDescuento->afp->id : '',
                                    'nombre' => $descuento->tipoDescuento->afp_id ? $descuento->tipoDescuento->afp->glosa : ''
                                ),
                                'formaPago' => array(
                                    'id' => $descuento->tipoDescuento->forma_pago ? $descuento->tipoDescuento->formaPago->id : '',
                                    'nombre' => $descuento->tipoDescuento->forma_pago ? $descuento->tipoDescuento->formaPago->glosa : ''
                                )
                            ),
                            'porMes' => $descuento->por_mes ? true : false,
                            'rangoMeses' => $descuento->rango_meses ? true : false,
                            'permanente' => $descuento->permanente ? true : false
                        );
                    }
                }
            }
            $this->miMisDescuentos = $listaDescuentos;
        }
        
        return $this->miMisDescuentos;
    }
        
    public function todosMisDescuentos()
    {        
        $listaDescuentos = array();
        $misDescuentos = $this->descuentos;
                
        if( $misDescuentos->count() ){
            foreach($misDescuentos as $descuento){
                if($descuento->tipoDescuento->estructuraDescuento->id==3){                        
                    $nombre = 'APVC AFP ' . $descuento->tipoDescuento->nombreAfp();
                }else if($descuento->tipoDescuento->estructuraDescuento->id==7){                        
                    $nombre = 'Cuenta de Ahorro AFP ' . $descuento->tipoDescuento->nombreAfp();
                }else{                    
                    $nombre = $descuento->tipoDescuento->nombre;
                }
                $listaDescuentos[] = array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'moneda' => $descuento->moneda,
                    'monto' => $descuento->monto,
                    'montoPesos' => Funciones::convertir($descuento->monto, $descuento->moneda),
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at)),
                    'tipo' => array(
                        'id' => $descuento->tipoDescuento->id,
                        'nombre' => $nombre,
                        'idCuenta' => $descuento->tipoDescuento->cuenta_id,
                        'estructura' => array(
                            'id' => $descuento->tipoDescuento->estructuraDescuento->id,
                            'nombre' => $descuento->tipoDescuento->estructuraDescuento->nombre
                        ),
                        'afp' => array(
                            'id' => $descuento->tipoDescuento->afp_id ? $descuento->tipoDescuento->afp->id : '',
                            'nombre' => $descuento->tipoDescuento->afp_id ? $descuento->tipoDescuento->afp->glosa : ''
                        ),
                        'formaPago' => array(
                            'id' => $descuento->tipoDescuento->forma_pago ? $descuento->tipoDescuento->formaPago->id : '',
                            'nombre' => $descuento->tipoDescuento->forma_pago ? $descuento->tipoDescuento->formaPago->glosa : ''
                        )
                    ),
                    'mes' => $descuento->mes ? Funciones::obtenerMesAnioTextoAbr($descuento->mes) : '',
                    'desde' => $descuento->desde ? Funciones::obtenerMesAnioTextoAbr($descuento->desde) : '',
                    'hasta' => $descuento->hasta ? Funciones::obtenerMesAnioTextoAbr($descuento->hasta) : '',
                    'porMes' => $descuento->por_mes ? true : false,
                    'rangoMeses' => $descuento->rango_meses ? true : false,
                    'permanente' => $descuento->permanente ? true : false
                );
            }
        }
        
        return $listaDescuentos;
    }
    
    public function prestamosCaja()
    {
        $prestamos = $this->misCuotasPrestamo();
        $caja = 0;
        $leasing = 0;
        $seguro = 0;
        $dental = 0;
        $otros = 0;
        $cargas = 0;
        
        if(count($prestamos)){
            foreach($prestamos as $prestamo){
                if($prestamo['prestamoCaja']){
                    $caja = ($caja + $prestamo['montoCuotaPagar']);
                }else if($prestamo['leasingCaja']){
                    $leasing = ($leasing + $prestamo['montoCuotaPagar']);
                }
            }
        }
        
        $descuentosCaja = $this->misDescuentos();
            
        if(count($descuentosCaja)){
            foreach($descuentosCaja as $descuento){
                if($descuento['tipo']['estructura']['id']==6){
                    if($descuento['tipo']['nombre']=='Créditos Personales CCAF'){
                        $caja = ($caja + $descuento['montoPesos']);
                    }else if($descuento['tipo']['nombre']=='Descuento por Leasing CCAF'){
                        $leasing = ($leasing + $descuento['montoPesos']);
                    }else if($descuento['tipo']['nombre']=='Descuento por seguro de vida CCAF'){
                        $seguro = ($seguro + $descuento['montoPesos']);
                    }else if($descuento['tipo']['nombre']=='Descuento Dental CCAF'){
                        $dental = ($dental + $descuento['montoPesos']);
                    }else if($descuento['tipo']['nombre']=='Otros Descuentos CCAF'){
                        $otros = ($otros + $descuento['montoPesos']);
                    }
                }
            }
        }
        
        $datos = array(
            'caja' => $caja,
            'leasing' => $leasing,
            'seguro' => $seguro,
            'dental' => $dental,
            'otros' => $otros,
            'cargas' => $cargas
        );
        
        return $datos;            
    }
    
    public function misCuotasPrestamo()
    {        
        $idTrabajador = $this->id;
        $listaPrestamos = array();
        $mes = \Session::get('mesActivo')->mes;
        $misPrestamos = Prestamo::where('trabajador_id', $idTrabajador)->where('primera_cuota', '<=', $mes)->where('ultima_cuota', '>=', $mes)->get();
        
        if( $misPrestamos->count() ){
            foreach($misPrestamos as $prestamo){
                $cuotaPagar = $prestamo->cuotaPagar();
                $listaPrestamos[] = array(
                    'id' => $prestamo->id,
                    'sid' => $prestamo->sid,
                    'moneda' => $prestamo->moneda,
                    'monto' => $prestamo->monto,
                    'codigo' => $prestamo->codigo,
                    'glosa' => $prestamo->glosa,
                    'nombreLiquidacion' => $prestamo->nombre_liquidacion,
                    'cuotas' => $prestamo->cuotas,
                    'codigo' => $prestamo->codigo,
                    'primeraCuota' => $prestamo->primera_cuota,
                    'ultimaCuota' => $prestamo->ultima_cuota,
                    'prestamoCaja' => $prestamo->prestamo_caja ? true : false,
                    'leasingCaja' => $prestamo->leasing_caja ? true : false,
                    'numeroCuotaPagar' => $cuotaPagar->numero,
                    'montoCuotaPagar' => $cuotaPagar->monto
                );
            }
        }
        
        return $listaPrestamos;
    }
    
    public function misDescuentosPermanentes()
    {        
        $listaDescuentos = array();
        $misDescuentos = Descuento::where('trabajador_id', $this->id)->where('permanente', 1)->get();
        
        if( $misDescuentos->count() ){
            foreach($misDescuentos as $descuento){
                $listaDescuentos[] = array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'moneda' => $descuento->moneda,
                    'monto' => $descuento->monto,
                    'montoPesos' => Funciones::convertir($descuento->monto, $descuento->moneda),
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at)),
                    'tipo' => array(
                        'id' => $descuento->tipoDescuento->id,
                        'nombre' => $descuento->tipoDescuento->nombre
                    ) 
                );
            }
        }
        
        return $listaDescuentos;
    }            
    
    public function misPrestamos()
    {        
        $misPrestamos = $this->prestamos;
        $listaPrestamos = array();
        
        if( $misPrestamos ){
            foreach($misPrestamos as $prestamo){
                $listaPrestamos[] = array(
                    'id' => $prestamo->id,
                    'created_at' => date('Y-m-d H:i:s', strtotime($prestamo->created_at)),
                    'glosa' => $prestamo->glosa,
                    'codigo' => $prestamo->codigo,
                    'nombreLiquidacion' => $prestamo->nombre_liquidacion,
                    'monto' => $prestamo->monto,
                    'cuotas' => $prestamo->cuotas
                );
            }
        }
        
        return $listaPrestamos;
    }        

    public function totalInasistencias()
    {        
        $totalDias = 0;
        $idMes = \Session::get('mesActivo')->id;
        $inasistencias = Inasistencia::where('trabajador_id', $this->id)->where('mes_id', $idMes)->get();
        
        if($inasistencias->count()){
            foreach($inasistencias as $inasistencia){
                $totalDias = $totalDias + $inasistencia->dias;
            }
        }
        
        return $totalDias;
    }
    
    public function misInasistencias()
    {        
        $idTrabajador = $this->id;
        $idMes = \Session::get('mesActivo')->id;
        $inasistencias = Inasistencia::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)->get();
        $listaInasistencias = array();
        
        if($inasistencias->count()){
            foreach($inasistencias as $inasistencia){
                $listaInasistencias[] = array(
                    'id' => $inasistencia->id,
                    'sid' => $inasistencia->sid,
                    'idTrabajador' => $inasistencia->trabajador_id,
                    'idMes' => $inasistencia->mes_id,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($inasistencia->created_at)),
                    'desde' => $inasistencia->desde,
                    'hasta' => $inasistencia->hasta,
                    'dias' => $inasistencia->dias,
                    'motivo' => $inasistencia->motivo,
                    'observacion' => $inasistencia->observacion
                );
            }
        }
        
        return $listaInasistencias;
    }
    
    public function totalAtrasos()
    {        
        $total = 0;
        $cantidadAtrasos = 0;
        $totalMinutos = 0;
        $horas = 0;
        $minutos = 0;
        $mes = \Session::get('mesActivo');
        $atrasos = Atraso::where('trabajador_id', $this->id)->where('fecha', '>=', $mes->mes)->where('fecha', '<=', $mes->fechaRemuneracion)->get();
        
        if($atrasos->count()){
            $cantidadAtrasos = $atrasos->count();
            foreach($atrasos as $atraso){
                $minutos += $atraso->minutos;
                $horas += $atraso->horas;
            }            
            if($minutos>59){
                $horas += (int) ($minutos / 60);
                $minutos = ($minutos % 60);
            }
            $totalMinutos = ($minutos + ($horas * 60));
            $total = date('H:i', mktime($horas,$minutos));
        }
        
        $datos = array(
            'total' => $total,
            'atrasos' => $cantidadAtrasos,
            'minutos' => $totalMinutos
        );
        
        return $datos;
    }
    
    public function misAtrasos()
    {        
        $idTrabajador = $this->id;
        $mes = \Session::get('mesActivo');
        $atrasos = Atraso::where('trabajador_id', $this->id)->where('fecha', '>=', $mes->mes)->where('fecha', '<=', $mes->fechaRemuneracion)->get();
        $listaAtrasos = array();
        
        if($atrasos->count()){
            foreach($atrasos as $atraso){
                $listaAtrasos[] = array(
                    'id' => $atraso->id,
                    'sid' => $atraso->sid,
                    'idTrabajador' => $atraso->trabajador_id,
                    'fecha' => $atraso->fecha,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($atraso->created_at)),
                    'horas' => $atraso->horas,
                    'minutos' => $atraso->minutos,
                    'total' => date('H:i', mktime($atraso->horas,$atraso->minutos)),
                    'observacion' => $atraso->observacion
                );
            }
        }
        
        return $listaAtrasos;
    }
        
    public function totalLicencias()
    {
        $totalLicencias = 0;
        $idMes = \Session::get('mesActivo')->id;
        $licencias = Licencia::where('trabajador_id', $this->id)->where('mes_id', $idMes)->get();
        
        if($licencias->count()){
            $totalLicencias = $licencias->count();
        }
        
        return $totalLicencias;
    }
    
    public function totalDiasLicencias()
    {
        $totalDiasLicencias = 0;
        $idMes = \Session::get('mesActivo')->id;
        $licencias = Licencia::where('trabajador_id', $this->id)->where('mes_id', $idMes)->get();
        $ficha = $this->ficha();
        
        if($licencias->count()){
            if($ficha->estado=='Finiquitado'){
                $fechaFiniquito = $ficha->fecha_finiquito;
                foreach($licencias as $licencia){
                    if($fechaFiniquito > $licencia->hasta){
                        $totalDiasLicencias += $licencia->dias;                    
                    }else{
                        
                    }
                }
            }else{
                foreach($licencias as $licencia){
                    $totalDiasLicencias += $licencia->dias;
                }
            }   
        }
        
        return $totalDiasLicencias;
    }
    
    public function misLicencias()
    {        
        $idTrabajador = $this->id;
        $idMes = \Session::get('mesActivo')->id;
        $licencias = Licencia::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)->get();
        $listaLicencias = array();
        
        if($licencias->count()){
            foreach($licencias as $licencia){
                $listaLicencias[] = array(
                    'id' => $licencia->id,
                    'sid' => $licencia->sid,
                    'idTrabajador' => $licencia->trabajador_id,
                    'idMes' => $licencia->mes_id,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($licencia->created_at)),
                    'desde' => $licencia->desde,
                    'hasta' => $licencia->hasta,
                    'dias' => $licencia->dias,
                    'codigo' => $licencia->codigo,
                    'observacion' => $licencia->observacion
                );
            }
        }
        
        return $listaLicencias;
    }
    
    public function totalPrestamos()
    {
        $prestamos = $this->prestamos;
        $totalPrestamos = 0;
        if($prestamos->count()){
            $totalPrestamos = $prestamos->count();
        }
        return $totalPrestamos;
    }
    
    public function totalCuotasPrestamos()
    {
        $cuotas = $this->misCuotasPrestamo();
        $totalCuotasPrestamos = 0;
        if($cuotas){
            foreach($cuotas as $cuota){
                $totalCuotasPrestamos += $cuota['montoCuotaPagar'];
            }
        }
        
        return $totalCuotasPrestamos;
    }
    
    public function totalHaberes()
    {
        $totalHaberes = 0.00;
        $totalHaberes = ($this->totalImponibles() + $this->noImponibles());
        
        return $totalHaberes;
    }
    
    public function totalHorasExtra()
    {
        $totalHorasExtra = 0.00;
        $idMes = \Session::get('mesActivo')->id;
        $horasExtra = HoraExtra::where('trabajador_id', $this->id)->where('mes_id', $idMes)->get();
        
        if($horasExtra->count()){
            foreach($horasExtra as $horaExtra){
                $totalHorasExtra += $horaExtra->cantidad;
            }
        }
        
        return $totalHorasExtra;
    }
    
    public function sueldoCalcularHorasExtra()
    {
        $sueldo = $this->sueldoBase();
        $haberes = $this->haberesCalculaHorasExtra();
        
        return ($sueldo + $haberes);
    }
    
    public function haberesCalculaHorasExtra()
    {
        $calculaHorasExtra = 0;
        $misHaberes = $this->misHaberes();
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if($haber['tipo']['horasExtra']){
                    $monto = Funciones::convertir($haber['monto'], $haber['moneda']);                 
                    $calculaHorasExtra += $monto;
                }
            }
        }
        
        return $calculaHorasExtra;
    }
    
    public function horasExtraPagar()
    {
        $idTrabajador = $this->id;
        $cantidad = $this->totalHorasExtra();
        $factor = 0;
        $total = 0;
        if($cantidad>0){
            $idMes = \Session::get('mesActivo')->id;
            $horasExtra = HoraExtra::where('trabajador_id', $this->id)->where('mes_id', $idMes)->get();
            $sueldo = $this->sueldoCalcularHorasExtra();
            foreach($horasExtra as $horaExtra){
                $factor = $horaExtra->factor;
                $total += (($sueldo * $factor) * $horaExtra->cantidad);    
            }
            
        }
        
        $datos = array(
            'cantidad' => $cantidad,
            'factor' => $factor,
            'total' => round($total)
        );
        
        return $datos;
    }
    
    public function misHorasExtra()
    {        
        $idTrabajador = $this->id;
        $idMes = \Session::get('mesActivo')->id;
        $horasExtra = HoraExtra::where('trabajador_id', $idTrabajador)->where('mes_id', $idMes)->get();
        $listaHorasExtra = array();
        
        if($horasExtra->count())
        {
            foreach($horasExtra as $horaExtra)
            {
                $listaHorasExtra[] = array(
                    'id' => $horaExtra->id,
                    'sid' => $horaExtra->sid,
                    'idTrabajador' => $horaExtra->trabajador_id,
                    'idMes' => $horaExtra->mes_id,
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($horaExtra->created_at)),
                    'fecha' => $horaExtra->fecha,
                    'factor' => $horaExtra->factor,
                    'cantidad' => $horaExtra->cantidad,
                    'observacion' => $horaExtra->observacion
                );
            }
        }
        
        return $listaHorasExtra;
    }        
    
    public function totalCartasNotificacion()
    {
        $totalCartasNotificacion = 0;
        $cartasNotificacion = $this->cartasNotificacion;
        
        if($cartasNotificacion->count()){
            $totalCartasNotificacion = $cartasNotificacion->count();
        }
        
        return $totalCartasNotificacion;
    }
    
    public function totalDocumentos()
    {
        $totalDocumentos = 0;
        $documentos = $this->documentos;
        
        if($documentos){
            $totalDocumentos = $documentos->count();
        }
        
        return $totalDocumentos;
    }
    
    public function totalCertificados()
    {
        $totalCertificados = 0;
        $certificados = $this->certificados;
        
        if($certificados->count()){
            $totalCertificados = $certificados->count();
        }
        
        return $totalCertificados;
    }
    
    public function misCertificados()
    {        
        $idTrabajador = $this->id;
        $certificados = Certificado::where('trabajador_id', $idTrabajador)->get();
        $listaCertificados = array();
        
        if($certificados->count()){
            foreach($certificados as $certificado){
                $listaCertificados[] = array(
                    'id' => $certificado->id,
                    'sid' => $certificado->sid,
                    'documento' => array(
                        'id' => $certificado->documento->id,
                        'sid' => $certificado->documento->sid,
                        'alias' => $certificado->documento->alias,
                        'nombre' => $certificado->documento->nombre
                    ),
                    'fecha' => $certificado->fecha,
                    'nombre' => $certificado->documento->nombre,
                    'tipo' => array(
                        'id' => $certificado->plantillaCertificado->id,
                        'nombre' => $certificado->plantillaCertificado->nombre
                    )
                );
            }
        }
        
        return $listaCertificados;
    }
    
    public function misCartasNotificacion()
    {        
        $idTrabajador = $this->id;
        $cartasNotificacion = CartaNotificacion::where('trabajador_id', $idTrabajador)->get();
        $listaCartasNotificacion = array();
        
        if($cartasNotificacion->count()){
            foreach($cartasNotificacion as $cartaNotificacion){
                $listaCartasNotificacion[] = array(
                    'id' => $cartaNotificacion->id,
                    'sid' => $cartaNotificacion->sid,
                    'fecha' => $cartaNotificacion->fecha,
                    'documento' => array(
                        'id' => $cartaNotificacion->documento->id,
                        'sid' => $cartaNotificacion->documento->sid,
                        'alias' => $cartaNotificacion->documento->alias,
                        'nombre' => $cartaNotificacion->documento->nombre
                    ),
                    'tipo' => array(
                        'id' => $cartaNotificacion->plantillaCartaNotificacion->id,
                        'nombre' => $cartaNotificacion->plantillaCartaNotificacion->nombre
                    )
                );
            }
        }
        
        return $listaCartasNotificacion;
    }
    
    public function misContratos()
    {        
        $idTrabajador = $this->id;
        $contratos = Documento::where('trabajador_id', $idTrabajador)->where('tipo_documento_id', 1)->get();
        $listaContratos = array();
        
        if($contratos->count()){
            foreach($contratos as $contrato){
                $listaContratos[] = array(
                    'id' => $contrato->id,
                    'sid' => $contrato->sid,
                    'nombre' => $contrato->nombre,
                    'alias' => $contrato->alias,
                    'descripcion' => $contrato->descripcion ? $contrato->descripcion : "",
                    'fecha' => date('Y-m-d H:i:s', strtotime($contrato->created_at)),
                    'tipo' => array(
                        'id' => $contrato->tipoDocumento->id,
                        'sid' => $contrato->tipoDocumento->sid,
                        'nombre' => $contrato->tipoDocumento->nombre
                    )
                );
            }
        }
        
        return $listaContratos;
    }
    
    public function misFichas()
    {        
        $fichas = $this->fichaTrabajador;
        $listaFichas = array();
        
        if($fichas->count()){
            foreach($fichas as $key => $ficha){
                $desde = $ficha->fecha;
                $hasta = $ficha->hasta($fichas);
                $listaFichas[] = array(
                    'id' => $ficha->id,
                    'fechaDesde' => $ficha->fecha,
                    'fechaHasta' => $hasta,
                    'desde' => Funciones::obtenerMesAnioTextoAbr($desde),
                    'hasta' => $hasta ? Funciones::obtenerMesAnioTextoAbr($hasta) : '∞',
                    'periodo' => $ficha->periodo($key, $desde, $hasta),
                    'fechaCreacion' => date('Y-m-d H:i:s', strtotime($ficha->created_at)),
                    'estado' => $ficha->estado
                );
            }
        }
        
        return $listaFichas;
    }
    
    public function misDocumentos()
    {        
        $idTrabajador = $this->id;
        $documentos = Documento::where('trabajador_id', $idTrabajador)->get();
        $listaDocumentos = array();
        
        if($documentos->count()){
            foreach($documentos as $documento){
                $listaDocumentos[] = array(
                    'id' => $documento->id,
                    'sid' => $documento->sid,
                    'nombre' => $documento->nombre,
                    'alias' => $documento->alias,
                    'descripcion' => $documento->descripcion ? $documento->descripcion : "",
                    'fecha' => date('Y-m-d H:i:s', strtotime($documento->created_at)),
                    'extension' =>$documento->extension(),
                    'tipo' => array(
                        'id' => $documento->tipoDocumento->id,
                        'sid' => $documento->tipoDocumento->sid,
                        'nombre' => $documento->tipoDocumento->nombre
                    )
                );
            }
        }
        
        return $listaDocumentos;
    }
    
    public function calcularMisVacaciones($fechaReconocimiento)
    {
        $idTrabajador = $this->id;
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $idMes = $mes->id;
        $fechaReconocimiento = new DateTime($fechaReconocimiento);
        $fecha = new DateTime($finMes);
        $diff = $fechaReconocimiento->diff($fecha);
        $meses = (($diff->y * 12) + $diff->m);
        $vacas = ($meses * 1.25);
        
        $vacaciones = new Vacaciones();
        $vacaciones->sid = Funciones::generarSID();
        $vacaciones->trabajador_id = $idTrabajador;
        $vacaciones->mes = $mes->mes;
        $vacaciones->dias = $vacas;
        $vacaciones->save(); 

        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'vacaciones' => $vacas
        );
        
        return Response::json($respuesta);
    }
    
    public function asignarVacaciones($dias=0)
    {
        $idTrabajador = $this->id;
        $mes = \Session::get('mesActivo')->mes;        
        
        $vacaciones = new Vacaciones();
        $vacaciones->sid = Funciones::generarSID();
        $vacaciones->trabajador_id = $idTrabajador;
        $vacaciones->mes = $mes;
        $vacaciones->dias = $dias;
        $vacaciones->save(); 

        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'vacaciones' => $vacaciones
        );
        
        return Response::json($respuesta);
    }
    
    public function misFiniquitos()
    {        
        $idTrabajador = $this->id;
        $finiquitos = Finiquito::where('trabajador_id', $idTrabajador)->get();
        $listaFiniquitos = array();
        
        if($finiquitos->count()){
            foreach($finiquitos as $finiquito){
                $listaFiniquitos[] = array(
                    'id' => $finiquito->id,
                    'sid' => $finiquito->sid,
                    'fecha' => $finiquito->fecha,
                    'causal' => array(
                        'id' => $finiquito->causalFiniquito->id,
                        'sid' => $finiquito->causalFiniquito->sid,
                        'codigo' => $finiquito->causalFiniquito->codigo,
                        'articulo' => $finiquito->causalFiniquito->articulo,
                        'nombre' => $finiquito->causalFiniquito->nombre
                    ),
                    'documento' => array(
                        'id' => $finiquito->documento->id,
                        'sid' => $finiquito->documento->sid
                    ),
                    'vacaciones' => $finiquito->vacaciones ? true : false,            
                    'sueldo_normal' => $finiquito->sueldo_normal ? true : false,            
                    'sueldo_variable' => $finiquito->sueldo_variable ? true : false,            
                    'mes_aviso' => $finiquito->mes_aviso ? true : false,            
                    'indemnizacion' => $finiquito->indemnizacion ? true : false,
                    'recibido' => $finiquito->recibido ? true : false
                );
            }
        }
        
        return $listaFiniquitos;
    }
    
    public function finiquitar($fecha)
    {
        $mesActual = \Session::get('mesActivo');  
        $idMes = $mesActual->id;  
        $mes = $mesActual->mes;  
        $ficha = $this->ficha();
        
        if($ficha->mes_id!=$idMes){
            $id = (FichaTrabajador::orderBy('id', 'DESC')->first()->id + 1);
            $nuevaFicha = new FichaTrabajador();
            $nuevaFicha = $ficha->replicate();
            $nuevaFicha->id = $id;
            $nuevaFicha->mes_id = $idMes;
            $nuevaFicha->fecha = $mes;
            $nuevaFicha->fecha_finiquito = $fecha;
            $nuevaFicha->estado = 'Finiquitado';
            $nuevaFicha->save(); 
        }else{
            $ficha->fecha_finiquito = $fecha;
            $ficha->estado = 'Finiquitado';
            $ficha->save(); 
        }
        
        return true;
    }
    
    public function isContrato()
    {
        return true;
    }
    //  Liquidación de Sueldo Trabajadores
    
    public function topeGratificacion()
    {    
        $mes = \Session::get('mesActivo');
        $empresa = \Session::get('empresa');
        $factorIMM = $empresa->tope_gratificacion;
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
        $rentaMin = RentaMinimaImponible::where('mes', $fecha)->where('nombre', 'Trab. Dependientes e Independientes')->first();
        if( $rentaMin ){
            $rmi = $rentaMin->valor;
            $tope = (( $factorIMM * $rmi ) / 12 );
        }else{
            $tope = 0;
        }
        
        return round($tope);
    }
    
    public function tramosHorasExtra()
    {
        $ficha = $this->ficha();
        $jornada = $ficha->tipoJornada;
        $tramos = array();
        
        if($jornada){
            $tramos = $jornada->tramos();
        }
        
        return $tramos;
    }
    
    public function horasJornada()
    {
        $horas = 0;
        $ficha = $this->ficha();
        $jornada = $ficha->tipoJornada;
        if($jornada){
            $horas = $jornada->numero_horas;
        }
        
        return $horas;
    }
    
    public function diasTrabajados()
    {     
        $mes = \Session::get('mesActivo');
        $empresa = \Session::get('empresa');
        $empleado = $this->ficha();
        $diasTrabajados = 30;
        $diaIngreso = 0;
        $diaFiniquito = 0;
        $inasistencias = $this->totalInasistencias();
        $licencias = $this->totalDiasLicencias();
        $licencias30 = $empresa->licencias_30 ? true : false;
        $ingreso30 = $empresa->ingresos_30 ? true : false;
        $finiquito30 = $empresa->finiquitos_30 ? true : false;    
        $diasMes = (int) date('d', strtotime($mes->fechaRemuneracion));

        if($empleado->fecha_ingreso>$mes->mes){
            $diasTrabajados = $diasMes;
            $diaIngreso = (( (int) date('d', strtotime($empleado->fecha_ingreso) )) - 1);
            $diasTrabajados -= $diaIngreso;
        }
        
        if($empleado->fecha_finiquito){
            if($empleado->fecha_finiquito<$mes->mes){
                return 0;
            }else{                
                $diaFiniquito = (int) date('d', strtotime($empleado->fecha_finiquito));
                $diasTrabajados = $diaFiniquito;
            }
        }
        
        if($licencias == $diasMes || $inasistencias == $diasMes){
            return 0;
        }
        
        $diasTrabajados = ($diasTrabajados - $inasistencias - $licencias);
        
        if($diasTrabajados<0){
            $diasTrabajados = 0;
        }
        
        return $diasTrabajados;
        
        /*$mes = \Session::get('mesActivo');
        $empresa = \Session::get('empresa');
        $empleado = $this->ficha();
        $diasTrabajados = 30;
        $diaIngreso = 0;
        $diaFiniquito = 0;
        $inasistencias = $this->totalInasistencias();
        $licencias = $this->totalDiasLicencias();
        $licencias30 = $empresa->licencias_30 ? true : false;
        $ingreso30 = $empresa->ingresos_30 ? true : false;
        $finiquito30 = $empresa->finiquitos_30 ? true : false;
        
        if($inasistencias>0){
            $diasMes = (int) date('d', strtotime($mes->fechaRemuneracion));
            if($inasistencias==$diasMes){
                return 0;
            }
            if($inasistencias>30){
                $inasistencias = 30;
            }
        }
        
        if($licencias>0){
            if(!$licencias30){
                $diasTrabajados = (int) date('d', strtotime($mes->fechaRemuneracion));
            }
        }

        if($empleado->fecha_ingreso>$mes->mes){
            $diaIngreso = (( (int) date('d', strtotime($empleado->fecha_ingreso) )) - 1);
            if(!$ingreso30){
                $diasTrabajados = (int) date('d', strtotime($mes->fechaRemuneracion));
            }
        }
        
        if($empleado->fecha_finiquito){
            if($empleado->fecha_finiquito<$mes->mes){
                return 0;
            }else{                
                $diaFiniquito = (int) date('d', strtotime($empleado->fecha_finiquito));
                if(!$finiquito30){
                    $diasTrabajados = (int) date('d', strtotime($mes->fechaRemuneracion));
                }
                $diaFiniquito = ($diasTrabajados - $diaFiniquito);
            }
        }
        
        $diasTrabajados = ($diasTrabajados - $inasistencias - $licencias - $diaIngreso - $diaFiniquito);
        
        if($diasTrabajados<0){
            $diasTrabajados = 0;
        }
        
        return $diasTrabajados;*/
    }
    
    public function misDiasTrabajados()
    {        
        $mes = \Session::get('mesActivo');
        $empleado = $this->ficha();
        $diasTrabajados = (int) date('d', strtotime($mes->fechaRemuneracion));
        
        if($empleado->fecha_ingreso>$mes->mes){
            $diaIngreso = (int) date('d', strtotime($empleado->fecha_ingreso));
            $diasTrabajados = $diasTrabajados - ($diaIngreso - 1);
        }
        
        if($empleado->fecha_finiquito){
            if($empleado->fecha_finiquito<$mes->mes){
                return 0;
            }
            $diasTrabajados = (int) date('d', strtotime($empleado->fecha_finiquito));
        }
        
        $diasTrabajados = ($diasTrabajados - $this->totalInasistencias() - $this->totalDiasLicencias());
        
        return $diasTrabajados;
    }
    
    public function diasDescontados()
    {
        $mes = \Session::get('mesActivo');
        $empleado = $this->ficha();
        $diasDescontados = 0;
        $diasMes = (int) date('d', strtotime($mes->fechaRemuneracion));
        $sueldoDiario = $this->sueldoDiario();
        
        if($empleado->fecha_ingreso>$mes->mes){
            $diaIngreso = (int) date('d', strtotime($empleado->fecha_ingreso));
            $diasDescontados += ($diaIngreso - 1);
        }
        
        if($empleado->fecha_finiquito){
            $diaIngreso = (int) date('d', strtotime($empleado->fecha_finiquito));
            $diasDescontados += ($diasMes - $diaIngreso);
        }
        
        $diasDescontados += ($this->totalInasistencias() + $this->totalDiasLicencias());
        $monto = ($sueldoDiario * $diasDescontados);
        
        if($diasDescontados>30){
            $diasDescontados=30;
        }
        
        $datos = array(
            'dias' => $diasDescontados,
            'diasCalendario' => $diasDescontados,
            'monto' => $monto
        );
        
        return $datos;
    }
    
    public function sueldoDiario()
    {
        $sueldoBase = Funciones::convertir($this->ficha()->sueldo_base, $this->ficha()->moneda_sueldo);
        $sueldo_diario = ($sueldoBase / 30);
        
        return $sueldo_diario;
    }
    
    public function descuentoAtrasos()
    {
        $totalAtrasos = $this->totalAtrasos();
        $total = 0;
        $descuento = 0;
        
        if($totalAtrasos['atrasos']>0){
            $totalMinutos = $totalAtrasos['minutos'];
            $total = $totalAtrasos['total'];
            $ficha = $this->ficha();
            $jornada = $ficha->tipoJornada;
            $horas = $jornada->numero_horas;
            $horas = ($horas * 4);
            $minutos = ($horas * 60);
            $sueldo = (($this->sueldoBase() / 30) * 28);
            $valorMinuto = ($sueldo / $minutos);
            $descuento = ($valorMinuto * $totalMinutos);            
        }
        
        $datos = array(
            'total' => $total,
            'descuento' => $descuento
        );
        
        return $datos;
    }

    public function sueldo()
    {        
        if(!$this->miSueldo){
            $diasTrabajados= $this->diasTrabajados();
            $descuentoAtrasos = $this->descuentoAtrasos()['descuento'];
            $sueldo = ($diasTrabajados * $this->sueldoDiario());
            $sueldo = ($sueldo - $descuentoAtrasos);
            $this->miSueldo = round($sueldo);   
        }
        
        return $this->miSueldo;
    }
    
    public function miSemanaCorrida()
    {     
        if(!$this->miMiSemanaCorrida){
            $semanaCorrida = $this->ficha()->semana_corrida ? true : false;
            $total = 0;
            if($semanaCorrida){
                $total = $this->totalSemanaCorrida();
            }
            $this->miMiSemanaCorrida = $total;
        }
        
        return $this->miMiSemanaCorrida;
    }
    
    public function miSemanaCorridas()
    {        
        $semanaCorrida = $this->ficha()->semana_corrida ? true : false;
        $total = 0;
        if($semanaCorrida){
            $total = $this->totalSemanaCorridas();
        }
        
        return $total;
    }
    
    public function diasDeTrabajo()
    {
        return 5;
    }
    
    public function totalSemanaCorrida()
    {        
        $semanaCorrida = $this->semanaCorrida();
        $empresa = \Session::get('empresa');
        $mes = \Session::get('mesActivo');
        $festivos = $empresa->totalFestivos();
        $id = $this->id;
        $idMes = $mes->id;
        $inasistencias = Inasistencia::where('trabajador_id', $id)->where('mes_id', $idMes)->get();
        $feriados = Feriado::feriados($mes->mes, $mes->fechaRemuneracion);
        $licencias = Licencia::where('trabajador_id', $id)->where('mes_id', $idMes)->get();
        $fechasFeriados = $this->totalFeriados($feriados);
        $fechasInasistencias = $this->totalFaltas($inasistencias);
        $fechasLicencias = $this->totalFaltas($licencias);
        $diasDeTrabajo = $this->diasDeTrabajo();

        $montoSemana1=$montoSemana2=$montoSemana3=$montoSemana4=$montoSemana5=0;
        if($diasDeTrabajo - ($fechasLicencias->semana_1 + $fechasFeriados->semana_1) > 0 ){        
            $montoSemana1 = ($semanaCorrida['semanas'][0]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_2 + $fechasFeriados->semana_2) > 0){
            $montoSemana2 = ($semanaCorrida['semanas'][1]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_3 + $fechasFeriados->semana_3) > 0){
            $montoSemana3 = ($semanaCorrida['semanas'][2]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_4 + $fechasFeriados->semana_4) > 0){
            $montoSemana4 = ($semanaCorrida['semanas'][3]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_5 + $fechasFeriados->semana_5) > 0){
            $montoSemana5 = ($semanaCorrida['semanas'][4]['comision'] / $diasDeTrabajo);
        }
        
        $semana1 = ($montoSemana1 * ($fechasFeriados->semana_1 + $fechasLicencias->semana_1 + $festivos));
        $semana2 = ($montoSemana2 * ($fechasFeriados->semana_2 + $fechasLicencias->semana_2 + $festivos));
        $semana3 = ($montoSemana3 * ($fechasFeriados->semana_3 + $fechasLicencias->semana_3 + $festivos));
        $semana4 = ($montoSemana4 * ($fechasFeriados->semana_4 + $fechasLicencias->semana_4 + $festivos));
        $semana5 = ($montoSemana5 * ($fechasFeriados->semana_5 + $fechasLicencias->semana_5 + $festivos));

        $total = ($semana1 + $semana2 + $semana3 + $semana4 + $semana5);
        
        return round($total);
    }
    
    public function totalSemanaCorridas()
    {        
        $semanaCorrida = $this->semanaCorrida();
        $empresa = \Session::get('empresa');
        $mes = \Session::get('mesActivo');
        $festivos = $empresa->totalFestivos();
        $id = $this->id;
        $idMes = $mes->id;
        $inasistencias = Inasistencia::where('trabajador_id', $id)->where('mes_id', $idMes)->get();
        $feriados = Feriado::feriados($mes->mes, $mes->fechaRemuneracion);
        $licencias = Licencia::where('trabajador_id', $id)->where('mes_id', $idMes)->get();
        $fechasFeriados = $this->totalFeriados($feriados);
        $fechasInasistencias = $this->totalFaltas($inasistencias);
        $fechasLicencias = $this->totalFaltas($licencias);
        $diasDeTrabajo = $this->diasDeTrabajo();
       

        $montoSemana1=$montoSemana2=$montoSemana3=$montoSemana4=$montoSemana5=0;
        if($diasDeTrabajo - ($fechasLicencias->semana_1 + $fechasFeriados->semana_1) > 0 ){        
            $montoSemana1 = ($semanaCorrida['semanas'][0]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_2 + $fechasFeriados->semana_2) > 0){
            $montoSemana2 = ($semanaCorrida['semanas'][1]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_3 + $fechasFeriados->semana_3) > 0){
            $montoSemana3 = ($semanaCorrida['semanas'][2]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_4 + $fechasFeriados->semana_4) > 0){
            $montoSemana4 = ($semanaCorrida['semanas'][3]['comision'] / $diasDeTrabajo);
        }
        if($diasDeTrabajo - ($fechasLicencias->semana_5 + $fechasFeriados->semana_5) > 0){
            $montoSemana5 = ($semanaCorrida['semanas'][4]['comision'] / $diasDeTrabajo);
        }

        
        $semana1 = ($montoSemana1 * ($fechasFeriados->semana_1 + $fechasLicencias->semana_1 + $festivos));
        $semana2 = ($montoSemana2 * ($fechasFeriados->semana_2 + $fechasLicencias->semana_2 + $festivos));
        $semana3 = ($montoSemana3 * ($fechasFeriados->semana_3 + $fechasLicencias->semana_3 + $festivos));
        $semana4 = ($montoSemana4 * ($fechasFeriados->semana_4 + $fechasLicencias->semana_4 + $festivos));
        $semana5 = ($montoSemana5 * ($fechasFeriados->semana_5 + $fechasLicencias->semana_5 + $festivos));

        $total = ($semana1 + $semana2 + $semana3 + $semana4 + $semana5);
        
        $datos = array(
            'semana1' => $semana1,
            'semana2' => $semana2,
            'semana3' => $semana3,
            'semana4' => $semana4,
            'semana5' => $semana5,
            'feriados' => $fechasFeriados,
            'licencias' => $fechasLicencias,
            'festivos' => $festivos,
            'montoSemana1' => $montoSemana1,
            'montoSemana2' => $montoSemana2,
            'montoSemana3' => $montoSemana3,
            'montoSemana4' => $montoSemana4,
            'montoSemana5' => $montoSemana5
        );
        
        return $datos;
    }
    
    public function totalFaltas($faltas)
    {
        $fechas = new stdClass();
        $fechas->semana_1 = 0;
        $fechas->semana_2 = 0;
        $fechas->semana_3 = 0;
        $fechas->semana_4 = 0;
        $fechas->semana_5 = 0;
        
        if($faltas->count()){
            $semanaAnterior = 0;
            $cont = 0;
            $mes = \Session::get('mesActivo');
            foreach($faltas as $falta){
                $desde = $falta['desde'];
                $hasta = $falta['hasta'];
                $inicial = (int) date('W', strtotime($mes->mes));
                $final = (int) date('W', strtotime($mes->fechaRemuneracion));
                $diaDesde = (int) date('j', strtotime($desde));
                $diaHasta = (int) date('j', strtotime($hasta));
                $resto = ($diaHasta - $diaDesde);
                for($i=0; $i<=$resto; $i++){
                    $fecha = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($desde)));
                    $semanaActual = (int) date('W', strtotime($fecha));     
                    $n = 'semana_' . (($semanaActual - $inicial) + 1);
                    if($semanaAnterior==$semanaActual){
                        $cont++;
                    }else{
                        $semanaAnterior = $semanaActual;
                        $cont = 1;
                    }
                    $fechas->$n = $cont;                
                }
            }
        }
        
        return $fechas;
    }
    
    public function totalFeriados($feriados)
    {
        $fechas = new stdClass();
        $fechas->semana_1 = 0;
        $fechas->semana_2 = 0;
        $fechas->semana_3 = 0;
        $fechas->semana_4 = 0;
        $fechas->semana_5 = 0;
        
        if(count($feriados)){
            $semanaAnterior = 0;
            $cont = 0;
            $mes = \Session::get('mesActivo');
            $inicial = (int) date('W', strtotime($mes->mes));
            $final = (int) date('W', strtotime($mes->fechaRemuneracion));
            foreach($feriados as $feriado){                
                $semanaActual = (int) date('W', strtotime($feriado));                
                $n = 'semana_' . (($semanaActual - $inicial) + 1);
                if($semanaAnterior==$semanaActual){
                    $cont++;
                }else{
                    $semanaAnterior = $semanaActual;
                    $cont = 1;
                }
                $fechas->$n = $cont;                
            }
        }
        
        return $fechas;
    }
    
    public function imponibles()
    {        
        $imponibles = 0;
        $misHaberes = $this->misHaberes();
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if($haber['tipo']['imponible']){
                    $monto = $haber['montoPesos'];                 
                    $imponibles += $monto;
                }
            }
        }
        
        return $imponibles;
    }
    
    public function otrosImponibles()
    {        
        $imponibles = 0;
        $misHaberes = $this->misHaberes();
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if($haber['tipo']['imponible']){
                    $monto = $haber['montoPesos'];                 
                    $imponibles += $monto;
                }
            }
        }
        
        return $imponibles;
    }
    
    public function sumaNoImponibles()
    {        
        $misHaberes = $this->misHaberes();
        $noImponibles = 0;
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if(!$haber['tipo']['imponible']){
                    $monto = $haber['montoPesos'];
                    $noImponibles += $monto;
                }
            }
        }
        
        return $noImponibles;
    }
    
    public function isActivo($anio)
    {
        $ficha = $this->ficha();
        if($ficha->estado!='En Creación'){
            $fechas = Funciones::obtenerRangoFechas($anio->anio);
            $periodoActual = ($ficha->fecha_ingreso <= $fechas['hasta']);        
            if($periodoActual){
                if($ficha->estado=='Finiquitado'){
                    if($ficha->fecha_finiquito<=$fechas['desde']){
                        return false;
                    }
                }
                return true;
            }
        }
        
        return false;
    }
    
    public function isActividad($anio)
    {
        $fechas = Funciones::obtenerRangoFechas($anio->anio);
        $liquidaciones = DB::table('liquidaciones')->where('trabajador_id', $this->id)->where('mes', '>=', $fechas['desde'])->where('mes', '<', $fechas['hasta'])->get();
        
        if($liquidaciones){
            return true;
        }
        
        return false;
    }
    
    public function sumaImponibles()
    {        
        $gratificacion = $this->gratificacion();
        $imponibles = $this->imponibles();
        $sueldo = $this->sueldo();
        $semanaCorrida = $this->miSemanaCorrida();
        $horasExtra = $this->horasExtraPagar()['total'];
        
        return ($imponibles + $gratificacion + $sueldo + $semanaCorrida + $horasExtra);
    }
    
    public function rentaImponible()
    {        
        if(!$this->miRentaImponible){
            $mes = \Session::get('mesActivo');
            $empleado = $this->ficha();
            $rentaImponible = $this->totalImponibles();
            $todosImponibles = $this->todosImponibles();
            $diasTrabajados = $this->diasTrabajados();

            if($empleado->prevision_id==8 || $empleado->prevision_id==10){
                $rentaImp = RentaTopeImponible::valor('Para afiliados a una AFP');

                $topeImponible = $rentaImp;

            }else if($empleado->prevision_id==9){
                $rentaImp = RentaTopeImponible::valor('Para afiliados al IPS (ex INP)');
                $topeImponible = $rentaImp;
            }
            $valorTope = Funciones::convertirUF($topeImponible);

            if($empleado->estado=='Ingresado'){
                if($diasTrabajados==0){
                    $diasMes = (int) date('d', strtotime($mes->fechaRemuneracion));
                    if($this->totalDiasLicencias()==$diasMes){
                        $rentaImponible = $this->totalImponibles();
                        if($rentaImponible > $valorTope){
                            $rentaImponible = $valorTope;
                        }
                    }   
                }else{
                    if($this->totalDiasLicencias()){
                        if($todosImponibles > $valorTope && $diasTrabajados < 30){
                            $diasTrabajados = $this->diasTrabajados();
                            $rentaImponible = (($valorTope / 30) * $diasTrabajados);
                        }else{
                            if($rentaImponible > $valorTope){
                                $rentaImponible = $valorTope;
                            }
                        }
                    }else{
                        if($rentaImponible > $valorTope){
                            $rentaImponible = $valorTope;
                        }
                    }
                }
            }else{
                if($rentaImponible > $valorTope){
                    $rentaImponible = $valorTope;
                }   
            }  
            $this->miRentaImponible = round($rentaImponible);
        }
        
        return $this->miRentaImponible;
    }
    
    public function rentaImponibleSalud()
    {        
        $mes = \Session::get('mesActivo')->mes;
        $empleado = $this->ficha();
        $rentaImponible = $this->totalImponibles();
        
        if($empleado->prevision_id==8 || $empleado->prevision_id==10){
            $topeImponible = RentaTopeImponible::valor('Para afiliados a una AFP');
        }else if($empleado->prevision_id==9){
            $topeImponible = RentaTopeImponible::valor('Para afiliados al IPS (ex INP)');
        }
        $valorTope = Funciones::convertirUF($topeImponible);

        if($rentaImponible > $valorTope){
            $rentaImponible = $valorTope;
        }
        
        return round($rentaImponible);
    }
    
    public function haberesTributables()
    {    
        $tributables = 0;
        $misHaberes = $this->haberesImponibles();
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if($haber['tipo']['tributable']){
                    $monto = Funciones::convertir($haber['monto'], $haber['moneda']);                 
                    $tributables += $monto;
                }
            }
        }
        
        return $tributables;
    }
    
    public function rentaImponibleTributable()
    {        
        $haberesTributables = $this->haberesTributables();
        $gratificacion = $this->gratificacion();
        $sueldo = $this->sueldo();
        $horasExtra = $this->horasExtraPagar()['total'];
        $semanaCorrida = $this->miSemanaCorrida();
                
        $rentaImponibleTributable = ($sueldo + $haberesTributables + $gratificacion + $horasExtra + $semanaCorrida);

        return $rentaImponibleTributable;
    }
    
    public function tasaAfp()
    {        
        $mes = \Session::get('mesActivo')->mes;
        $empleado = $this->ficha();
        $idAfp = $empleado->afp_id;
        $empresa = \Session::get('empresa');
        $sis = $empresa->sis ? true : false;
        $socio = (strtolower($empleado->tipo_trabajador)=='socio') ? true : false;
        $pagaSis = 'empresa';
        $tasaTrabajador = 0;
        $tasa = 0;
        $tasaSis = 0;
        $tasaEmpleador = 0;
        
        if($empleado->tipo_id==11 || $empleado->tipo_id==12){        
            if($empleado->prevision_id==8){
                $tasa = TasaCotizacionObligatorioAfp::valor($idAfp, 'tasa');
                $tasaTrabajador = $tasa;
                if($empleado->tipo_id==11){
                    $tasaSis = TasaCotizacionObligatorioAfp::valor($idAfp, 'sis');
                }
                if($socio){
                    $pagaSis = 'empleado'; 
                }else{
                    if($sis){
                        $tasaEmpleador = $tasaSis;
                    }else{
                        $pagaSis = 'empleado';
                    }
                }
            }else if($empleado->prevision_id==9){
                $mes = '2017-01-01';
                $tasa = TasaCajasExRegimen::where('caja_id', 9)->where('mes', $mes)->first()['tasa'];
                $tasaTrabajador = $tasa;
            }
        }
        
        $datos = array(
            'tasaTrabajador' => $tasaTrabajador,
            'tasaEmpleador' => $tasaEmpleador,
            'tasaObligatoria' => $tasa,
            'tasaSis' => $tasaSis,
            'pagaSis' => $pagaSis
        );
        
        return $datos;
    }
    
    public function cuentaAhorroVoluntario()
    {        
        $descuentos = $this->misDescuentos();
        if(count($descuentos)){
            foreach($descuentos as $descuento){
                if($descuento['tipo']['estructura']['id']==7){
                    $total = $descuento['montoPesos'];
                    return $total;
                }
            }
        }
        
        return 0;
    }
    
    public function descuentosCaja()
    {        
        $descuentos = $this->misDescuentos();
        $creditosPersonales = 0;
        $descuentoDental = 0;
        $descuentosLeasing = 0;
        $descuentosSeguro = 0;
        $otrosDescuentos = 0;
        $descuentoCargas = 0;
        
        if(count($descuentos)){
            foreach($descuentos as $descuento){
                if($descuento['tipo']['estructura']['id']==6){
                    if($descuento['tipo']['id']==6){
                        $creditosPersonales += $descuento['montoPesos'];
                    }else if($descuento['tipo']['id']==7){
                        $descuentoDental += $descuento['montoPesos'];
                    }else if($descuento['tipo']['id']==8){
                        $descuentosLeasing += $descuento['montoPesos'];
                    }else if($descuento['tipo']['id']==9){
                        $descuentosSeguro += $descuento['montoPesos'];
                    }
                }
            }
        }
        
        
        $datos = array(
            'creditosPersonales' => $creditosPersonales,
            'descuentoDental' => $descuentoDental,
            'descuentosLeasing' => $descuentosLeasing,
            'descuentosSeguro' => $descuentosSeguro,
            'otrosDescuentos' => $otrosDescuentos,
            'descuentoCargas' => $descuentoCargas
        );
        return $datos;
    }
    
    public function apvc()
    {        
        $descuentos = $this->misDescuentos();
        if(count($descuentos)){
            foreach($descuentos as $descuento){
                if($descuento['tipo']['estructura']['id']==3){
                    $idAfp = TipoDescuento::find($descuento['tipo']['id'])->nombre;
                    $datos = array(
                        'monto' => $descuento['montoPesos'],
                        'moneda' => $descuento['moneda'],
                        'cotizacionTrabajador' => $descuento['monto'],
                        'cotizacionEmpleador' => 0,
                        'numeroContrato' => '',
                        'idAfp' => $idAfp,
                        'idFormaPago' => 102
                    );
                    return $datos; 
                }
            }
        }
        
        return null;
    }
    
    public function totalAfp($nuevaRentaImponible=null)
    {                
        $diasLicencia = $this->totalDiasLicencias(); 
        $tasa = $this->tasaAfp();
        $rentaImponible = $this->rentaImponible();
        $rentaImponibleIngresada = NULL;
        $totalTrabajador = (($tasa['tasaTrabajador'] * $rentaImponible ) / 100);
        $totalEmpleador = (( $tasa['tasaEmpleador'] * $rentaImponible ) / 100);
        $cotizacion = (( $tasa['tasaObligatoria'] * $rentaImponible ) / 100);
        $isSIS = false;
        $sis = 0;
        
        if($nuevaRentaImponible){
            $isSIS = true;
            $rentaImponibleIngresada = $nuevaRentaImponible;
            $sisLicencia = (( $tasa['tasaSis'] * (($nuevaRentaImponible / 30) * $diasLicencia)) / 100);
            $sisActual = (( $tasa['tasaSis'] * $rentaImponible ) / 100);
            $sis = ($sisLicencia + $sisActual);
        }else{
            $empleado = $this->ficha();
            if($empleado->prevision_id==8){
                if($diasLicencia > 0){
                    $mesActual = \Session::get('mesActivo')->mes;
                    $diasTrabajados = $this->diasTrabajados(); 
                    $rentaImponibleAnterior = $this->rentaImponibleAnteriorAfp($mesActual);
                    $sisLicencia = (( $tasa['tasaSis'] * (($rentaImponibleAnterior['rentaImponible'] / 30) * $diasLicencia)) / 100);
                    $sisActual = (( $tasa['tasaSis'] * $rentaImponible ) / 100);
                    $sis = ($sisLicencia + $sisActual);
                    $isSIS = $rentaImponibleAnterior['isSIS'];
                }else{
                    $isSIS = true;
                    $sis = (( $tasa['tasaSis'] * $rentaImponible ) / 100);
                }
            }else{
                $isSIS = true;
            }
        }
        
        $datos = array(
            'totalTrabajador' => round($totalTrabajador),
            'totalEmpleador' => round($totalEmpleador),
            'cotizacion' => round($cotizacion),
            'sis' => round($sis),
            'isSIS' => $isSIS,
            'porcentajeSis' => $tasa['tasaSis'],
            'porcentajeCotizacion' => $tasa['tasaObligatoria'],
            'cuentaAhorroVoluntario' => $this->cuentaAhorroVoluntario(),
            'pagaSis' => $tasa['pagaSis'],
            'rentaImponibleIngresada' => $rentaImponibleIngresada
        );
        
        return $datos;
    }
    
    public function rentaImponibleAnteriorAfp($mesActual)
    {
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mesActual)));
        $liquidacionAnterior = Liquidacion::where('trabajador_id', $this->id)->where('mes', $mesAnterior)->first();
        if($liquidacionAnterior){
            if($liquidacionAnterior->dias_trabajados==30){
                if($liquidacionAnterior->detalleAfp){
                    $datos = array(
                        'rentaImponible' => $liquidacionAnterior->miDetalleAfp()['rentaImponible'],
                        'isSIS' => true
                    );
                    return $datos;
                }
            }else{
                return $this->rentaImponibleAnteriorAfp($mesAnterior);
            }
        }
        
        $datos = array(
            'rentaImponible' => 0,
            'isSIS' => false
        );
        
        return $datos;
    }
    
    public function rentaImponibleAnteriorSC($mesActual)
    {
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mesActual)));
        $liquidacionAnterior = Liquidacion::where('trabajador_id', $this->id)->where('mes', $mesAnterior)->first();
        if($liquidacionAnterior){
            if($liquidacionAnterior->dias_trabajados==30){
                if($liquidacionAnterior->detalleSeguroCesantia){
                    $datos = array(
                        'rentaImponible' => $liquidacionAnterior->miDetalleSeguroCesantia()['rentaImponible'],
                        'isSC' => true
                    );
                    return $datos;
                }
            }else{
                return $this->rentaImponibleAnteriorSC($mesAnterior);
            }
        }
        
        $datos = array(
            'rentaImponible' => 0,
            'isSC' => false
        );
        
        return $datos;
    }
    
    public function movimientoPersonal()
    {        
        $mesActual = \Session::get('mesActivo');
        $mes = $mesActual->mes;
        $fechaRemuneracion = $mesActual->fechaRemuneracion;
        $codigo = 0;
        $fechaDesde = null;
        $fechaHasta = null;
        $empleado = $this->ficha();
        $fechaReconocimiento = $empleado->fecha_reconocimiento;
        $fechaFiniquito = $empleado->fecha_finiquito;
        $tipoContrato = $empleado->tipo_contrato_id;
        $rut = '';
        $digito = '';
        $licencias = $this->misLicencias();
        $inasistencias = $this->misInasistencias();
        
        
        if($fechaReconocimiento>=$mes && $fechaReconocimiento<=$fechaRemuneracion){
            if($tipoContrato==1){
                $codigo = 1;
                $fechaDesde = $fechaReconocimiento;
            }else if($tipoContrato==2){
                $fechaDesde = $fechaReconocimiento;
                $fechaHasta = $empleado->fecha_vencimiento;
                $codigo = 7;
            }
            $fechaDesde = $fechaReconocimiento;
        }else if($empleado->estado=='Finiquitado'){
            if($fechaFiniquito>=$mes && $fechaFiniquito<=$fechaRemuneracion){
                $codigo = 2;
                $fechaHasta = $fechaFiniquito;                
            }else if($fechaFiniquito < $mes){
                $codigo = 12;
            }
        }else if(count($licencias)){
            $codigo = 3;
            $fechaDesde = $licencias[0]['desde'];
            $fechaHasta = $licencias[0]['hasta'];
        }else if(count($inasistencias)){
            if($inasistencias[0]['motivo']=='Permiso sin goce de sueldo'){
                $codigo = 4;  
            }else{
                $codigo = 11;                  
            }            
            $fechaDesde = $inasistencias[0]['desde'];
            $fechaHasta = $inasistencias[0]['hasta'];
        }
        
        $datos = array(
            'codigo' => $codigo,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'rut' => $rut,
            'digito' => $digito
        );
        
        return $datos;
    }
    
    public function totalColacion()
    {        return 0;
        $ficha = $this->ficha();
        $monto = $ficha->monto_colacion;
        $proporcional = $ficha->proporcional_colacion;
        $totalColacion = 0;
        
        if($monto>0){
            $totalColacion = Funciones::convertir($monto, $ficha->moneda_colacion);
            $diasTrabajados = $this->diasTrabajados();
            if($diasTrabajados < 30 && $proporcional==1){
                $diasTrabajados = $this->diasTrabajados();
                $totalColacion = (($totalColacion / 30) * $diasTrabajados);
            }
        }        
        
        return round($totalColacion);
    }
    
    public function totalMovilizacion()
    {        return 0;
        $ficha = $this->ficha();
        $monto = $ficha->monto_movilizacion;
        $totalMovilizacion = 0;
        $proporcional = $ficha->proporcional_movilizacion;
        
        if($monto>0){
            $totalMovilizacion = Funciones::convertir($monto, $ficha->moneda_movilizacion);
            $diasTrabajados = $this->diasTrabajados();
            if($diasTrabajados < 30 && $proporcional==1){
                $diasDescontados = $this->diasDescontados()['dias'];
                $totalMovilizacion = ($totalMovilizacion - (($totalMovilizacion / 30) * $diasDescontados));
            }
        }        
        
        return round($totalMovilizacion);
    }
    
    public function totalViatico()
    {        return 0;
        $ficha = $this->ficha();
        $monto = $ficha->monto_viatico;
        $proporcional = $ficha->proporcional_viatico;
        $totalViatico = 0;
        
        if($monto>0){
            $totalViatico = Funciones::convertir($monto, $ficha->moneda_viatico);
            $diasTrabajados = $this->diasTrabajados();
            if($diasTrabajados < 30 && $proporcional==1){
                $diasDescontados = $this->diasDescontados()['dias'];
                $totalViatico = ($totalViatico - (($totalViatico / 30) * $diasDescontados));
            }
        }        
        
        return round($totalViatico);
    }
    
    public function totalMutual()
    {   
        $empresa = \Session::get('empresa');
        $rentaImponible = $this->rentaImponible();
        $totalMutual = 0;        
        $porcentajeMutual = $empresa->porcentajeMutual();
        
        $totalMutual = round((($rentaImponible * $porcentajeMutual['fijo']) + ($rentaImponible * $porcentajeMutual['adicional']) + ($rentaImponible * $porcentajeMutual['extraordinaria']) + ($rentaImponible * $porcentajeMutual['sanna'])) / 100);
        
        return $totalMutual;
    }
    
    public function totalMutuales()
    {   
        $empresa = \Session::get('empresa');
        $rentaImponible = $this->rentaImponible();
        $totalMutual = 0;        
        $porcentajeMutual = $empresa->porcentajeMutual();
        
        $totalMutual = round((($rentaImponible * $porcentajeMutual['fijo']) + ($rentaImponible * $porcentajeMutual['adicional'])) / 100);
        return $porcentajeMutual;
    }
    
    public function totalSalud()
    {        
        if(!$this->miTotalSalud){
            $empleado =  $this->ficha();
            $idSalud = $empleado->isapre->id;
            $rentaImponible = $this->rentaImponible();
            $adicional = 0;
            $montoSalud = round( $rentaImponible * 0.07 );
            $excedente = 0;
            $montoCaja = 0;
            $montoFonasa = 0;
            $cargas = 0;
            $empresa = \Session::get('empresa');
            $bool = false;        

            if($this->rentaImponible()>0 && $idSalud!=240){
                $diasTrabajados = $this->diasTrabajados(); 
                if($idSalud!=246){
                    $cotizacionSalud = $empleado->cotizacion_isapre; 
                    if($cotizacionSalud == 'UF'){
                        $totalSalud = Funciones::convertirUF($empleado->monto_isapre);
                        if($empleado->estado=='Ingresado' && $diasTrabajados < 30 && !$empresa->salud_completa){
                            $diasTrabajados = $this->diasTrabajados();
                            $totalSalud = (($totalSalud / 30) * $diasTrabajados);
                        }
                        $adicional = ($totalSalud - $montoSalud);
                    }else if($cotizacionSalud == '7%'){
                        $uf = Funciones::convertirUF($empleado->monto_isapre);
                        $totalSalud = round( $rentaImponible * 0.07 );
                        $adicional = ($totalSalud - $montoSalud);
                    }else if($cotizacionSalud == '7% + UF'){
                        $uf = Funciones::convertirUF($empleado->monto_isapre);
                        if($empleado->estado=='Ingresado' && $diasTrabajados < 30 && !$empresa->salud_completa){
                            $diasTrabajados = $this->diasTrabajados();
                            $uf = (($uf / 30) * $diasTrabajados);
                        }
                        $base = round( $rentaImponible * 0.07 );
                        $totalSalud = ($base + $uf);
                        $adicional = ($totalSalud - $montoSalud);
                    }else{
                        $totalSalud = $empleado->monto_isapre;
                        if($empleado->estado=='Ingresado' && $diasTrabajados < 30 && !$empresa->salud_completa){
                            $diasTrabajados = $this->diasTrabajados();
                            $totalSalud = (($totalSalud / 30) * $diasTrabajados);
                        }
                        $adicional = ($totalSalud - $montoSalud);
                    }            
                    if($adicional<0){
                        $excedente = ($adicional * -1);
                        $adicional = 0;
                        $totalSalud = $montoSalud;
                    }
                }else{                      
                    if($empresa->caja_id!=257){
                        $montoFonasa = round( $rentaImponible * 0.064 );
                        $montoCaja = round( $rentaImponible * 0.006 );
                        $montoSalud = ($montoFonasa + $montoCaja);
                        $totalSalud = $montoSalud;
                    }else{
                        $montoFonasa = round(($rentaImponible * 0.064) + ($rentaImponible * 0.006));   
                        $montoSalud = $montoFonasa;
                        $totalSalud = $montoSalud;
                    }  
                }                        
            }else{
                $montoSalud = 0;
                $adicional = 0;
                $excedente = 0;
                $totalSalud = 0;
            }
            
            $datos = array(
                'obligatorio' => round($montoSalud),
                'montoFonasa' => round($montoFonasa),
                'montoCaja' => round($montoCaja),
                'adicional' => round($adicional),
                'excedente' => round($excedente),
                'cargas' => round($cargas),
                'total' => round($totalSalud)
            );
            
            $this->miTotalSalud = $datos;
        }
        
        
        
        return $this->miTotalSalud;
    }
    
    public function comprobarHaberes($haberes)
    {
        $misHaberes = $this->misHaberesPermanentes();
        $update = array();
        $create = array();
        $destroy = array();
        
        if($misHaberes){
            foreach($haberes as $haber)
            {
                $isUpdate = false;
                
                if(isset($haber['id'])){    
                    foreach($misHaberes as $miHaber)
                    {
                        if($haber['id'] == $miHaber['id']){
                            $update[] = $haber;
                            $isUpdate = true;
                        }                        
                        if($isUpdate){
                            break;
                        }
                    }
                }else{
                    $create[] = $haber;
                }
            }

            foreach($misHaberes as $miHaber)
            {
                $isHaber = false;
                foreach($haberes as $haber)
                {
                    if(isset($haber['id'])){
                        if($miHaber['id'] == $haber['id']){
                            $isHaber = true;                        
                        }
                    }
                }
                if(!$isHaber){
                    $destroy[] = $miHaber;
                }
            }
        }else{
            $create = $haberes;
        }
        
        $datos = array(
            'create' => $create,
            'update' => $update,
            'destroy' => $destroy
        );
        
        $this->updateHaberes($datos);
        
        //return $datos;
    }
    
    public function comprobarDescuentos($descuentos)
    {
        $misDescuentos = $this->misDescuentosPermanentes();
        $update = array();
        $create = array();
        $destroy = array();
        
        if($misDescuentos){
            foreach($descuentos as $descuento)
            {
                $isUpdate = false;
                
                if(isset($descuento['id'])){    
                    foreach($misDescuentos as $miDescuento)
                    {
                        if($descuento['id'] == $miDescuento['id']){
                            $update[] = $descuento;
                            $isUpdate = true;
                        }                        
                        if($isUpdate){
                            break;
                        }
                    }
                }else{
                    $create[] = $descuento;
                }
            }

            foreach($misDescuentos as $miDescuento)
            {
                $isHaber = false;
                foreach($descuentos as $descuento)
                {
                    if(isset($descuento['id'])){
                        if($miDescuento['id'] == $descuento['id']){
                            $isHaber = true;                        
                        }
                    }
                }
                if(!$isHaber){
                    $destroy[] = $miDescuento;
                }
            }
        }else{
            $create = $descuentos;
        }
        
        $datos = array(
            'create' => $create,
            'update' => $update,
            'destroy' => $destroy
        );
        
        $this->updateDescuentos($datos);
        
        //return $datos;
    }
    
    public function updateHaberes($datos)
    {
        if($datos['create']){
            foreach($datos['create'] as $haber){
                $nuevoHaber = new Haber();
                $nuevoHaber->sid = Funciones::generarSID();
                $nuevoHaber->tipo_haber_id = $haber['tipo']['id'];
                $nuevoHaber->trabajador_id = $this->id;
                $nuevoHaber->moneda = $haber['moneda'];
                $nuevoHaber->monto = $haber['monto'];
                $nuevoHaber->proporcional = $haber['proporcional'];
                $nuevoHaber->permanente = 1;
                $nuevoHaber->save();
            }
        }  
        if($datos['update']){
            foreach($datos['update'] as $haber){
                $nuevoHaber = Haber::find($haber['id']);
                $nuevoHaber->tipo_haber_id = $haber['tipo']['id'];
                $nuevoHaber->moneda = $haber['moneda'];
                $nuevoHaber->monto = $haber['monto'];
                $nuevoHaber->proporcional = $haber['proporcional'];
                $nuevoHaber->save();
            }
        }    
        if($datos['destroy']){
            foreach($datos['destroy'] as $haber){
                $nuevoHaber = Haber::find($haber['id']);
                $nuevoHaber->delete();
            }
        } 
        
        return;
    }
    
    public function updateDescuentos($datos)
    {
        if($datos['create']){
            foreach($datos['create'] as $descuento){
                $nuevoDescuento = new Descuento();
                $nuevoDescuento->sid = Funciones::generarSID();
                $nuevoDescuento->tipo_descuento_id = $descuento['tipo']['id'];
                $nuevoDescuento->trabajador_id = $this->id;
                $nuevoDescuento->moneda = $descuento['moneda'];
                $nuevoDescuento->monto = $descuento['monto'];
                $nuevoDescuento->permanente = 1;
                $nuevoDescuento->save();
            }
        }  
        if($datos['update']){
            foreach($datos['update'] as $descuento){
                $nuevoDescuento = Descuento::find($descuento['id']);
                $nuevoDescuento->tipo_descuento_id = $descuento['tipo']['id'];
                $nuevoDescuento->moneda = $descuento['moneda'];
                $nuevoDescuento->monto = $descuento['monto'];
                $nuevoDescuento->save();
            }
        }    
        if($datos['destroy']){
            foreach($datos['destroy'] as $descuento){
                $nuevoDescuento = Descuento::find($descuento['id']);
                $nuevoDescuento->delete();
            }
        } 
        
        return;
    }
  
    public function antiguedad()
    {
      $mes = \Session::get('mesActivo');
      $finMes = $mes->fechaRemuneracion;
      $empleado = $this->ficha();
      $fechaReconocimiento = $empleado->fecha_reconocimiento;
      $fechaReconocimiento = new DateTime($fechaReconocimiento);
      $fecha = new DateTime($finMes);
      $diff = $fechaReconocimiento->diff($fecha);
      $anios = $diff->y;
      
      return $anios;
    }
    
    public function antiguedadCesantia()
    {
      $mes = \Session::get('mesActivo');
      $finMes = $mes->fechaRemuneracion;
      $empleado = $this->ficha();
      $fechaReconocimiento = $empleado->fecha_reconocimiento_cesantia;
      if(!$fechaReconocimiento){
          $fechaReconocimiento = $empleado->fecha_reconocimiento;
      }
      $fechaReconocimiento = new DateTime($fechaReconocimiento);
      $fecha = new DateTime($finMes);
      $diff = $fechaReconocimiento->diff($fecha);
      $anios = $diff->y;
      
      return $anios;
    }
    
    public function antiguedadMeses()
    {
      $mes = \Session::get('mesActivo');
      $finMes = $mes->fechaRemuneracion;
      $empleado = $this->ficha();
      $fechaReconocimiento = $empleado->fecha_reconocimiento;
      $fechaReconocimiento = new DateTime($fechaReconocimiento);
      $fecha = new DateTime($finMes);
      $diff = $fechaReconocimiento->diff($fecha);
      $meses = (($diff->y * 12) + $diff->m);
      
      return $meses;
    }
    
    public function rentaImponibleSC()
    {
        $mes = \Session::get('mesActivo')->mes;
        $tope = RentaTopeImponible::valor('Para Seguro de Cesantía');
        
        return $rentaImponible;
    }
    
    public function totalSeguroCesantia($nuevaRentaImponible=null)
    {        
        $mes = \Session::get('mesActivo')->mes;
        $totalSeguroCesantiaTrabajador = 0;
        $diasLicencia = $this->totalDiasLicencias();
        $totalSeguroCesantiaEmpleador = 0;
        $empleado = $this->ficha();
        $afcTrabajador = 0;
        $rentaImponible = 0;
        $rentaImponibleEmpleador = NULL;
        $afcEmpleador = 0;
        $isSC = false;
        $ri = 0;
        
        if($empleado->seguro_desempleo && strtolower($empleado->tipo_trabajador)=='normal'){
            if($empleado->tipoContrato['id']==2){
              $indefinido = false;
            }else{
              $indefinido = true;
            }  
            
            $rentaImponible = $this->sumaImponibles();
            $todosImponibles = $this->todosImponibles();
            $diasTrabajados = $this->diasTrabajados();
            $topeSeguro = RentaTopeImponible::valor('Para Seguro de Cesantía');
            
            $topeSeguroPesos = Funciones::convertirUF($topeSeguro);        
            
            if($nuevaRentaImponible){
                $ri = $nuevaRentaImponible;
                $rentaImponibleEmpleador = (($nuevaRentaImponible / 30) * $diasLicencia);
                $isSC = true;
            }else{
                if($empleado->estado=='Ingresado'){
                    if($diasLicencia > 0){
                        $mesActual = \Session::get('mesActivo')->mes;
                        $rentaImponibleAnterior = $this->rentaImponibleAnteriorSC($mesActual);
                        $rentaImponibleEmpleador = $rentaImponibleAnterior['rentaImponible'];
                        if($rentaImponibleEmpleador > $topeSeguroPesos){
                            $rentaImponibleEmpleador = $topeSeguroPesos;
                        }
                        $isSC = $rentaImponibleAnterior['isSC'];
                        $ri = $rentaImponibleEmpleador;
                        $rentaImponibleEmpleador = (($rentaImponibleEmpleador / 30) * $diasLicencia);
                        if($todosImponibles > $topeSeguroPesos && $diasTrabajados < 30){
                            $diasTrabajados = $this->diasTrabajados();
                            $rentaImponible = (($topeSeguroPesos / 30) * $diasTrabajados);
                        }else{
                            if($rentaImponible > $topeSeguroPesos){
                                $rentaImponible = $topeSeguroPesos;
                            }
                        }
                    }else{
                        if($rentaImponible > $topeSeguroPesos){
                            $rentaImponible = $topeSeguroPesos;
                        }
                        $isSC = true;
                    }
                }else{
                    if($rentaImponible > $topeSeguroPesos){
                        $rentaImponible = $topeSeguroPesos;
                    }   
                    $isSC = true;
                }
            }
            
            if($this->antiguedadCesantia()<11){                          
              
              if($indefinido){
                  $afcTrabajador = SeguroDeCesantia::valor('Contrato Plazo Indefinido', 'trabajador');    
                  $afcEmpleador = SeguroDeCesantia::valor('Contrato Plazo Indefinido', 'empleador');    
                  $totalSeguroCesantiaTrabajador = round((( $afcTrabajador * $rentaImponible ) / 100 ));
                  $totalSeguroCesantiaEmpleador = round(( $afcEmpleador * ($rentaImponibleEmpleador + $rentaImponible ) / 100 ));
              }else{
                  $afcTrabajador = SeguroDeCesantia::valor('Contrato Plazo Fijo', 'trabajador');    
                  $afcEmpleador = SeguroDeCesantia::valor('Contrato Plazo Fijo', 'empleador');  
                  $totalSeguroCesantiaTrabajador = round((( $afcTrabajador * $rentaImponible ) / 100 ));
                  $totalSeguroCesantiaEmpleador = round(( $afcEmpleador * ($rentaImponibleEmpleador + $rentaImponible ) / 100 ));
              }
            }else{
              if($indefinido){
                  $afcTrabajador = SeguroDeCesantia::valor('Contrato Plazo Indefinido 11 años o más ', 'trabajador');    
                  $afcEmpleador = SeguroDeCesantia::valor('Contrato Plazo Indefinido 11 años o más ', 'empleador');  
                  $totalSeguroCesantiaTrabajador = round((( $afcTrabajador * $rentaImponible ) / 100 ));
                  $totalSeguroCesantiaEmpleador = round(( $afcEmpleador * ($rentaImponibleEmpleador + $rentaImponible ) / 100 ));
              }else{
                  $afcTrabajador = SeguroDeCesantia::valor('Contrato Plazo Fijo', 'trabajador');    
                  $afcEmpleador = SeguroDeCesantia::valor('Contrato Plazo Fijo', 'empleador'); 
                  $totalSeguroCesantiaTrabajador = round((( $afcTrabajador * $rentaImponible ) / 100 ));
                  $totalSeguroCesantiaEmpleador = round(( $afcEmpleador * ($rentaImponibleEmpleador + $rentaImponible ) / 100 ));
              }
            }
        }else{
            $isSC = true;
        }                
      
        $datos = array(
            'afc' => $afcTrabajador,
            'afcEmpleador' => $afcEmpleador,
            'total' => $totalSeguroCesantiaTrabajador,
            'totalEmpleador' => $totalSeguroCesantiaEmpleador,
            'rentaImponible' => $rentaImponible ? $rentaImponible : $ri,
            'rentaImponibleIngresada' => $nuevaRentaImponible ? $rentaImponibleEmpleador : NULL,
            'isSC' => $isSC
        );
        
        return $datos;
    }
    
    public function licenciasAdicional()
    {
        $licencias = $this->misLicencias();
        $datos = array();
        if(count($licencias)>1){
            foreach($licencias as $licencia){
                $datos[] = array(
                    'desde' => $licencia['desde'],
                    'hasta' => $licencia['hasta']
                );
            }
            unset($datos[0]);
            return $datos;
        }
        return false;
    }
    
    public function isLiquidacion($mes=null)
    {
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        $isLiquidacion = false;
        $liquidaciones = Liquidacion::where('trabajador_id', $this->id)->where('mes', $mes)->get();
        
        if($liquidaciones->count()){
            $isLiquidacion = true;
        }
        
        return $isLiquidacion;
    }
    
    public function declaracion($anio)
    {
        $declaracion = DeclaracionTrabajador::where('trabajador_id', $this->id)->where('anio_id', $anio->id)->first();
        if($declaracion){
            $datos = array(
                'id' => $declaracion->id,
                'sid' => $declaracion->sid,
                'folio' => $declaracion->folio,
                'nombre' => $declaracion->nombre_archivo
            );
            
            return $datos;
        }
        
        return null;
    }
    
    public function totalDescuentosPrevisionales()
    {                           
        $totalAfp = $this->totalAfp();
        $totalSalud = $this->totalSalud();
        $totalSeguroCesantia = $this->totalSeguroCesantia();        
        $sis = 0;
        if($totalAfp['pagaSis']=='empleado'){
            $sis = $totalAfp['sis'];
        }
        $totalDescuentos = ( $totalAfp['totalTrabajador'] + $totalSalud['obligatorio'] + $totalSalud['adicional'] + $totalSeguroCesantia['total'] + $sis);
        
        return $totalDescuentos;
    }
    
    public function totalOtrosDescuentos()
    {        
        $id = $this->id;
        $empleado = $this->ficha();
        $mes = \Session::get('mesActivo')->mes;
        $descuentos = $this->misDescuentos();
        $cuotas = $this->totalCuotasPrestamos();
        $apvs = $this->totalApvPagar();
        $sumaDescuentos = 0;
        
        if($descuentos){
            foreach($descuentos as $desc){
                $monto = Funciones::convertir($desc['monto'], $desc['moneda']);
                $sumaDescuentos += $monto;
            }    
        }
        
        $sumaDescuentos = $sumaDescuentos + $apvs + $cuotas;
              
        return $sumaDescuentos;
    } 
    
    public function totalAnticipos()
    {        
        $id = $this->id;
        $empleado = $this->ficha();
        $mes = \Session::get('mesActivo')->mes;
        $descuentos = $this->misDescuentos();        
        $sumaAnticipos = 0;
        
        if($descuentos){
            foreach($descuentos as $desc){
                if($desc['tipo']['estructura']['id']==2){
                    $sumaAnticipos += $desc['montoPesos'];
                }
            }
        }
                      
        return $sumaAnticipos;
    }        
    
    public function zonaImpuestoUnico()
    {
        $empresa = \Session::get('empresa');
        
        if($empresa->impuesto_unico=='e'){
            return $empresa->zona;
        }else{
            $ficha = $this->ficha();
            $zona = $ficha->zonaImpuestoUnico ? $ficha->zonaImpuestoUnico->porcentaje : 0;
            return $zona;            
        }
    }
    
    public function baseImpuestoUnico()
    {        
        $rentaImponibleTributable = $this->rentaImponibleTributable();
        $baseImpuestoUnico = 1;
        $mes = \Session::get('mesActivo')->mes;
        $zona = $this->zonaImpuestoUnico();
        $sis = 0;
        $rebaja = 0;
        
        if($rentaImponibleTributable > 0){
            $rentaImp = RentaTopeImponible::valor('Para afiliados a una AFP');
            if($rentaImp){
                $topeImponible = Funciones::convertirUF($rentaImp);
            }else{
                $topeImponible = 0;
            }
            
            $topeImponible = ($topeImponible * 0.07);
            $salud = $this->totalSalud()['total'];
            $afp = $this->totalAfp();
            $totalAfp = $afp['totalTrabajador'];
            
            if($afp['pagaSis']=='empleado'){
                $sis = $afp['sis'];
            }
            
            $totalSeguroCesantia = $this->totalSeguroCesantia()['total'];       
            $totalDescuentosPrevisionales = ($totalAfp + $salud + $totalSeguroCesantia);
            $apvsRegimenB = $this->totalApvsRegimenB();
            if($salud>$topeImponible){
                $resto = ($salud - $topeImponible);
                $totalDescuentosPrevisionales = ($totalDescuentosPrevisionales - $resto);
            }

            $baseImpuestoUnico = ($rentaImponibleTributable - $totalDescuentosPrevisionales - $apvsRegimenB - $sis);
            $rebaja = ($baseImpuestoUnico * ($zona / 100));
            $baseImpuestoUnico = round($baseImpuestoUnico - $rebaja);
            
            if($baseImpuestoUnico<1){
                $baseImpuestoUnico = 1;
            }
        }
        
        $datos = array(
            'base' => $baseImpuestoUnico,
            'rebaja' => $rebaja
        );
        
        return $datos;
    }
    
    public function tramoImpuesto()
    {        
        $mes = Session::get('mesActivo');
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
        $tramos = TablaImpuestoUnico::where('mes', $fecha)->get();
        $factor = 0;
        $baseImpuestoUnico = $this->baseImpuestoUnico()['base'];
        foreach($tramos as $tramo){
            $desde = Funciones::convertirUTM($tramo->imponible_mensual_desde, false);
            $hasta = Funciones::convertirUTM($tramo->imponible_mensual_hasta, false);
            if($baseImpuestoUnico > $desde && $baseImpuestoUnico <= $hasta){
                $factor = $tramo;
                break;
            }
        }     
        
        return $factor;
    }
    
    public function totalApvsRegimenB()
    {
        $apvs = $this->misApvsPagar();
        $totalRebajar = 0;
        if($apvs){
            foreach($apvs as $apv){
                if(strtoupper($apv['regimen'])=='B'){
                    $totalRebajar += Funciones::convertir($apv['monto'], $apv['moneda']);
                }
            }
        }
        
        return $totalRebajar;
    }
    
    static function isAllLiquidados($idMes)
    {
        $mes = MesDeTrabajo::find($idMes);
        $finMes = $mes['fecha_remuneracion'];
        $trabajadores = Trabajador::all();
        $bool = true;
        
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        if(!$trabajador->isLiquidacion($mes['mes'])){
                        $trabajadore[] = $trabajador;
                            $bool = false;
                            break;
                        }
                    }
                }
            }
        }
        
        return $bool;
    }
    
    public function impuestoDeterminado()
    {                        
        $tramo = $this->tramoImpuesto();
        $factor = $tramo->factor;
        $cantidadARebajar = Funciones::convertirUTM($tramo->cantidad_a_rebajar, false);
        $baseImpuestoUnico = $this->baseImpuestoUnico()['base'];        
        
        $impuestoDeterminado = round((($factor / 100) * $baseImpuestoUnico) - $cantidadARebajar);
        
        return $impuestoDeterminado;
    }
    
    public function noImponibles()
    {        
        $empleado = $this->ficha();
        $noImponibles = $this->sumaNoImponibles();
        $permanentes = ($this->totalColacion() + $this->totalViatico() + $this->totalMovilizacion());
        $cargasFamiliares = $this->cargasFamiliares()['monto'];
        $total = ($noImponibles + $permanentes + $cargasFamiliares);
        
        return $total;
    }
    
    public function totalImponibles()
    {        
        $gratificacion = $this->gratificacion();
        $imponibles = $this->imponibles();
        $sueldo = $this->sueldo();
        $horasExtra = $this->horasExtraPagar()['total'];
        $semanaCorrida = $this->miSemanaCorrida();
        
        $total = ($imponibles + $sueldo + $gratificacion + $horasExtra + $semanaCorrida);
        
        return $total;
    }   
    
    public function todosImponibles()
    {        
        $gratificacion = $this->gratificacion();
        $imponibles = $this->imponibles();
        $sueldo = $this->sueldoBase();
        $horasExtra = $this->horasExtraPagar()['total'];
        $semanaCorrida = $this->miSemanaCorrida();
        
        $total = ($imponibles + $sueldo + $gratificacion + $horasExtra + $semanaCorrida);
        
        return $total;
    } 
    
    public function sueldoBase()
    {
        $empleado = $this->ficha();
        
        return Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo);
    }
    
    public function remuneracionAnualTrabajador()
    {
        $sum = 0;
        $mesActual = \Session::get('mesActivo')->mes;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' year', strtotime($mesActual)));
        $liquidaciones = Liquidacion::where('trabajador_id', $this->id)->whereBetween('mes', [$mesAnterior, $mesActual])->get();
        if($liquidaciones->count()){
            $sum = $liquidaciones->sum('sueldo_liquido');
        }
        
        return $sum;
    }
    
    public function haberesGratificacion()
    {    
        $haberesGratificacion = 0;
        $misHaberes = $this->misHaberes();
        
        if( $misHaberes){
            foreach($misHaberes as $haber){
                if($haber['tipo']['gratificacion']){
                    $monto = $haber['montoPesos'];                 
                    $haberesGratificacion += $monto;
                }
            }
        }
        
        $horasExtra = TipoHaber::find(7);
        if($horasExtra->gratificacion){
            $totalHorasExtra = $this->horasExtraPagar()['total'];
            $haberesGratificacion += $totalHorasExtra;
        }
        
        return $haberesGratificacion;
    }
    
    public function baseGratificacion()
    {
        $haberesGratificacion = $this->haberesGratificacion();
        $sueldo = $this->sueldo();
        
        return ($sueldo + $haberesGratificacion);
    }
    
    public function tipoGratificacion()
    {
        $empresa = \Session::get('empresa');
        $tipoGratificacion = '';
        $empleado = $this->ficha();
        if($empleado->gratificacion_especial){
            $tipoGratificacion = 'es';
        }else{
            if($empresa->gratificacion=='e'){
                $tipoGratificacion = $empresa->tipo_gratificacion;
            }else{
                $tipoGratificacion = $empleado->gratificacion;
            }
        }
                
        return $tipoGratificacion;
    }
    
    public function gratificacionProporcional()
    {
        $tipoGratificacion = $this->tipoGratificacion();
        $proporcionalInasistencias = false;
        $proporcionalLicencias = false;
        
        if($tipoGratificacion=='m'){        
            $empresa = \Session::get('empresa');
            if($empresa->gratificacion=='e'){
                $proporcionalInasistencias = $empresa->gratificacion_proporcional_inasistencias ? true : false;
                $proporcionalLicencias = $empresa->gratificacion_proporcional_licencias ? true : false;
            }else{
                $empleado = $this->ficha();
                $proporcionalInasistencias = $empleado->gratificacion_proporcional_inasistencias ? true : false;
                $proporcionalLicencias = $empleado->gratificacion_proporcional_licencias ? true : false;
            }
        }
        $datos = array(
            'licencias' => $proporcionalLicencias,
            'inasistencias' => $proporcionalInasistencias
        );
        
        return $datos;
    }
    
    public function gratificacion()
    {
        if(!$this->miGratificacion){
            $gratificacion = 0;
            $empleado = $this->ficha();

            if(strtolower($empleado->tipo_trabajador)=='normal'){
                $baseGratificacion = $this->baseGratificacion();
                $diasTrabajados = $this->diasTrabajados();
                $tipoGratificacion = $this->tipoGratificacion();        
                $proporcional = $this->gratificacionProporcional();
                $empresa = \Session::get('empresa');

                if($empresa->rut=='965799206'){
                    if($this->id==9){
                        $mes = \Session::get('mesActivo')->id;
                        if($mes==92 || $mes==93){
                            return 0;
                        }
                    }
                }

                if($baseGratificacion > 0){
                    if($tipoGratificacion=='es'){
                        $gratificacion = Funciones::convertir($empleado->monto_gratificacion, $empleado->moneda_gratificacion);
                    }else if($tipoGratificacion=='m'){
                        $gratificacion = (($baseGratificacion) * 0.25);
                        $topeGratificacion = $this->topeGratificacion();
                        if($gratificacion > $topeGratificacion){
                            $gratificacion = $topeGratificacion;
                        }  
                        if($proporcional['licencias'] || $proporcional['inasistencias']){
                            if($proporcional['licencias'] && $this->totalDiasLicencias()>0){
                                $diasTrabajados = $this->diasTrabajados();
                                $gratificacion = (($gratificacion / 30) * $diasTrabajados);
                            }else{
                                if($proporcional['inasistencias'] && $this->totalInasistencias()>0){
                                    $diasTrabajados = $this->diasTrabajados();
                                    $gratificacion = (($gratificacion / 30) * $diasTrabajados);
                                    
                                } 
                            }
                        }
                    }else{
                        $mes = \Session::get('mesActivo');
                        $mesActual = $mes->mes;
                        $anio = AnioRemuneracion::find($mes->idAnio);
                        $gratificacion = 0;
                        if($anio->gratificacion==$mesActual){
                            $utilidad = $anio->utilidad;
                            $antiguedad = $this->antiguedadMeses();
                            if($utilidad){
                                $remuneracionAnualDevengada = Liquidacion::remuneracionAnualDevengada();
                                $remuneracionAnualTrabajador = $this->remuneracionAnualTrabajador();
                                $factor = ($utilidad / $remuneracionAnualDevengada);
                                $gratificacion = ($factor * $remuneracionAnualTrabajador);
                                if($antiguedad<12){
                                    $gratificacion = (($gratificacion / 12) * $antiguedad);
                                }
                            }
                        }
                    }
                }
            } 
            $this->miGratificacion = round($gratificacion);
        }
        
        return $this->miGratificacion;
    }
    
    public function sueldoLiquido()
    {        
        $imponibles = $this->totalImponibles();
        $noImponibles = $this->noImponibles();
        $descuentosPrevisionales = $this->totalDescuentosPrevisionales();
        $descuentosTributarios = $this->impuestoDeterminado();
        $otrosDescuentos = $this->totalOtrosDescuentos();
        $sueldoLiquido = 0;
        
        $sueldoLiquido = ( $imponibles + $noImponibles - $descuentosPrevisionales - $descuentosTributarios - $otrosDescuentos );
        
        return round($sueldoLiquido);
    }
    
    static function trabajadoresRMI(&$notificaciones)
    {
        if(!\Session::get('empresa')){
            return Response::json(array());
        }
        $mesActual = \Session::get('mesActivo');
        if($mesActual->indicadores){
            $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#reajuste-global');
            $finMes = $mesActual->fechaRemuneracion;
            $mes = $mesActual->mes;

            $rmi = RentaMinimaImponible::where('mes', $mes)->where('nombre', 'Trab. Dependientes e Independientes')->first()->valor;
            $trabajadores = Trabajador::all();
            $listaTrabajadores=array();

            if( $trabajadores->count() ){
                foreach( $trabajadores as $trabajador ){
                    $empleado = $trabajador->ficha();
                    if($empleado){
                        if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes && Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo)<$rmi){
                           $notificaciones[] = array(
                                'concepto' => 'RMI',
                                'titulo' => "<a href='#reajuste-global'>Reajuste Global Sueldo</a>",
                                'mensaje' => "<a href='#reajuste-global'>Existen trabajadores cuyo sueldo no supera la Renta Mínima  Imponible.</a>"
                            ); 
                            return;
                        }

                    }

                }

            }
        }
        
        return;
    }
    
    public function eliminarDatos()
    {
        $idTrabajador = $this->id;
        $fichas = FichaTrabajador::where('trabajador_id', $idTrabajador)->get();
        if($fichas->count()){
            foreach($fichas as $ficha){
                $ficha->delete();
            }
        }
        $haberes = Haber::where('trabajador_id', $idTrabajador)->get();
        if($haberes->count()){
            foreach($haberes as $haber){
                $haber->delete();
            }
        }
        $descuentos = Descuento::where('trabajador_id', $idTrabajador)->get();
        if($descuentos->count()){
            foreach($descuentos as $descuento){
                $descuento->delete();
            }
        }
        $horasExtra = HoraExtra::where('trabajador_id', $idTrabajador)->get();
        if($horasExtra->count()){
            foreach($horasExtra as $horaExtra){
                $horaExtra->delete();
            }
        }
        $inasistencias = Inasistencia::where('trabajador_id', $idTrabajador)->get();
        if($inasistencias->count()){
            foreach($inasistencias as $inasistencia){
                $inasistencia->delete();
            }
        }
        $licencias = Licencia::where('trabajador_id', $idTrabajador)->get();
        if($licencias->count()){
            foreach($licencias as $licencia){
                $licencia->delete();
            }
        }
        $semanas = SemanaCorrida::where('trabajador_id', $idTrabajador)->get();
        if($semanas->count()){
            foreach($semanas as $semana){
                $semana->delete();
            }
        }
        $vacaciones = Vacaciones::where('trabajador_id', $idTrabajador)->get();
        if($vacaciones->count()){
            foreach($vacaciones as $vacacion){
                $vacacion->delete();
            }
        }
        $tomasVacaciones = TomaVacaciones::where('trabajador_id', $idTrabajador)->get();
        if($tomasVacaciones->count()){
            foreach($tomasVacaciones as $tomaVacaciones){
                $tomaVacaciones->delete();
            }
        }
        $prestamos = Prestamo::where('trabajador_id', $idTrabajador)->get();
        if($prestamos->count()){
            foreach($prestamos as $prestamo){
                $prestamos->eliminarPrestamo();
            }
        }
        $documentos = Documento::where('trabajador_id', $idTrabajador)->get();
        if($documentos->count()){
            foreach($documentos as $documento){
                $documento->eliminarDocumento();
            }
        }
        $apvs = Apv::where('trabajador_id', $idTrabajador)->get();
        if($apvs->count()){
            foreach($apvs as $apv){
                $apv->delete();
            }
        }
        $cargas = Carga::where('trabajador_id', $idTrabajador)->get();
        if($cargas->count()){
            foreach($cargas as $carga){
                $carga->delete();
            }
        }
        $secciones = Seccion::where('encargado_id', $idTrabajador)->get();
        if($secciones->count()){
            foreach($secciones as $seccion){
                $seccion->encargado_id = NULL;
                $seccion->save();
            }
        }
        $usuario = DB::table('usuarios')->where('funcionario_id', $idTrabajador)->first();
        if($usuario){
            DB::table('permisos')->where('usuario_id', $usuario->id)->delete();
            DB::table('usuarios')->where('id', $usuario->id)->delete();
        }
    }
    
    
    /*  PORTAL_EMPLEADOS    */
        
    
    public function validar($datos)
    {
        $trabajadores = Trabajador::where('rut', $datos['rut'])->get();

        if($trabajadores->count()){
            if($this->id){
                foreach($trabajadores as $trabajador){
                    if($trabajador['rut']==$datos['rut'] && $trabajador['id']!=$this->id){
                        $errores = new stdClass();
                        $errores->rut = array('El RUT ya se encuentra registrado');
                        return $errores;
                    }
                }
            }else{
                foreach($trabajadores as $trabajador){
                    if($trabajador['rut']==$datos['rut']){
                        $errores = new stdClass();
                        $errores->rut = array('El RUT ya se encuentra registrado');
                        return $errores;
                    }
                }
            }
        }
        
        return;
    }

    static function errores($datos)
    {
        if($datos['id']){
            $rules =    array(
                'rut' => 'required|unique:trabajadores,rut,'.$datos['id']
            );
        }else{
            $rules =    array(
                'rut' => 'required|unique:trabajadores,rut'
            );
        }
                
        $message = array(
            'rut.required' => 'Obligario!',
            'nombres.required' => 'Obligario!',
            'apellidos.required' => 'Obligario!',
            'nacionalidad.required' => 'Obligario!',
            'sexo.required' => 'Obligario!',
            'estado_civil.required' => 'Obligario!',
            'fecha_nacimiento.required' => 'Obligario!',
            'direccion.required' => 'Obligario!',
            'comuna_id.required' => 'Obligario!',
            'celular.required' => 'Obligario!',
            'email.required' => 'Obligario!',
            'cargo.required' => 'Obligario!',
            'seccion_id.required' => 'Obligario!',
            'tipo_cuenta.required' => 'Obligario!',
            'banco.required' => 'Obligario!',
            'numero_cuenta.required' => 'Obligario!',
            'fecha_ingreso.required' => 'Obligario!',
            'fecha_reconocimiento.required' => 'Obligario!',
            'tipo_contrato.required' => 'Obligario!',
            'tipo_jornada.required' => 'Obligario!',
            'semana_corrida.required' => 'Obligario!',
            'moneda_sueldo.required' => 'Obligario!',
            'sueldo_base.required' => 'Obligario!',
            'tipo_trabajador.required' => 'Obligario!',
            'gratificacion_mensual.required' => 'Obligario!',
            'gratificacion_anual.required' => 'Obligario!',
            'moneda_colacion.required' => 'Obligario!',
            'colacion.required' => 'Obligario!',
            'moneda_movilizacion.required' => 'Obligario!',
            'movilizacion.required' => 'Obligario!',
            'moneda_viatico.required' => 'Obligario!',
            'viatico.required' => 'Obligario!',
            'afp.required' => 'Obligario!',
            'seguro_desempleo.required' => 'Obligario!',
            'isapre.required' => 'Obligario!',
            'cotizacion_isapre.required' => 'Obligario!',
            'monto_isapre.required' => 'Obligario!',
            'sindicato.required' => 'Obligario!',
            'moneda_sindicato.required' => 'Obligario!',
            'monto_sindicato.requiredo' => 'Obligario!',
            'rut.unique' => 'El RUT ya se encuentra registrado!'
        );

        $verifier = App::make('validation.presence');
        //$verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->getMessageBag()->toArray();
        }else{
            return false;
        }
    }
}