<?php

class ClausulasFiniquitoController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#clausulas-finiquito');
        $clausulasFiniquito = ClausulaFiniquito::all();
        $listaClausulasFiniquito=array();
        if( $clausulasFiniquito->count() ){
            foreach( $clausulasFiniquito as $clausulaFiniquito ){
                $listaClausulasFiniquito[]=array(
                    'id' => $clausulaFiniquito->id,
                    'sid' => $clausulaFiniquito->sid,
                    'plantilla' => array(
                        'id' => $clausulaFiniquito->plantillaFiniquito->id,
                        'nombre' => $clausulaFiniquito->plantillaFiniquito->nombre
                    ),
                    'nombre' => $clausulaFiniquito->nombre,
                    'clausula' => $clausulaFiniquito->clausula
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaClausulasFiniquito
        );
        
        return Response::json($datos);
    }
    
    public function listaClausulasFiniquito($sid)
    {
    	$listaClausulasFiniquito = array();
        $idPlantilla = PlantillaFiniquito::whereSid($sid)->first()->id;
    	$clausulasFiniquito = ClausulaFiniquito::where('plantilla_finiquito_id', $idPlantilla)->orderBy('id', 'ASC')->get();
    	if( $clausulasFiniquito->count() ){
            foreach( $clausulasFiniquito as $clausulaFiniquito ){
                $listaClausulasFiniquito[]=array(
                    'id' => $clausulaFiniquito->id,
                    'sid' => $clausulaFiniquito->sid,
                    'nombre' => $clausulaFiniquito->nombre,
                    'clausula' => $clausulaFiniquito->clausula
                );
            }
    	}
        
        $datos = array(
            'datos' => $listaClausulasFiniquito
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
        $errores = ClausulaFiniquito::errores($datos);      
        
        if(!$errores){
            $clausulaFiniquito = new ClausulaFiniquito();
            $clausulaFiniquito->sid = Funciones::generarSID();
            $clausulaFiniquito->nombre = $datos['nombre'];
            $clausulaFiniquito->plantilla_finiquito_id = $datos['plantilla_finiquito_id'];
            $clausulaFiniquito->clausula = $datos['clausula'];
            $clausulaFiniquito->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $clausulaFiniquito->sid
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
        $clausulaFiniquito = ClausulaFiniquito::whereSid($sid)->first();

        $datos=array(
            'id' => $clausulaFiniquito->id,
            'sid' => $clausulaFiniquito->sid,            
            'nombre' => $clausulaFiniquito->nombre,
            'clausula' => $clausulaFiniquito->clausula
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
        $clausulaFiniquito = ClausulaFiniquito::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = ClausulaFiniquito::errores($datos);       
        
        if(!$errores and $clausulaFiniquito){
            $clausulaFiniquito->nombre = $datos['nombre'];
            $clausulaFiniquito->plantilla_finiquito_id = $datos['plantilla_finiquito_id'];
            $clausulaFiniquito->clausula = $datos['clausula'];
            $clausulaFiniquito->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $clausulaFiniquito->sid
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
        ClausulaFiniquito::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'plantilla_finiquito_id' => Input::get('plantilla')['id'],
            'clausula' => Input::get('clausula')
        );
        return $datos;
    }

}