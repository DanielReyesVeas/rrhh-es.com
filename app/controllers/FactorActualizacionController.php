<?php

class FactorActualizacionController extends \BaseController {
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
        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#factores-actualizacion');
        $mes = \Session::get('mesActivo');
        $factores = FactorActualizacion::where('anio', $mes->anio)->orderBy('mes')->get();
        
        $listaFactores=array();
        if( $factores->count() ){
            foreach( $factores as $factor ){
                $listaFactores[]=array(
                    'id' => $factor->id,
                    'anio' => $factor->anio,
                    'mes' => $factor->mes,
                    'mesTexto' => Funciones::obtenerMesTexto(date('m', strtotime($factor->mes))),
                    'porcentaje' => $factor->porcentaje,
                    'factor' => $factor->factor
                );
            }
        }
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaFactores
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

    }    
    
    public function modificar()
    {
        $datos = Input::all();
        
        if($datos){
            foreach($datos as $dato){
                $id = $dato['id'];
                $listaFactores = FactorActualizacion::find($id);
                $listaFactores->porcentaje = $dato['porcentaje'];
                $listaFactores->factor = $dato['factor'];
                $listaFactores->save();                     
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {

    }
    
    public function get_datos_formulario(){
        $datos = array(

        );
        return $datos;
    }

}