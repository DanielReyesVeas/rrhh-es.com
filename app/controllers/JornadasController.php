<?php

class JornadasController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#jornadas');
        $jornadas = Jornada::all();
        $listaJornadas=array();
        if( $jornadas->count() ){
            foreach( $jornadas as $jornada ){
                $tramos = $jornada->tramos();
                $listaJornadas[]=array(
                    'id' => $jornada->id,
                    'sid' => $jornada->sid,
                    'nombre' => $jornada->nombre,
                    'tramos' => $tramos['tramos'],
                    'tramosMostrar' => $tramos['mostrar'],
                    'numeroHoras' => $jornada->numero_horas
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaJornadas
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
        $errores = Jornada::errores($datos);      
        
        if(!$errores){
            $jornada = new Jornada();
            $jornada->sid = Funciones::generarSID();
            $jornada->nombre = $datos['nombre'];
            $jornada->numero_horas = $datos['numero_horas'];
            $jornada->save();
            if($datos['tramos']){
                foreach($datos['tramos'] as $tramo){
                    $nuevaJornadaTramo = new JornadaTramo();
                    $nuevaJornadaTramo->jornada_id = $jornada->id;
                    $nuevaJornadaTramo->tramo_id = $tramo['idTramo'];
                    $nuevaJornadaTramo->save();
                }
            }
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $jornada->sid
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
        $jornada = Jornada::whereSid($sid)->first();
        $tramosHorasExtra = TramoHoraExtra::all();
        
        $datosJornada=array(
            'id' => $jornada->id,
            'sid' => $jornada->sid,
            'nombre' => $jornada->nombre,
            'numeroHoras' => $jornada->numero_horas,
            'tramos' => $jornada->tramos()['tramos']
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosJornada,
            'tramosHorasExtra' => $tramosHorasExtra
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
        $jornada = Jornada::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Jornada::errores($datos);       
        
        if(!$errores and $jornada){
            $jornada->nombre = $datos['nombre'];
            $jornada->numero_horas = $datos['numero_horas'];
            $tramos = $jornada->comprobarTramos($datos['tramos']);
            if($tramos['create']){
                foreach($tramos['create'] as $tramo){
                    $nuevaJornadaTramo = new JornadaTramo();
                    $nuevaJornadaTramo->jornada_id = $tramo['jornada_id'];
                    $nuevaJornadaTramo->tramo_id = $tramo['tramo_id'];
                    $nuevaJornadaTramo->save();
                }
            }
            
            if($tramos['update']){
                foreach($tramos['update'] as $tramo){
                    $nuevaJornadaTramo = JornadaTramo::find($tramo['id']);
                    $nuevaJornadaTramo->jornada_id = $tramo['jornada_id'];
                    $nuevaJornadaTramo->tramo_id = $tramo['tramo_id'];
                    $nuevaJornadaTramo->save();
                }
            }
            
            if($tramos['destroy']){
                foreach($tramos['destroy'] as $tramo){
                    $nuevaJornadaTramo = JornadaTramo::find($tramo['id']);
                    $nuevaJornadaTramo->delete();
                }
            }
            $jornada->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $jornada->sid,
                'tramos' => $tramos
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
        $jornada = Jornada::whereSid($sid)->first();
        
        $errores = $jornada->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#jornadas', $jornada->id, $jornada->nombre, 'Delete');    
            $jornada->eliminarTramos();
            $jornada->delete();
            $datos = array(
                'success' => true,
                'mensaje' => "La Información fue eliminada correctamente"
            );
        }else{
            $datos = array(
                'success' => false,
                'errores' => $errores,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada"
            );
        }
        return Response::json($datos);
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'tramos' => Input::get('tramos'),
            'tramo_hora_extra_id' => Input::get('idTramoHoraExtra'),
            'numero_horas' => Input::get('numeroHoras')
        );
        return $datos;
    }

}