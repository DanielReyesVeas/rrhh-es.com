<?php

class DetalleLiquidacionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $detalleLiquidaciones = DetalleLiquidacion::all();
        $listaDetalleLiquidaciones=array();
        if( $detalleLiquidaciones->count() ){
            foreach( $detalleLiquidaciones as $detalleLiquidacion ){
                $listaDetalleLiquidaciones[]=array(
                    'id' => $detalleLiquidacion->id,
                    'sid' => $detalleLiquidacion->sid
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaDetalleLiquidaciones
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
        $datos = Input::all();
     
        
        foreach($datos as $dato){
            $detalleLiquidacion = new DetalleLiquidacion();
            $detalleLiquidacion->sid = Funciones::generarSID();            
            $detalleLiquidacion->liquidacion_id = $dato['idLiquidacion'];            
            $detalleLiquidacion->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $detalleLiquidacion->sid
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
        $detalleLiquidacion = DetalleLiquidacion::whereSid($sid)->first();

        $datos=array(
            'id' => $detalleLiquidacion->id,
            'sid' => $detalleLiquidacion->sid
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
        $detalleLiquidacion = DetalleLiquidacion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = DetalleLiquidacion::errores($datos);       
        
        if(!$errores and $detalleLiquidacion){
            $detalleLiquidacion->trabajador_id = $datos['trabajador_id'];
            $detalleLiquidacion->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $detalleLiquidacion->sid
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
        DetalleLiquidacion::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'encargado_id' => Input::get('idEncargado'),
            'trabajador_rut' => Input::get('rutTrabajador'),
            'trabajador_nombres' => Input::get('nombresTrabajador'),
            'trabajador_apellidos' => Input::get('apellidosTrabajador'),
            'trabajador_cargo' => Input::get('cargoTrabajador'),
            'trabajador_seccion' => Input::get('seccionTrabajador'),
            'uf' => Input::get('uf'),
            'utm' => Input::get('utm')
        );
        return $datos;
    }

}