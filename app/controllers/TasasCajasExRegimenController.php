<?php

class TasasCajasExRegimenController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tabla-cajas');
        $mes = \Session::get('mesActivo')->mes;
        $tablaCajas = TasaCajasExRegimen::all();
        
        $listaTablaCajas=array();
        if( $tablaCajas->count() ){
            foreach( $tablaCajas as $tabla ){
                $listaTablaCajas[]=array(
                    'id' => $tabla->id,
                    'caja' => array(
                        'id' => $tabla->caja->id,
                        'nombre' => $tabla->caja->nombre
                    ),
                    'mes' => $tabla->mes,
                    'tasa' => $tabla->tasa,
                    'total' => ($tabla->tasa + 7.95)
                );
            }
        }
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTablaCajas
        );
        
        return Response::json($datos);
    }

    
    public function modificar(){
        $datos = Input::all();
        
        if($datos){
            foreach($datos as $dato){
                $id = $dato['id'];
                $tabla = TasaCajasExRegimen::find($id);
                $tabla->tasa = $dato['tasa'];
                $tabla->save();                     
            }
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue modificada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'id' => Input::get('id'),
            'tasa' => Input::get('tasa')
        );
        return $datos;
    }

}