<?php

class TablaImpuestoUnicoController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tabla-impuesto-unico');
        $mes = \Session::get('mesActivo')->mes;
        $tablaImpuestoUnico = TablaImpuestoUnico::where('mes', $mes)->orderBy('tramo')->get();
        
        $listaTablaImpuestoUnico=array();
        if( $tablaImpuestoUnico->count() ){
            foreach( $tablaImpuestoUnico as $tabla ){
                $listaTablaImpuestoUnico[]=array(
                    'id' => $tabla->id,
                    'tramo' => $tabla->tramo,
                    'imponibleMensualDesde' => $tabla->imponible_mensual_desde,
                    'imponibleMensualHasta' => $tabla->imponible_mensual_hasta,
                    'factor' => $tabla->factor,
                    'cantidadARebajar' => $tabla->cantidad_a_rebajar
                );
            }
        }
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTablaImpuestoUnico
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
        $errores = TablaImpuestoUnico::errores($datos);      
        
        if(!$errores){
            $tablaImpuestoUnico = new TablaImpuestoUnico();
            $tablaImpuestoUnico->tramo = $datos['tramo'];
            $tablaImpuestoUnico->imponible_mensual_desde = $datos['imponible_mensual_desde'];
            $tablaImpuestoUnico->imponible_mensual_hasta = $datos['imponible_mensual_hasta'];
            $tablaImpuestoUnico->factor = $datos['factor'];
            $tablaImpuestoUnico->cantidad_a_rebajar = $datos['cantidad_a_rebajar'];
            $tablaImpuestoUnico->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $tablaImpuestoUnico->id
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
    
    public function modificar(){
        $datos = Input::all();
        
        if($datos){
            foreach($datos as $dato){
                $id = $dato['id'];
                $tabla = TablaImpuestoUnico::find($id);
                $tabla->imponible_mensual_desde = $dato['imponibleMensualDesde'];
                $tabla->imponible_mensual_hasta = $dato['imponibleMensualHasta'];
                $tabla->factor = $dato['factor'];
                $tabla->cantidad_a_rebajar = $dato['cantidadARebajar'];
                $tabla->save();                     
            }
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue modificada correctamente"
        );
        
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
        //
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
        $tablaImpuestoUnico = TablaImpuestoUnico::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TablaImpuestoUnico::errores($datos);       
        
        if(!$errores and $tablaImpuestoUnico){
            $tablaImpuestoUnico = new TablaImpuestoUnico();
            $tablaImpuestoUnico->tramo = $datos['tramo'];
            $tablaImpuestoUnico->imponible_mensual_desde = $datos['imponible_mensual_desde'];
            $tablaImpuestoUnico->imponible_mensual_hasta = $datos['imponible_mensual_hasta'];
            $tablaImpuestoUnico->factor = $datos['factor'];
            $tablaImpuestoUnico->cantidad_a_rebajar = $datos['cantidad_a_rebajar'];
            $tablaImpuestoUnico->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tablaImpuestoUnico->sid
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
        TablaImpuestoUnico::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'tramo' => Input::get('tramo'),
            'imponible_mensual_desde' => Input::get('imponibleMensualDesde'),
            'imponible_mensual_hasta' => Input::get('imponibleMensualHasta'),
            'factor' => Input::get('factor'),
            'cantidad_a_rebajar' => Input::get('cantidadARebajar')
        );
        return $datos;
    }

}