<?php

class ClausulasContratoController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#clausulas-contrato');
        $clausulasContrato = ClausulaContrato::orderBy('plantilla_contrato_id')->get();
        $listaClausulasContrato=array();
        if( $clausulasContrato->count() ){
            foreach( $clausulasContrato as $clausulaContrato ){
                $listaClausulasContrato[]=array(
                    'id' => $clausulaContrato->id,
                    'sid' => $clausulaContrato->sid,
                    'plantilla' => array(
                        'id' => $clausulaContrato->plantillaContrato->id,
                        'nombre' => $clausulaContrato->plantillaContrato->nombre
                    ),
                    'nombre' => $clausulaContrato->nombre,
                    'clausula' => $clausulaContrato->clausula,
                    'clausulaNotificacion' => $clausulaContrato->clausula_notificacion
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaClausulasContrato
        );
        
        return Response::json($datos);
    }
    
    public function listaClausulasContrato($sid)
    {
    	$listaClausulasContrato = array();
        $idPlantilla = PlantillaContrato::whereSid($sid)->first()->id;
    	$clausulasContrato = ClausulaContrato::where('plantilla_contrato_id', $idPlantilla)->orderBy('id', 'ASC')->get();
    	if( $clausulasContrato->count() ){
            foreach( $clausulasContrato as $clausulaContrato ){
                $listaClausulasContrato[]=array(
                    'id' => $clausulaContrato->id,
                    'sid' => $clausulaContrato->sid,
                    'nombre' => $clausulaContrato->nombre,
                    'clausula' => $clausulaContrato->clausula,
                    'clausula_notificacion' => $clausulaContrato->clausula_notificacion
                );
            }
    	}
        
        $datos = array(
            'datos' => $listaClausulasContrato
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
        $errores = ClausulaContrato::errores($datos);      
        
        if(!$errores){
            $clausulaContrato = new ClausulaContrato();
            $clausulaContrato->sid = Funciones::generarSID();
            $clausulaContrato->nombre = $datos['nombre'];
            $clausulaContrato->plantilla_contrato_id = $datos['plantilla_contrato_id'];
            $clausulaContrato->clausula = $datos['clausula'];
            $clausulaContrato->clausula_notificacion = $datos['clausula_notificacion'];
            $clausulaContrato->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $clausulaContrato->sid
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
        $clausulaContrato = ClausulaContrato::whereSid($sid)->first();

        $datos=array(
            'id' => $clausulaContrato->id,
            'sid' => $clausulaContrato->sid,            
            'nombre' => $clausulaContrato->nombre,
            'clausula' => $clausulaContrato->clausula,
            'clausulaNotificacion' => $clausulaContrato->clausula_notificacion
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
        $clausulaContrato = ClausulaContrato::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = ClausulaContrato::errores($datos);       
        
        if(!$errores and $clausulaContrato){
            $clausulaContrato->nombre = $datos['nombre'];
            $clausulaContrato->plantilla_contrato_id = $datos['plantilla_contrato_id'];
            $clausulaContrato->clausula = $datos['clausula'];
            $clausulaContrato->clausula_notificacion = $datos['clausula_notificacion'];
            $clausulaContrato->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $clausulaContrato->sid
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
        ClausulaContrato::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'plantilla_contrato_id' => Input::get('plantilla')['id'],
            'clausula' => Input::get('clausula'),
            'clausula_notificacion' => Input::get('clausulaNotificacion')
        );
        return $datos;
    }

}