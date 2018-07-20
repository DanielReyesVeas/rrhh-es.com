<?php

class AniosRemuneracionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#gratificacion');
        $aniosRemuneraciones = AnioRemuneracion::all();
        $listaAniosRemuneraciones=array();
                
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                $listaAniosRemuneraciones[]=array(
                    'id' => $anioRemuneracion->id,
                    'sid' => $anioRemuneracion->sid,
                    'nombre' => $anioRemuneracion->anio,
                    'fecha' => $anioRemuneracion->gratificacion ? $anioRemuneracion->gratificacion : '',
                    'mes' => $anioRemuneracion->gratificacion ? Funciones::obtenerMesTexto(date('m', strtotime($anioRemuneracion->gratificacion))) : '',
                    'pagar' => $anioRemuneracion->pagar ? true : false,
                    'utilidad' => $anioRemuneracion->utilidad
                );
            }
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaAniosRemuneraciones
        );
        
        return Response::json($datos);
    }
            
    public function datosCierre()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'anios' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cierre-mensual');
        $aniosRemuneraciones = AnioRemuneracion::all();
        $listaAniosRemuneraciones=array();
        $bd = \Session::get('basedatos');
        
        $maxAnio=0;
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                $listaAniosRemuneraciones[]=array(
                    'id' => $anioRemuneracion->id,
                    'sid' => $anioRemuneracion->sid,
                    'nombre' => $anioRemuneracion->anio                    
                );
                $maxAnio = AnioRemuneracion::orderBy('anio', 'DESC')->first()->anio;
                $max = AnioRemuneracion::orderBy('anio', 'DESC')->get();
            }
        }
        
        



        /*****************************************************************/
        /*  Modificado para tomar el anio seleccionado en el formulario
        /*****************************************************************/
        $anioSeleccionado = Input::get('anio');
        if($anioSeleccionado){
            $anioRemuneracion = AnioRemuneracion::where('anio', $anioSeleccionado)->first();
        }else{
            $id = \Session::get('mesActivo')->idAnio;        
            $anioRemuneracion = AnioRemuneracion::find($id);
        }
        /******************************************************************/

        $datosAnioRemuneracion = array();
        if($anioRemuneracion){
            $datosAnioRemuneracion=array(
                'id' => $anioRemuneracion->id,
                'sid' => $anioRemuneracion->sid,
                'nombre' => $anioRemuneracion->anio,
                'estadoMeses' => $anioRemuneracion->estadoMeses(),
                'meses' => $anioRemuneracion->meses()
            );
        }

        // comprobar que el anio no existe para definir que es un nuevo anio
        $listaAnios =  Funciones::array_column($listaAniosRemuneraciones, 'nombre');
        
        $datos = array(
            'accesos' => $permisos,
            'anios' => $listaAniosRemuneraciones,
            'datos' => $datosAnioRemuneracion,
            'isNuevoAnio' => $anioRemuneracion->isNuevoAnio() && ($anioRemuneracion->anio==$maxAnio && $maxAnio<date("Y"))
        );
        
        return Response::json($datos);
    }
    
    public function datosCentralizacion($sid)
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'anios' => array(), 'permisos' => array()));
        }
        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#centralizacion');
        $aniosRemuneraciones = AnioRemuneracion::orderBy('anio', 'DESC')->get();
        $listaAniosRemuneraciones=array();
        
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                $listaAniosRemuneraciones[]=array(
                    'id' => $anioRemuneracion->id,
                    'sid' => $anioRemuneracion->sid,
                    'nombre' => $anioRemuneracion->anio                    
                );
            }
        }
        
        $datosAnioRemuneracion = array();
        $mes = \Session::get('mesActivo');  
        
        if(!$sid){
            $id = $mes->idAnio;        
            $anioRemuneracion = AnioRemuneracion::find($id);
        }else{
            $anioRemuneracion = AnioRemuneracion::whereSid($sid)->first();
        }
        
        if($anioRemuneracion){
            $datosAnioRemuneracion=array(
                'id' => $anioRemuneracion->id,
                'sid' => $anioRemuneracion->sid,
                'nombre' => $anioRemuneracion->anio,
                'estadoMeses' => $anioRemuneracion->estadoMeses(),
                'meses' => $anioRemuneracion->meses()
            );
        }
        
        $datos = array(
            'accesos' => $permisos,
            'anios' => $listaAniosRemuneraciones,
            'datos' => $datosAnioRemuneracion,
            'isLiquidaciones' => AnioRemuneracion::isLiquidaciones(),
            'isIndicadores' => $mes->indicadores,
            'isCentralizado' => $anioRemuneracion->isCentralizado($mes->mes),
            'anio' => $anioRemuneracion
        );
        
        return Response::json($datos);
    }
    
    public function cerrarMeses()
    {
        $datos = Input::all();
        $idAnio = \Session::get('mesActivo')->idAnio;
        $anio = AnioRemuneracion::find($idAnio);
    
        foreach($datos as $mes){
            if($mes['iniciado']){                    
                $nombre = strtolower($mes['nombre']);
                $anio->$nombre = $mes['abierto'];
            }
        }
                    
        $anio->save();

        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function feriados()
    {
        $datos = Input::all();
        
        $feriados = $datos['feriados'];
        $anio = $datos['anio'];
        $mes = $datos['mes'];
        
        $feriados = Feriado::comprobar($feriados, $mes);
        
        Feriado::masivo($feriados, $anio);

        $respuesta=array(
            'anio' => $anio,
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function calendario()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#semana-corrida');        
        $empresa = \Session::get('empresa');
        $festivos = $empresa->festivos();
        $aniosRemuneraciones = AnioRemuneracion::all();
        $listaAniosRemuneraciones=array();
        
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                $listaAniosRemuneraciones[]=array(
                    'id' => $anioRemuneracion->id,
                    'sid' => $anioRemuneracion->sid,
                    'nombre' => $anioRemuneracion->anio,
                    'meses' => $anioRemuneracion->mesesFestivos()
                );
            }
        }

        $datos=array(
            'anios' => $listaAniosRemuneraciones,
            'festivos' => $festivos,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function feriadosVacaciones()
    {
        $datos = Input::all();
        
        $feriados = $datos['feriados'];
        $anio = $datos['anio'];
        $mes = $datos['mes'];
        
        $feriados = FeriadoVacaciones::comprobar($feriados, $mes);
        
        FeriadoVacaciones::masivo($feriados, $anio);

        $respuesta=array(
            'anio' => $anio,
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'f' => $feriados
        );
        
        return Response::json($respuesta);
    }
    
    public function calendarioVacaciones()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores-vacaciones');
        
        $aniosRemuneraciones = AnioRemuneracion::all();
        $listaAniosRemuneraciones=array();
        
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                $listaAniosRemuneraciones[]=array(
                    'id' => $anioRemuneracion->id,
                    'sid' => $anioRemuneracion->sid,
                    'nombre' => $anioRemuneracion->anio,
                    'meses' => $anioRemuneracion->mesesFestivosVacaciones()
                );
            }
        }

        $datos=array(
            'anios' => $listaAniosRemuneraciones,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function gratificacion()
    {
        $datos = Input::all();
        $anios = array();
        foreach($datos as $dato){
            $anio = AnioRemuneracion::find($dato['id']);
            $anio->pagar = $dato['pagar'];
            $anio->gratificacion = $dato['fecha'];
            $anio->save();
            $anios[] = $anio;
        }
                    
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'datos' => $anios
        );
        
        return Response::json($respuesta);
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
        $errores = ValorIndicador::errores($datos);      
        
        if(!$errores){
            $valorIndicador = new ValorIndicador();
            $valorIndicador->indicador_id = $datos['indicador_id'];
            $valorIndicador->valor = $datos['valor'];
            $valorIndicador->fecha = $datos['fecha'];
            $valorIndicador->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $valorIndicador->id
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    
   
    public function show($sid)
    {
        $anioRemuneracion = AnioRemuneracion::whereSid($sid)->first();

        $datosAnioRemuneracion=array(
            'id' => $anioRemuneracion->id,
            'sid' => $anioRemuneracion->sid,
            'nombre' => $anioRemuneracion->anio,
            'enero' => $anioRemuneracion->enero ? true : false,
            'febrero' => $anioRemuneracion->febrero ? true : false,
            'marzo' => $anioRemuneracion->marzo ? true : false,
            'abril' => $anioRemuneracion->abril ? true : false,
            'mayo' => $anioRemuneracion->mayo ? true : false,
            'junio' => $anioRemuneracion->junio ? true : false,
            'julio' => $anioRemuneracion->julio ? true : false,
            'agosto' => $anioRemuneracion->agosto ? true : false,
            'septiembre' => $anioRemuneracion->septiembre ? true : false,
            'octubre' => $anioRemuneracion->octubre ? true : false,
            'noviembre' => $anioRemuneracion->noviembre ? true : false,
            'diciembre' => $anioRemuneracion->diciembre ? true : false,
            'meses' => $anioRemuneracion->meses()
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosAnioRemuneracion
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
        $anio = AnioRemuneracion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = AnioRemuneracion::errores($datos);       
        
        if(!$errores and $anio){
            $anio->gratificacion = $datos['fecha'];
            $anio->pagar = $datos['pagar'];
            $anio->utilidad = $datos['utilidad'];
            $anio->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $anio->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);
        
        /*$valorIndicador = ValorIndicador::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = ValorIndicador::errores($datos);       
        
        if(!$errores and $valorIndicador){
            $valorIndicador->indicador_id = $datos['indicador_id'];
            $valorIndicador->valor = $datos['valor'];
            $valorIndicador->fecha = $datos['fecha'];
            $valorIndicador->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $valorIndicador->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);*/
    }
    
    public function modificarFestivosSemanaCorrida()
    {
        $datos = Input::all();
        $festivos = '';
        
        foreach($datos as $dato){
            if($dato['festivo']){
                $festivos .= '1';
            }else{
                $festivos .= '0';
            }
        }
        
        $configuracion = VariableSistema::where('variable', 'festivos')->first();
        $configuracion->valor1 = $festivos;
        $configuracion->save();
        Empresa::configuracion();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'datos' => $festivos,
            'conf' => $configuracion
        );

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
        AnioRemuneracion::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'id' => Input::get('id'),
            'sid' => Input::get('sid'),
            'nombre' => Input::get('nombre'),
            'pagar' => Input::get('pagar'),
            'fecha' => Input::get('fecha'),
            'utilidad' => Input::get('utilidad')
        );
        return $datos;
    }

}