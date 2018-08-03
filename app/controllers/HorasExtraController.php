<?php

class HorasExtraController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $horasExtra = HoraExtra::all();
        $listaHorasExtra=array();
        
        if( $horasExtra->count() ){
            foreach( $horasExtra as $horaExtra ){
                $listaHorasExtra[]=array(
                    'id' => $horaExtra->id,
                    'sid' => $horaExtra->sid,
                    'idTrabajador' => $horaExtra->trabajador_id,
                    'idMes' => $horaExtra->mes_id,
                    'cantidad' => $horaExtra->cantidad,
                    'fecha' => $horaExtra->fecha,
                    'observacion' => $horaExtra->observacion,
                    'fechaCreacion' => $horaExtra->created_at
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaHorasExtra
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
        $errores = HoraExtra::errores($datos);      
        
        if(!$errores){
            $horaExtra = new HoraExtra();
            $horaExtra->sid = Funciones::generarSID();
            $horaExtra->trabajador_id = $datos['trabajador_id'];
            $horaExtra->mes_id = $datos['mes_id'];
            $horaExtra->cantidad = $datos['cantidad'];
            $horaExtra->horas = $datos['horas'];
            $horaExtra->minutos = $datos['minutos'];
            $horaExtra->factor = $datos['factor'];
            $horaExtra->tipo_id = $datos['tipo']['id'];
            $horaExtra->fecha = $datos['fecha'];
            $horaExtra->observacion = $datos['observacion'];
            $horaExtra->save();
            
            $trabajador = $horaExtra->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-horas-extra', $trabajador->id, $ficha->nombreCompleto(), 'Create', $horaExtra->id, $horaExtra->cantidad, NULL);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $horaExtra->sid
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
    
    public function tipos()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $tipos = TipoHoraExtra::all();
        $tiposHoraExtra = array();
        
        if($tipos->count()){
            foreach($tipos as $tipo){
                $tiposHoraExtra[] = array(
                    'id' => $tipo->id,        
                    'sid' => $tipo->sid,        
                    'nombre' => $tipo->nombre        
                );
            }
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $tiposHoraExtra
        );
        
        return Response::json($datos);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $datosHoraExtra = null;
        $trabajadores = array();
        $mesActual = \Session::get('mesActivo');
        $tipos = TipoHoraExtra::listaTiposHoraExtra();
        
        if($sid){
            $horaExtra = HoraExtra::whereSid($sid)->first();
            $datosHoraExtra=array(
                'id' => $horaExtra->id,
                'sid' => $horaExtra->sid,            
                'fecha' => $horaExtra->fecha,
                'tipo' => array(
                    'id' => $horaExtra->tipo->id,
                    'nombre' => $horaExtra->tipo->nombre
                ),
                'factor' => $horaExtra->factor,
                'horas' => $horaExtra->horas,
                'minutos' => $horaExtra->minutos,
                'cantidad' => $horaExtra->cantidad,
                'observacion' => $horaExtra->observacion,
                'trabajador' => $horaExtra->trabajadorHoraExtra()
            );
        }else{            
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHoraExtra,
            'mesActual' => $mesActual,
            'trabajadores' => $trabajadores,
            'tipos' => $tipos
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
        $horaExtra = HoraExtra::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = HoraExtra::errores($datos);       
        
        if(!$errores and $horaExtra){
            $horaExtra->trabajador_id = $datos['trabajador_id'];
            $horaExtra->mes_id = $datos['mes_id'];
            $horaExtra->factor = $datos['factor'];
            $horaExtra->tipo_id = $datos['tipo']['id'];
            $horaExtra->cantidad = $datos['cantidad'];
            $horaExtra->horas = $datos['horas'];
            $horaExtra->minutos = $datos['minutos'];
            $horaExtra->fecha = $datos['fecha'];
            $horaExtra->observacion = $datos['observacion'];
            $horaExtra->save();
            
            $trabajador = $horaExtra->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-horas-extra', $trabajador->id, $ficha->nombreCompleto(), 'Update', $horaExtra->id, $horaExtra->cantidad, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'id' => $horaExtra->id
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
        $horaExtra = HoraExtra::whereSid($sid)->first();
        
        $trabajador = $horaExtra->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#ingreso-horas-extra', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $horaExtra['id'], $horaExtra['cantidad'], NULL);
            
        $horaExtra->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'mes_id' => Input::get('idMes'),
            'fecha' => Input::get('fecha'),
            'factor' => Input::get('factor'),
            'tipo' => Input::get('tipo'),
            'cantidad' => Input::get('cantidad'),
            'horas' => Input::get('horas'),
            'minutos' => Input::get('minutos'),
            'observacion' => Input::get('observacion')
        );
        return $datos;
    }

}