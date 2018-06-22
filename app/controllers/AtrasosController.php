<?php

class AtrasosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $atrasos = Atraso::all();
        $listaAtrasos=array();
        if( $atrasos->count() ){
            foreach( $atrasos as $atraso ){
                $listaAtrasos[]=array(
                    'id' => $atraso->id,
                    'sid' => $atraso->sid,
                    'idTrabajador' => $atraso->trabajador_id,
                    'fecha' => $atraso->fecha,
                    'horas' => $atraso->horas,
                    'minutos' => $atraso->minutos,
                    'observacion' => $atraso->observacion,
                    'fechaCreacion' => $atraso->created_at
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaAtrasos
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
        $errores = null;  
        
        if(!$errores){
            $atraso = new Atraso();
            $atraso->sid = Funciones::generarSID();
            $atraso->trabajador_id = $datos['trabajador_id'];
            $atraso->fecha = $datos['fecha'];
            $atraso->horas = $datos['horas'];
            $atraso->minutos = $datos['minutos'];
            $atraso->observacion = $datos['observacion'];
            $atraso->save();
            
            $trabajador = $atraso->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#atrasos', $trabajador->id, $ficha->nombreCompleto(), 'Create', $atraso->id, $atraso->horas . ':' . $atraso->minutos, NULL);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $atraso->id
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#atrasos');
        $datosAtraso = null;
        $trabajadores = array();
        $mesActual = \Session::get('mesActivo');
        
        if($sid){
            $atraso = Atraso::whereSid($sid)->first();
            $datosAtraso=array(
                'id' => $atraso->id,
                'sid' => $atraso->sid,            
                'fecha' => $atraso->fecha,
                'horas' => $atraso->horas,
                'minutos' => $atraso->minutos,
                'total' => date('H:i', mktime($atraso->horas,$atraso->minutos)),
                'observacion' => $atraso->observacion,
                'trabajador' => $atraso->trabajadorAtraso()
            );
        }else{
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosAtraso,
            'mesActual' => $mesActual,
            'trabajadores' => $trabajadores
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
        $atraso = Atraso::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Atraso::errores($datos);       
        
        if(!$errores and $atraso){
            $atraso->trabajador_id = $datos['trabajador_id'];
            $atraso->fecha = $datos['fecha'];
            $atraso->horas = $datos['horas'];
            $atraso->minutos = $datos['minutos'];
            $atraso->observacion = $datos['observacion'];
            $atraso->save();
            
            $trabajador = $atraso->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#atrasos', $trabajador->id, $ficha->nombreCompleto(), 'Update', $atraso->id, $atraso->horas . ':' . $atraso->minutos, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $atraso->sid
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
        $atraso = Atraso::whereSid($sid)->first();
        
        $trabajador = $atraso->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#atrasos', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $atraso['id'], $atraso['dias'], NULL);
        
        $atraso->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'fecha' => Input::get('fecha'),
            'horas' => Input::get('horas'),
            'minutos' => Input::get('minutos'),
            'observacion' => Input::get('observacion')
        );
        return $datos;
    }

}