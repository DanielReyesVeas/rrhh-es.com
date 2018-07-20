<?php

class Empresa extends \Eloquent {
	protected $table = "empresas";
    protected $connection  = "principal";

    public function comuna(){
        return $this->belongsTo('Comuna', 'comuna_id');
    }
    
    public function comunaRepresentante(){
        return $this->belongsTo('Comuna', 'representante_comuna_id');
    }
    
    public function mutual(){
        return $this->belongsTo('Glosa', 'mutual_id');
    }
    
    public function caja(){
        return $this->belongsTo('Glosa', 'caja_id');
    }

    public function rut_formato(){
        return Funciones::formatear_rut($this->rut);
    }

    public function cuentasCorrientes(){
        return $this->hasMany('EmpresaCtaCte', 'empresa_id')->orderBy('id', 'ASC');
    }

    public function misZonas()
    {
        $bd = $this->base_datos;
        Config::set('database.default', $bd );     
        $zonas = DB::table('zonas_impuesto_unico')->get();
        $detalles = array();
        
        if(count($zonas)){
            foreach($zonas as $zona){
                $detalles[] = array(
                    'id' => $zona->id,
                    'sid' => $zona->sid,
                    'nombre' => $zona->nombre,
                    'porcentaje' => $zona->porcentaje
                );
            }
        }

        return $detalles;
    }    
  
    public function url()
    {   
        $url = $this->url;
        
        if(!$url){
            $sub = str_replace('rrhhes_', '', Config::get('cliente.CLIENTE.EMPRESA'));
            $dominio = 'https://' . $sub . '.rrhh-es.com';
            $url = $dominio . '/#/login/' . $this->portal;
        }
        
        return $url;
    }
    
    static function suite()
    {           
        $sub = str_replace('rrhhes_', '', Config::get('cliente.CLIENTE.EMPRESA'));
        $url = 'https://' . $sub . '.rrhh-es.com';
        
        return $url;
    }
    
    public function misCentrosCosto()
    {
        $centrosCosto = array();
        $centros = DB::table('variables_sistema')->where('variable', 'centro_costo')->orderBy('valor1')->get();
        if(count($centros)){
            foreach($centros as $centro){
                $centrosCosto[] = array(
                    'nivel' => $centro->valor1,
                    'nombre' => $centro->valor2
                );
            }
        }
        
        return $centrosCosto;
    }
    
    public function updateCentrosCosto($centros)
    {
        $centrosCosto = array();
        $i = 1;
        $bd = $this->base_datos;
        Config::set('database.default', $bd ); 
        DB::table('variables_sistema')->where('variable', 'centro_costo')->delete();
        foreach($centros as $centro){
            $centrosCosto[] = array(
                'variable' => 'centro_costo',
                'valor1' => $i,
                'valor2' => $centro['nombre']
            );
            $i++;
        }
        DB::table('variables_sistema')->insert($centrosCosto);
        Config::set('database.default', 'principal' ); 
    }
    
    public function domicilio()
    {
        $direccion = $this->direccion;
        $comuna = $this->comuna->comuna;
        $provincia = $this->comuna->provincia->provincia;
        $domicilio = $direccion . ', comuna de ' . $comuna . ', de la ciudad de ' . $provincia;
        
        return $domicilio;
    }
    
    public function comprobarZonas($zonas)
    {
        $bd = $this->base_datos;
        Config::set('database.default', $bd );     
        $misZonas = DB::table('zonas_impuesto_unico')->get();
        $update = array();
        $create = array();
        $destroy = array();
        
        if($misZonas){
            foreach($zonas as $zona)
            {
                $isUpdate = false;
                
                if(isset($zona['id'])){    
                    foreach($misZonas as $miZona)
                    {
                        if($zona['id'] == $miZona->id){
                            $update[] = array(
                                'id' => $zona['id'],
                                'sid' => $zona['sid'],
                                'nombre' => $zona['nombre'],
                                'porcentaje' => $zona['porcentaje']
                            );
                            $isUpdate = true;
                        }                        
                        if($isUpdate){
                            break;
                        }
                    }
                }else{
                    $create[] = array(
                        'nombre' => $zona['nombre'],
                        'porcentaje' => $zona['porcentaje']
                    );
                }
            }

            foreach($misZonas as $miZona)
            {
                $isZona = false;
                foreach($zonas as $zona)
                {
                    if(isset($zona['id'])){
                        if($miZona->id == $zona['id']){
                            $isZona = true;                        
                        }
                    }
                }
                if(!$isZona){
                    $destroy[] = array(
                        'id' => $miZona->id,
                        'sid' => $miZona->sid
                    );
                }
            }
        }else{
            $create = $zonas;
        }
        
        $datos = array(
            'create' => $create,
            'update' => $update,
            'destroy' => $destroy
        );
        
        return $datos;
    }
    
    static function columna($nivel)
    {
        $columna = DB::table('variables_sistema')->where('variable', 'centro_costo')->where('valor1', $nivel)->first();
        if($columna){
            return $columna->valor2;
        }
        
        return null;
    }
    
    static function configuracion()
    {
        $bd = \Session::get('basedatos');
        Config::set('database.default', 'principal');
        $configuracion = DB::table('variables_globales')->where('variable', 'configuracion')->first();           
        
        if($configuracion->valor=='e'){
            $configuraciones = new stdClass();
            Config::set('database.default', $bd);
            $variables = DB::table('variables_sistema')->get();
            $configuraciones->configuracion = $configuracion->valor;
            foreach($variables as $variable){
                $nombre = $variable->variable;
                if($variable->valor1==1){
                    $valor = true;
                }else if($variable->valor1==0){
                    $valor = false;
                }else{
                    $valor = $variable->valor1;                
                }
                $configuraciones->$nombre = $valor;
            }
        }else{
            $configuraciones = new stdClass();
            $variables = DB::table('variables_globales')->get();
            Config::set('database.default', $bd);
            $festivos = DB::table('variables_sistema')->where('variable', 'festivos')->first();
            foreach($variables as $variable){
                $nombre = $variable->variable;
                if($variable->valor==1){
                    $valor = true;
                }else if($variable->valor==0){
                    $valor = false;
                }else{
                    $valor = $variable->valor;                
                }
                $configuraciones->$nombre = $valor;
            }
            $configuraciones->festivos = $festivos->valor1;
            $configuraciones->configuracion = $configuracion->valor;
        }                
        
        \Session::set('configuracion', $configuraciones);
        Config::set('database.default', $bd);
        
        return $configuraciones;
    }
    
    public function totalFestivos()
    {
        $configuracion = \Session::get('configuracion');
        $festivos = $configuracion->festivos;
        $total = substr_count($festivos, '0');
        
        return $total;
    }
    
    public function festivos()
    {
        $configuracion = \Session::get('configuracion');
        $festivos = $configuracion->festivos;
        $datos = array();
        $dias = Config::get('constants.dias');
        
        for($i=0,$len=strlen($festivos); $i<$len; $i++){
            $datos[] = array(
                'id' => $dias[$i]['id'],
                'dia' => $dias[$i]['dia'],
                'nombre' => $dias[$i]['value'],
                'festivo' => $festivos[$i] ? true : false
            );
        }
            
        return $datos;
    }
    
    public function arrayDiasHabiles()
    {
        $configuracion = \Session::get('configuracion');
        $habiles = $configuracion->festivos;
        $datos = array();
        $dias = Config::get('constants.dias');
        
        for($i=0,$len=strlen($habiles); $i<$len; $i++){
            if($habiles[$i]){
                $datos[] = $dias[$i]['id'];
            }
        }
            
        return $datos;
    }
    
    public function totalDiasHabiles($descontarFeriados=false)
    {
        $mes = \Session::get('mesActivo');
        $desde = $mes->mes;
        $hasta = $mes->fechaRemuneracion;
        $count = 0;
        $habiles = $this->arrayDiasHabiles();
        $feriados = array();
        if($descontarFeriados){
            $feriados = Feriado::feriados($mes->mes, $mes->fechaRemuneracion);
        }

        while($desde<=$hasta){            
            $weekDay = date('N', strtotime($desde));
            if(in_array($weekDay, $habiles) && !in_array($desde, $feriados) && $weekDay!=7){
                $count++;
            }
            $desde = date('Y-m-d', strtotime('+1 day', strtotime($desde)));
        }
        
        return $count;
    }
    
    public function totalDiasNoHabiles()
    {
        $mes = \Session::get('mesActivo');
        $desde = $mes->mes;
        $hasta = $mes->fechaRemuneracion;
        $count = 0;

        while($desde<=$hasta){            
            $weekDay = date('N', strtotime($desde));
            if($weekDay==7){
                $count++;
            }
            $desde = date('Y-m-d', strtotime('+1 day', strtotime($desde)));
        }
        
        return $count;
    }
    
    static function variableConfiguracion($var)
    {
        $configuracion = \Session::get('configuracion');
        if(isset($configuracion->$var)){
            return $configuracion->$var;                
        }
        
        return false;
    }    
    
    static function habilitadas($empresas)
    {
        $bd = \Session::get('basedatos');
        Config::set('database.default', 'principal');
        $habilitadas = DB::table('empresas')->where('habilitada', 1)->lists('id');
        Config::set('database.default', $bd);
        
        foreach($empresas as $index => $empresa){
            if(!in_array($empresa['id'], $habilitadas)){
                unset($empresas[$index]);
            }
        }
        
        return $empresas;
    }

    public function porcentajeMutual()
    {
        $idAnio = \Session::get('mesActivo')->idAnio;
        $fijo = 0;
        $adicional = 0;
        $extraordinaria = 0;
        $sanna = 0;
        
        $mutual = Mutual::where('anio_id', $idAnio)->first();
        if($mutual){
            $fijo = $mutual->tasa_fija;
            $adicional = $mutual->tasa_adicional;
            $extraordinaria = $mutual->extraordinaria;
            $sanna = $mutual->sanna;
        }
        
        $datos = array(
            'fijo' => $fijo,
            'adicional' => $adicional,
            'extraordinaria' => $extraordinaria,
            'sanna' => $sanna
        );
        
        return $datos;
    }
    
    static function feriadosVacaciones()
    {
        $feriados = FeriadoVacaciones::all();
        $detalle = array();
        
        if($feriados->count()){
            foreach($feriados as $feriado){
                $detalle[] = array(
                    'id' => $feriado->id,
                    'sid' => $feriado->sid,
                    'fecha' => $feriado->fecha
                );
            }    
        }
        
        return $detalle;
    }
    
    static function isMutual()
    {       
        $empresa = \Session::get('empresa');
        if($empresa->mutual_id==263){
            return false;
        }
        return true;
    }
    
    static function isCaja()
    {        
        $empresa = \Session::get('empresa');
        if($empresa->caja_id==257){
            return false;
        }
        return true;
    }
    
    static function isSuccessCME()
    {
        $sub = str_replace('rrhhes_', '', Config::get('cliente.CLIENTE.EMPRESA'));
        //$sub = 'demo';
        $url = 'http://' . $sub . '.cme-es.com//';
        if (@file_get_contents($url,false,NULL,0,1))
        {
            return true;
        }
        return false;
    }
    
    public function ultimoMes()
    {
        $datosMesDeTrabajo = new stdClass();          
        $mesDeTrabajo = MesDeTrabajo::orderBy('mes', 'DESC')->first();  
        
        if($mesDeTrabajo){
            $datosMesDeTrabajo->id = $mesDeTrabajo->id;
            $datosMesDeTrabajo->mes = $mesDeTrabajo->mes;
            $datosMesDeTrabajo->mesActivo = $mesDeTrabajo->nombre . ' ' . $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->nombre = $mesDeTrabajo->nombre;
            $datosMesDeTrabajo->idAnio = $mesDeTrabajo->anio_id;
            $datosMesDeTrabajo->anio = $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->fechaRemuneracion = $mesDeTrabajo->fecha_remuneracion;
        }
        
        return $datosMesDeTrabajo;
    }
    
    public function primerMes()
    {
        $datosMesDeTrabajo = new stdClass();          
        $mesDeTrabajo = MesDeTrabajo::orderBy('mes')->first();
        
        if($mesDeTrabajo){
            $datosMesDeTrabajo->id = $mesDeTrabajo->id;
            $datosMesDeTrabajo->mes = $mesDeTrabajo->mes;
            $datosMesDeTrabajo->mesActivo = $mesDeTrabajo->nombre . ' ' . $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->nombre = $mesDeTrabajo->nombre;
            $datosMesDeTrabajo->idAnio = $mesDeTrabajo->anio_id;
            $datosMesDeTrabajo->anio = $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->fechaRemuneracion = $mesDeTrabajo->fecha_remuneracion;
        }
        
        return $datosMesDeTrabajo;
    }
	
    public function centrosAsignables()
    {
        $centros = CentroCosto::listaCentrosCosto();
        $count = 0;
        $niveles = $this->niveles_centro_costo;

        if(count($centros)){
            foreach($centros as $centro){
                if($centro['nivel']==$niveles){
                    $count++;
                }
            }
        }

        return $count;
    }
    
    public function mutuales()
    {
        Config::set('database.default', $this->base_datos);
        $mutuales = Mutual::all();
        $datos = array();
        
        if($mutuales->count()){
            foreach($mutuales as $mutual){
                $datos[] = array(
                    'id' => $mutual->id,
                    'mutual' => array(
                        'id' => $mutual->mutual->id,
                        'nombre' => $mutual->mutual->glosa
                    ),
                    'codigo' => $mutual->codigo,
                    'tasaFija' => $mutual->tasa_fija,
                    'tasaAdicional' => $mutual->tasa_adicional,
                    'extraordinaria' => $mutual->extraordinaria,
                    'sanna' => $mutual->sanna,
                    'idAnio' => $mutual->anio_id,
                    'anio' => array(
                        'id' => $mutual->anioRemuneracion->id,
                        'nombre' => $mutual->anioRemuneracion->anio
                    )                        
                );
            }
        }
        Config::set('database.default', 'principal');
        
        return $datos;
    }
    
    public function cajas()
    {
        Config::set('database.default', $this->base_datos);
        $cajas = Caja::all();
        $datos = array();
        
        if($cajas->count()){
            foreach($cajas as $caja){
                $datos[] = array(
                    'id' => $caja->id,
                    'caja' => array(
                        'id' => $caja->caja->id,
                        'nombre' => $caja->caja->glosa
                    ),
                    'codigo' => $caja->codigo,
                    'idAnio' => $caja->anio_id,
                    'anio' => array(
                        'id' => $caja->anioRemuneracion->id,
                        'nombre' => $caja->anioRemuneracion->anio
                    )                        
                );
            }
        }
        Config::set('database.default', 'principal');
        
        return $datos;
    }
    
    public function actualizarMutuales($mutuales)
    {
        if(count($mutuales)){
            foreach($mutuales as $mutual){
                $nuevaMutual = Mutual::find($mutual['id']);
                $nuevaMutual->codigo = $mutual['codigo'];
                $nuevaMutual->extraordinaria = $mutual['extraordinaria'];
                $nuevaMutual->sanna = $mutual['sanna'];
                $nuevaMutual->tasa_fija = $mutual['tasaFija'];
                $nuevaMutual->tasa_adicional = $mutual['tasaAdicional'];
                $nuevaMutual->mutual_id = $mutual['mutual']['id'];
                $nuevaMutual->save();
            }
        }
    }
    
    public function actualizarCajas($cajas)
    {
        if(count($cajas)){
            foreach($cajas as $caja){
                $nuevaCaja = Caja::find($caja['id']);
                $nuevaCaja->codigo = $caja['codigo'];
                $nuevaCaja->caja_id = $caja['caja']['id'];
                $nuevaCaja->save();
            }
        }
    }
    
    public function aniosEmpresa()
    {
        Config::set('database.default', $this->base_datos);
        $anios = AnioRemuneracion::all();
        $datos = array();
        
        if($anios->count()){
            foreach($anios as $anio){
                $datos[] = array(
                    'id' => $anio->id,
                    'nombre' => $anio->anio                   
                );
            }
        }
            
        Config::set('database.default', 'principal');
        
        return $datos;
    }
    
    static function errores($datos){

        if($datos['id']){
            $rules =    array(
                'rut' => 'required|unique:empresas,rut,'.$datos['id'],
                'razon_social' => 'required|unique:empresas,razon_social,'.$datos['id']
            );
        }else{
            $rules =    array(
                'rut' => 'required|unique:empresas,rut',
                'razon_social' => 'required|unique:empresas,razon_social'
            );
        }

        $message =  array(
            'rut.required' => 'Obligatorio!',
            'representante_rut.required' => 'Obligatorio!',
            'representante_nombre.required' => 'Obligatorio!',
            'representante_direccion.required' => 'Obligatorio!',
            'representante_comuna_id.required' => 'Obligatorio!',
            'rut.unique' => 'El RUT ya se encuentra registrado!',
            'razon_social.required' => 'Obligatorio!',
            'razon_social.unique' => 'Ya se encuentra registrada!'
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            // la validacion tubo errores
            return $validation->getMessageBag()->toArray();
        }else{
            // no hubo errores de validacion
            return false;
        }
    }
}