<?php

class PlantillasFiniquitosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $plantillasFiniquitos = PlantillaFiniquito::all();        
        $listaPlantillas = array();
        if($plantillasFiniquitos->count()){
            foreach($plantillasFiniquitos as $plantillaFiniquito)
            $listaPlantillas[] = array(
                'id' => $plantillaFiniquito->id,
                'sid' => $plantillaFiniquito->sid,
                'nombre' => $plantillaFiniquito->nombre
            );
        }
            
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaPlantillas
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
        $errores = PlantillaFiniquito::errores($datos);      
        
        if(!$errores){
            $plantillaFiniquito = new PlantillaFiniquito();
            $plantillaFiniquito->sid = Funciones::generarSID();
            $plantillaFiniquito->nombre = $datos['nombre'];
            $plantillaFiniquito->cuerpo = $datos['cuerpo'];
            $plantillaFiniquito->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $plantillaFiniquito->sid
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
        $plantilla = PlantillaFiniquito::whereSid($sid)->first();        
        $plantillaFiniquito = new stdClass();

        if($plantilla){
            $plantillaFiniquito->id = $plantilla->id;
            $plantillaFiniquito->sid = $plantilla->sid;
            $plantillaFiniquito->nombre = $plantilla->nombre;
            $plantillaFiniquito->cuerpo = $plantilla->cuerpo;
        }
            
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $plantillaFiniquito
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
        $plantillaFiniquito = PlantillaFiniquito::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = PlantillaFiniquito::errores($datos);       
        
        if(!$errores and $plantillaFiniquito){
            $plantillaFiniquito->nombre = $datos['nombre'];
            $plantillaFiniquito->cuerpo = $datos['cuerpo'];
            $plantillaFiniquito->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $plantillaFiniquito->sid
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
        PlantillaFiniquito::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'cuerpo' => Input::get('cuerpo')
        );
        return $datos;
    }

}