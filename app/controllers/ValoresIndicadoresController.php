<?php

class ValoresIndicadoresController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $valoresIndicadores = ValorIndicador::all();
        $listaValoresIndicadores=array();
        if( $valoresIndicadores->count() ){
            foreach( $valoresIndicadores as $valorIndicador ){
                $listaValoresIndicadores[]=array(
                    'id' => $valorIndicador->id,
                    'indicador' => $valorIndicador->indicador_id,
                    'valor' => $valorIndicador->valor,
                    'fecha' => $valorIndicador->fecha
                );
            }
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaValoresIndicadores
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
        $errores = ValorIndicador::errores($datos);      
        
        if(!$errores){
            $valorIndicador = new ValorIndicador();
            $valorIndicador->indicador_id = $datos['indicador_id'];
            $valorIndicador->valor = $datos['valor'];
            $valorIndicador->fecha = $datos['fecha'];
            $valorIndicador->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $valorIndicador->id
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
    
    public function indicadores($fecha)
    {
        $lista = ValorIndicador::valorFecha($fecha);
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $lista
        );
        
        return Response::json($datos);
    }
    
    public function show($sid)
    {
        $valorIndicador = ValorIndicador::whereSid($sid)->first();

        $datosValorIndicador=array(
            'id' => $valorIndicador->id,
            'valor' => $valorIndicador->valor,
            'indicador' => $valorIndicador->indicador_id,
            'fecha' => $valorIndicador->fecha
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosValorIndicador
        );
        
        return Response::json($datos);
    }
        
    
    public function ingresoMasivo(){
        $datos = Input::all();
        $datosValoresIndicadores = new stdClass();
        $tablas = array();
        Trabajador::calcularVacaciones();
        Trabajador::crearSemanasCorridas();
        $mes = \Session::get('mesActivo');
        $idMes = $mes->id;
        
        if($datos){
            foreach($datos as $dato){
                $valorIndicador = new ValorIndicador();
                $valorIndicador->indicador_id = $dato['indicador_id'];
                $valorIndicador->valor = $dato['valor'];
                $valorIndicador->mes = $mes->mes;
                $valorIndicador->fecha = $dato['fecha'];
                $valorIndicador->save(); 
                
                $indicador = ValorIndicador::find($valorIndicador->id);
                $nombre = $indicador->indicador->nombre;
                
                $datosValoresIndicadores->$nombre = array(
                    'id' => $indicador->id,
                    'valor' => $indicador->valor,
                    'indicador' => $indicador->indicador->nombre,
                    'fecha' => $indicador->fecha
                );  
            }
            
        }
        
        if($idMes!=1){
            ValorIndicador::crearIndicadores();
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'indicadores' => $datosValoresIndicadores
        );
        
        return Response::json($respuesta);
    }
    
    public function modificar(){
        $datos = Input::all();
        $datosValoresIndicadores = new stdClass();
        
        if($datos){
            foreach($datos as $dato){
                $id = $dato['id'];
                $valorIndicador = ValorIndicador::find($id);
                $valorIndicador->valor = $dato['valor'];
                $valorIndicador->save(); 
                
                $indicador = ValorIndicador::find($valorIndicador->id);
                $nombre = $indicador->indicador->nombre;
                
                $datosValoresIndicadores->$nombre = array(
                    'id' => $indicador->id,
                    'valor' => $indicador->valor,
                    'indicador' => $indicador->indicador->nombre,
                    'fecha' => $indicador->fecha
                );  
            }
        }

        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue modificada correctamente",
            'indicadores' => $datosValoresIndicadores
        );
        
        return Response::json($respuesta);
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
        $valorIndicador = ValorIndicador::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = ValorIndicador::errores($datos);       
        
        if(!$errores and $valorIndicador){
            $valorIndicador->indicador_id = $datos['indicador_id'];
            $valorIndicador->valor = $datos['valor'];
            $valorIndicador->fecha = $datos['fecha'];
            $valorIndicador->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $valorIndicador->sid
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
        ValorIndicador::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'idIndicador' => Input::get('indicador_id'),
            'valor' => Input::get('valor'),
            'fecha' => Input::get('fecha')
        );
        return $datos;
    }

}