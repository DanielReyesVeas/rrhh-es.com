<?php

class DescuentosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $descuentos = Descuento::all();
        $listaDescuentos=array();
        if( $descuentos->count() ){
            foreach( $descuentos as $descuento ){
                $listaDescuentos[]=array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'idTrabajador' => $descuento->trabajador_id,
                    'idTipoDescuento' => $descuento->tipo_descuento_id,
                    'tipo' => array(
                        'id' => $descuento->tipoDescuento ? $descuento->tipoDescuento->id : "",
                        'nombre' => $descuento->tipoDescuento ? $descuento->tipoDescuento->nombre : ""
                    ),
                    'mes' => array(
                        'id' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->id : "",
                        'nombre' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->nombre : "",
                        'mes' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->mes : ""
                    ), 
                    'porMes' => $descuento->por_mes ? true : false,
                    'rangoMeses' => $descuento->rango_meses ? true : false,
                    'permanente' => $descuento->permanente ? true : false,
                    'todosAnios' => $descuento->todos_anios ? true : false,
                    'moneda' => $descuento->moneda,
                    'monto' => $descuento->monto
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaDescuentos
        );
        
        return Response::json($datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $datos = $this->get_datos_formulario();
        $errores = Descuento::errores($datos);      
        
        if(!$errores){
            if($datos['tipo_descuento_id']==0){
                $trabajador = Trabajador::find($datos['trabajador_id']);
                $ficha = $trabajador->ficha();
                if($ficha){
                    $afp = $ficha->afp_id;
                    if(!$afp){
                        $afp = $ficha->afp_seguro_id;
                    }
                    if(!$afp){
                        $respuesta=array(
                            'success' => false,
                            'mensaje' => "La acción no pudo ser completada porque el trabajador no está en AFP.",
                            'errores' => $errores
                        );
                        return Response::json($respuesta);
                    }else{
                        $tipo = TipoDescuento::where('estructura_descuento_id', 7)->where('nombre', $afp)->first();
                        $datos['tipo_descuento_id'] = $tipo->id;
                    }
                }
            }
            $descuento = new Descuento();
            $descuento->sid = Funciones::generarSID();
            $descuento->trabajador_id = $datos['trabajador_id'];
            $descuento->tipo_descuento_id = $datos['tipo_descuento_id'];
            $descuento->mes_id = $datos['mes_id'];
            $descuento->mes = $datos['mes'];
            $descuento->desde = $datos['desde'];
            $descuento->hasta = $datos['hasta'];
            $descuento->por_mes = $datos['por_mes'];
            $descuento->rango_meses = $datos['rango_meses'];
            $descuento->permanente = $datos['permanente'];
            $descuento->todos_anios = $datos['todos_anios'];
            $descuento->moneda = $datos['moneda'];
            $descuento->monto = $datos['monto'];
            $descuento->save();
            
            if($descuento->moneda=='$'){
                $monto = $descuento->moneda . $descuento->monto;
            }else{
                $monto = $descuento->monto . ' ' . $descuento->moneda;
            }
            
            $trabajador = $descuento->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-descuentos', $trabajador->id, $ficha->nombreCompleto(), 'Create', $descuento->id, $monto, 'Descuentos Trabajadores', $descuento->tipo_descuento_id, $descuento->tipoDescuento->nombre);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $descuento->sid
            );
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        
        return Response::json($respuesta);
    }
    
    public function eliminarPermanente()
    {
        $datos = Input::all();
        $mes = \Session::get('mesActivo');
        
        $descuento = Descuento::whereSid($datos['sid'])->first();        
        $descuento->hasta = $mes->mes;
        $descuento->save();
        
        if($descuento->moneda=='$'){
            $monto = $descuento->moneda . $descuento->monto;
        }else{
            $monto = $descuento->monto . ' '. $descuento->moneda;
        }
        
        $trabajador = $descuento->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#ingreso-descuentos', $trabajador->id, $ficha->nombreCompleto(), 'Delete Parcial', $descuento->id, $monto, 'Descuentos Trabajadores', $descuento->tipo_descuento_id, $descuento->tipoDescuento->nombre);
        
        $respuesta=array(
            'success' => true,
            'datos' => $datos,
            'mensaje' => "La Información fue eliminada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    
    public function ingresoMasivo()
    {
        $datos = Input::all();
    
        foreach($datos['descuentos'] as $des){
            $errores = Descuento::errores($des);   
            if(!$errores){
                $descuento = new Descuento();
                $descuento->sid = Funciones::generarSID();
                $descuento->trabajador_id = $des['trabajador_id'];
                $descuento->tipo_descuento_id = $des['tipo_descuento_id'];
                $descuento->mes_id = $des['mes_id'];
                $descuento->mes = $des['mes'];
                $descuento->desde = $des['desde'];
                $descuento->hasta = $des['hasta'];
                $descuento->por_mes = $des['por_mes'];
                $descuento->rango_meses = $des['rango_meses'];
                $descuento->permanente = $des['permanente'];
                $descuento->todos_anios = $des['todos_anios'];
                $descuento->moneda = $des['moneda'];
                $descuento->monto = $des['monto'];
                $descuento->save(); 
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente"
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            }
        }
        
        return Response::json($respuesta);
    }
    
    public function generarIngresoMasivo()
    {
        $datos = Input::all();
        $descuentoIngresar = $datos['descuento'];
        $trabajadores = $datos['trabajadores'];
        $idMes = null;
        $mes = null;
        $permanente = false;
        $porMes = false;
        $rangoMeses = false;
        $desde = null;
        $hasta = null;
        $idTipoDescuento = $descuentoIngresar['id'];
        
        foreach($trabajadores as $trabajador){
            $rut = $trabajador['trabajador']['rut'];
            $idTrabajador = Trabajador::where('rut', $rut)->first()->id;
            if(!$trabajador['trabajador']['descuento']['temporalidad']){
                $mes = \Session::get('mesActivo');
                $idMes = $mes->id;
                $mes = $mes->mes;
                $porMes = true;
            }else if($trabajador['trabajador']['descuento']['temporalidad']=='permanente'){
                $permanente = true;
            }else{
                $rangoMeses = true;
                $meses = Funciones::rangoMeses($trabajador['trabajador']['descuento']['temporalidad']);
                $desde = $meses->desde;
                $hasta = $meses->hasta;
            }
            
            $descuento = new Descuento();
            $descuento->sid = Funciones::generarSID();
            $descuento->trabajador_id = $idTrabajador;
            $descuento->mes_id = $idMes;
            $descuento->mes = $mes;
            $descuento->tipo_descuento_id = $idTipoDescuento;
            $descuento->permanente = $permanente;
            $descuento->por_mes = $porMes;
            $descuento->todos_anios = false;
            $descuento->rango_meses = $rangoMeses;
            $descuento->desde = $desde;
            $descuento->hasta = $hasta;
            $descuento->moneda = $trabajador['trabajador']['descuento']['moneda'];
            $descuento->monto = $trabajador['trabajador']['descuento']['monto'];
            $descuento->save();                        
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function importarPlanilla()
    {
        $insert = array();
        $descuentoIngresar = Input::get('descuento');
        
        if(Input::hasFile('file')){            
            $file = Input::file('file')->getRealPath();
            $data = Excel::load($file, function($reader){                
            })->get();
            if(!empty($data) && $data->count()){
                foreach($data as $key => $value){
                    if(isset($value->rut) && isset($value->moneda) && isset($value->monto)){
                        $insert[] = array(
                            'rut' => $value->rut,              
                            'moneda' => $value->moneda,                 
                            'monto' => $value->monto,                 
                            'temporalidad' => $value->temporalidad               
                        );
                    }else{
                        $errores = array();
                        $errores[] = 'El formato no corresponde con el archivo de la planilla. Por favor vuelva a descargar la planilla.';
                    }
                }
            }
        }
        
        if(!isset($errores)){
            $errores = $this->comprobarErrores($insert);
        }
        
        if(!$errores){            
            $tabla = array();
            foreach($insert as $dato){
                $trabajador = Trabajador::where('rut', $dato['rut'])->first();
                $nombreCompleto = $trabajador->ficha()->nombreCompleto();
                
                $tabla[] = array(
                    'trabajador' => array(
                        'id' => $trabajador->id,
                        'rut' => $dato['rut'],
                        'rutFormato' => Funciones::formatear_rut($dato['rut']),
                        'nombreCompleto' => $nombreCompleto,
                        'descuento' => array(
                            'moneda' => $dato['moneda'],
                            'monto' => $dato['monto'],
                            'temporalidad' => $dato['temporalidad']
                        )
                    )                        
                );
            }
            
            $respuesta=array(
                'success' => true,
                'mensaje' => "La Información fue almacenada correctamente",
                'datos' => $tabla,
                'descuento' => $descuentoIngresar
            );
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        }
        
        
        return Response::json($respuesta);
    }
    
    public function importarPlanillaMasivo()
    {
        $array = array();

        if(Input::hasFile('file')){   
            $file = Input::file('file')->getRealPath();
            $data = Excel::load($file, function($reader){                
            })->noHeading()->all()->toArray();
            
            $array = $this->formatearArray($data);
            
            if(!isset($errores)){
                $errores = $this->comprobarErroresMasivo($array);
            }
            
            if(!$errores){                                        
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente",
                    'datos' => $array,
                    'descuento' => $data
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            }
        }else{
            $errores = array();
            $errores[] = 'El formato no corresponde con el archivo de la planilla. Por favor vuelva a descargar la planilla.';
        }

        return Response::json($respuesta);
    }
    
    public function generarIngresoMasivoDescuentos()
    {
        $datos = Input::all();
        $trabajadores = $datos['datos'];
        $descuentos = $datos['descuentos'];
        $mes = \Session::get('mesActivo');
        
        foreach($trabajadores as $trabajador){
            $rut = Funciones::quitar_formato_rut($trabajador['rut']);
            $trab = Trabajador::where('rut', $rut)->first();
            
            foreach($trabajador['descuentos'] as $key => $value){
                if($value > 0){
                    $codigo = $descuentos[$key];
                    $descuento = TipoDescuento::where('codigo', $codigo['codigo'])->first();
                    if($descuento){
                        $nuevoDescuento = new Descuento();
                        $nuevoDescuento->sid = Funciones::generarSID();
                        $nuevoDescuento->trabajador_id = $trab['id'];
                        $nuevoDescuento->tipo_descuento_id = $descuento['id'];
                        $nuevoDescuento->mes_id = $mes->id;
                        $nuevoDescuento->moneda = '$';
                        $nuevoDescuento->monto = $value;
                        $nuevoDescuento->por_mes = 1;
                        $nuevoDescuento->rango_meses = 0;
                        $nuevoDescuento->permanente = 0;
                        $nuevoDescuento->todos_anios = 0;
                        $nuevoDescuento->mes = $mes->mes;
                        $nuevoDescuento->desde = NULL;
                        $nuevoDescuento->hasta = NULL;
                        $nuevoDescuento->save();
                        
                        if($nuevoDescuento->moneda=='$'){
                            $monto = $nuevoDescuento->moneda . $nuevoDescuento->monto;
                        }else{
                            $monto = $nuevoDescuento->monto . ' ' . $nuevoDescuento->moneda;
                        }

                        $trabajador = $nuevoDescuento->trabajador;
                        $ficha = $trabajador->ficha();
                        Logs::crearLog('#ingreso-descuentos', $trabajador->id, $ficha->nombreCompleto(), 'Create', $nuevoDescuento->id, $monto, 'Ingreso Masivo', $nuevoDescuento->tipo_descuento_id, $nuevoDescuento->tipoDescuento->nombre);
                    }
                }
            }
        }
        
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function formatearArray($array)
    {
        $arreglo = array();
        $descuentos = array();
        foreach($array[0] as $key => $value){
            $descuentos[] = $value;
            $encabezado[] = array(
                'codigo' => $value,
                'nombre' => $array[1][$key],
                'active' => false
            );
        }
        foreach($array as $arr){
            if($arr[0]){
                $active = false;
                $misDescuentos = array();
                foreach($descuentos as $key => $value){
                    if($value){
                        if($arr[$key]){                            
                            if(!$encabezado[$key]['active']){
                                $encabezado[$key]['active'] = true;
                            }
                            $active = true;
                        }
                        $misDescuentos[$key] =  $arr[$key] ? $arr[$key] : 0;
                    }
                }
                $arreglo[] = array(
                    'rut' => $arr[0],
                    'nombreCompleto' => $arr[1],
                    'descuentos' => $misDescuentos,
                    'active' => $active
                );
            }
        }
        foreach($encabezado as $key => $value){
            if(!$value['active']){
                unset($encabezado[$key]);            
                unset($descuentos[$key]);            
            }
        }
        foreach($arreglo as $key => $value){
            if(!$value['active']){
                unset($arreglo[$key]);                
            }else{
                foreach($value['descuentos'] as $llave => $valor){
                    if(!in_array($llave, array_keys($descuentos))){
                        unset($arreglo[$key]['descuentos'][$llave]);
                    }
                }
            }
        }
        
        $datos = array(
            'encabezado' => $encabezado,
            'datos' => array_values($arreglo)
        );
        
        return $datos;
    }
    
    public function comprobarErroresMasivo($datos)
    {
        if(!count($datos['datos']) || !count($datos['encabezado'])){
            $listaErrores = array();
            $listaErrores[] = 'El archivo no contiene datos.';
            return $listaErrores;
        }
       
        return ;
    }
    
    public function comprobarErrores($datos)
    {
        $trabajadores = Trabajador::all();
        $ruts = $trabajadores->lists('rut');
        $listaErrores = array();
        
        foreach($datos as $dato){
            if($dato){
                if(!in_array($dato['rut'], $ruts)){
                    $listaErrores[] = 'El trabajador con RUT: ' . Funciones::formatear_rut($dato['rut']) . ' no existe.';
                }
                if($dato['moneda']!='$' && $dato['moneda']!='UF' && $dato['moneda']!='UTM'){
                    $listaErrores[] = 'Formato de Moneda "' . $dato['moneda'] . '" incorrecto, recuerda que los formatos son $, UF o UTM.';
                }
                if(!is_numeric($dato['monto'])){
                    $listaErrores[] = 'Formato del Monto "' . $dato['monto'] . '" incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                }
                if(strtolower($dato['temporalidad'])!='permanente' && $dato['temporalidad']!=null && !Funciones::comprobarFecha($dato['temporalidad'])){
                    $listaErrores[] = 'Formato de Temporalidad "' . $dato['temporalidad'] . '" incorrecto, recuerda que este campo puede estar en blanco, "permanente" o en un rango de fechas "03-2017 - 06-2017".';
                }
            }
        }
        
        return $listaErrores;
    }    
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $descuento = Descuento::whereSid($sid)->first();
        $nombre = $descuento->tipoDescuento->nombre;
        if($descuento->tipoDescuento->estructura_descuento_id==7){
            $nombre = 'Cuenta de Ahorro AFP ' . $descuento->tipoDescuento->nombreAfp();
        }
        $datos=array(
            'id' => $descuento->id,
            'sid' => $descuento->sid,            
            'mes' => array(
                'id' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->id : "",
                'sid' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->sid : "",
                'nombre' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->nombre : "",
                'mes' => $descuento->mesDeTrabajo ? $descuento->mesDeTrabajo->mes : ""
            ), 
            'desde' => $descuento->desde,
            'hasta' => $descuento->hasta,
            'porMes' => $descuento->por_mes ? true : false,
            'rangoMeses' => $descuento->rango_meses ? true : false,
            'permanente' => $descuento->permanente ? true : false,
            'todosAnios' => $descuento->todos_anios ? true : false,
            'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at)),
            'monto' => $descuento->monto,
            'moneda' => $descuento->moneda,
            'tipo' => array(
                'nombre' => $nombre,
                'sid' => $descuento->tipoDescuento->sid,
                'id' => $descuento->tipoDescuento->id,
            ),            
            'trabajador' => $descuento->trabajadorDescuento()
        );
        return Response::json($datos);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($sid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($sid)
    {
        $descuento = Descuento::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Descuento::errores($datos);       
        
        if(!$errores and $descuento){
            $descuento->trabajador_id = $datos['trabajador_id'];
            $descuento->tipo_descuento_id = $datos['tipo_descuento_id'];
            $descuento->mes_id = $datos['mes_id'];
            $descuento->mes = $datos['mes'];
            $descuento->desde = $datos['desde'];
            $descuento->hasta = $datos['hasta'];
            $descuento->por_mes = $datos['por_mes'];
            $descuento->rango_meses = $datos['rango_meses'];
            $descuento->permanente = $datos['permanente'];
            $descuento->todos_anios = $datos['todos_anios'];
            $descuento->moneda = $datos['moneda'];
            $descuento->monto = $datos['monto'];
            $descuento->save();
            
            if($descuento->moneda=='$'){
                $monto = $descuento->moneda . $descuento->monto;
            }else{
                $monto = $descuento->monto . ' ' . $descuento->moneda;
            }
            
            $trabajador = $descuento->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-descuentos', $trabajador->id, $ficha->nombreCompleto(), 'Update', $descuento->id, $monto, 'Descuentos Trabajadores', $descuento->tipo_descuento_id, $descuento->tipoDescuento->nombre);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $descuento->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {
        $mensaje="La Información fue eliminada correctamente";
        $descuento = Descuento::whereSid($sid)->first();
        
        if($descuento['moneda']=='$'){
            $monto = $descuento['moneda'] . $descuento['monto'];
        }else{
            $monto = $descuento['monto'] . $descuento['moneda'];
        }
        
        $trabajador = $descuento->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#ingreso-descuentos', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $descuento['id'], $monto, 'Descuentos Trabajadores', $descuento['tipo_haber_id'], $descuento->tipoDescuento->nombre);
        
        $descuento->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }    
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'tipo_descuento_id' => Input::get('idTipoDescuento'),
            'mes_id' => Input::get('idMes'),
            'mes' => Input::get('mes'),
            'desde' => Input::get('desde'),
            'hasta' => Input::get('hasta'),
            'por_mes' => Input::get('porMes'),
            'rango_meses' => Input::get('rangoMeses'),
            'permanente' => Input::get('permanente'),
            'todos_anios' => Input::get('todosAnios'),
            'moneda' => Input::get('moneda'),
            'monto' => Input::get('monto')
        );
        return $datos;
    }

}