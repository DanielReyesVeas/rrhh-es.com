<?php

class PlantillasCertificadosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $plantillasCertificados = PlantillaCertificado::all();
        $listaPlantillasCertificados=array();
        if( $plantillasCertificados->count() ){
            foreach( $plantillasCertificados as $plantillaCertificado ){
                $listaPlantillasCertificados[]=array(
                    'id' => $plantillaCertificado->id,
                    'sid' => $plantillaCertificado->sid,
                    'nombre' => $plantillaCertificado->nombre,
                    'cuerpo' => $plantillaCertificado->cuerpo
                );
            }
        }
        
        $trabajadores = Trabajador::activosFiniquitados();
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'trabajadores' => $trabajadores,
            'datos' => $listaPlantillasCertificados
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
        $errores = PlantillaCertificado::errores($datos);      
        
        if(!$errores){
            $plantillaCertificado = new PlantillaCertificado();
            $plantillaCertificado->sid = Funciones::generarSID();
            $plantillaCertificado->nombre = $datos['nombre'];
            $plantillaCertificado->cuerpo = $datos['cuerpo'];
            $plantillaCertificado->save();            
            
            Logs::crearLog('#certificados', $plantillaCertificado->id, $plantillaCertificado->nombre, 'Create', NULL, NULL, 'Plantillas Certificados');
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $plantillaCertificado->sid
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
        $plantillaCertificado = PlantillaCertificado::whereSid($sid)->first();

        $datosCertificado=array(
            'id' => $plantillaCertificado->id,
            'sid' => $plantillaCertificado->sid,
            'cuerpo' => $plantillaCertificado->cuerpo,
            'nombre' => $plantillaCertificado->nombre
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosCertificado
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
        $plantillaCertificado = PlantillaCertificado::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = PlantillaCertificado::errores($datos);       
        
        if(!$errores and $plantillaCertificado){
            $plantillaCertificado->cuerpo = $datos['cuerpo'];
            $plantillaCertificado->nombre = $datos['nombre'];
            $plantillaCertificado->save();
            
            Logs::crearLog('#certificados', $plantillaCertificado->id, $plantillaCertificado->nombre, 'Update', NULL, NULL, 'Plantillas Certificados');
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $plantillaCertificado->sid
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
        $plantillaCertificado = PlantillaCertificado::whereSid($sid)->first();
        
        Logs::crearLog('#certificados', $plantillaCertificado->id, $plantillaCertificado->nombre, 'Delete', NULL, NULL, 'Plantillas Certificados');
        
        $plantillaCertificado->delete();
        
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