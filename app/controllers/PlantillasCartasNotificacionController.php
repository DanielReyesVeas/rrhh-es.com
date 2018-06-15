<?php

class PlantillasCartasNotificacionController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $plantillasCartasNotificacion = PlantillaCartaNotificacion::all();
        $listaPlantillasCartasNotificacion=array();
        if( $plantillasCartasNotificacion->count() ){
            foreach( $plantillasCartasNotificacion as $plantillaCartaNotificacion ){
                $listaPlantillasCartasNotificacion[]=array(
                    'id' => $plantillaCartaNotificacion->id,
                    'sid' => $plantillaCartaNotificacion->sid,
                    'nombre' => $plantillaCartaNotificacion->nombre,
                    'cuerpo' => $plantillaCartaNotificacion->cuerpo
                );
            }
        }        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaPlantillasCartasNotificacion
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
        $errores = PlantillaCartaNotificacion::errores($datos);      
        
        if(!$errores){
            $plantillaCartaNotificacion = new PlantillaCartaNotificacion();
            $plantillaCartaNotificacion->sid = Funciones::generarSID();
            $plantillaCartaNotificacion->nombre = $datos['nombre'];
            $plantillaCartaNotificacion->cuerpo = $datos['cuerpo'];
            $plantillaCartaNotificacion->save();
            
            Logs::crearLog('#cartas-de-notificacion', $plantillaCartaNotificacion->id, $plantillaCartaNotificacion->nombre, 'Create', NULL, NULL, 'Plantillas Cartas de Notificación');

            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $plantillaCartaNotificacion->sid
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
        $plantillaCartaNotificacion = PlantillaCartaNotificacion::whereSid($sid)->first();

        $datosCarta=array(
            'id' => $plantillaCartaNotificacion->id,
            'sid' => $plantillaCartaNotificacion->sid,
            'cuerpo' => $plantillaCartaNotificacion->cuerpo,
            'nombre' => $plantillaCartaNotificacion->nombre
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosCarta
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
        $plantillaCartaNotificacion = PlantillaCartaNotificacion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = PlantillaCartaNotificacion::errores($datos);       
        
        if(!$errores and $plantillaCartaNotificacion){
            $plantillaCartaNotificacion->cuerpo = $datos['cuerpo'];
            $plantillaCartaNotificacion->nombre = $datos['nombre'];
            $plantillaCartaNotificacion->save();
            
            Logs::crearLog('#cartas-de-notificacion', $plantillaCartaNotificacion->id, $plantillaCartaNotificacion->nombre, 'Update', NULL, NULL, 'Plantillas Cartas de Notificación');
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $plantillaCartaNotificacion->sid
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
        $plantillaCartaNotificacion = PlantillaCartaNotificacion::whereSid($sid)->first();
        
        Logs::crearLog('#cartas-de-notificacion', $plantillaCartaNotificacion->id, $plantillaCartaNotificacion->nombre, 'Delete', NULL, NULL, 'Plantillas Cartas de Notificación');
        
        $plantillaCartaNotificacion->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'cuerpo' => Input::get('cuerpo'),
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}