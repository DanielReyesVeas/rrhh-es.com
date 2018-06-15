<?php

class PlantillasContratosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $plantillasContratos = PlantillaContrato::all();        
        $listaPlantillas = array();
        if($plantillasContratos->count()){
            foreach($plantillasContratos as $plantillaContrato)
            $listaPlantillas[] = array(
                'id' => $plantillaContrato->id,
                'sid' => $plantillaContrato->sid,
                'nombre' => $plantillaContrato->nombre
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
        $errores = PlantillaContrato::errores($datos);      
        
        if(!$errores){
            $plantillaContrato = new PlantillaContrato();
            $plantillaContrato->sid = Funciones::generarSID();
            $plantillaContrato->nombre = $datos['nombre'];
            $plantillaContrato->cuerpo = $datos['cuerpo'];
            $plantillaContrato->save();
            
            Logs::crearLog('#trabajadores', $plantillaContrato->id, $plantillaContrato->nombre, 'Create', NULL, NULL, 'Gestión Planillas Contrato'); 
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $plantillaContrato->sid
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
        $plantillasContratos = PlantillaContrato::whereSid($sid)->first();        
        $plantillaContrato = new stdClass();

        if($plantillasContratos){
            $plantillaContrato->id = $plantillasContratos->id;
            $plantillaContrato->sid = $plantillasContratos->sid;
            $plantillaContrato->nombre = $plantillasContratos->nombre;
            $plantillaContrato->cuerpo = $plantillasContratos->cuerpo;
        }
            
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $plantillaContrato
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
        $plantillaContrato = PlantillaContrato::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = PlantillaContrato::errores($datos);       
        
        if(!$errores and $plantillaContrato){
            $plantillaContrato->nombre = $datos['nombre'];
            $plantillaContrato->cuerpo = $datos['cuerpo'];
            $plantillaContrato->save();
            
            Logs::crearLog('#trabajadores', $plantillaContrato->id, $plantillaContrato->nombre, 'Update', NULL, NULL, 'Gestión Planillas Contrato'); 
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $plantillaContrato->sid
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
        $plantillaContrato = PlantillaContrato::whereSid($sid)->first();
        
        Logs::crearLog('#trabajadores', $plantillaContrato->id, $plantillaContrato->nombre, 'Delete', NULL, NULL, 'Gestión Planillas Contrato'); 
        
        $plantillaContrato->delete();
        
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