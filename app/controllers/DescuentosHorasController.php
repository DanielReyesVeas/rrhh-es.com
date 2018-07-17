<?php

class DescuentosHorasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function store()
    {
        $datos = $this->get_datos_formulario();
        $errores = null;  
        
        if(!$errores){
            $descuento = new DescuentoHora();
            $descuento->sid = Funciones::generarSID();
            $descuento->trabajador_id = $datos['trabajador_id'];
            $descuento->fecha = $datos['fecha'];
            $descuento->horas = $datos['horas'];
            $descuento->minutos = $datos['minutos'];
            $descuento->observacion = $datos['observacion'];
            $descuento->save();
            
            $trabajador = $descuento->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#sueldo-hora', $trabajador->id, $ficha->nombreCompleto(), 'Create', $descuento->id, $descuento->horas . ':' . $descuento->minutos, NULL);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $descuento->id
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
        $datosDescuento = null;
        $mesActual = \Session::get('mesActivo');
        
        $descuento = DescuentoHora::whereSid($sid)->first();
        $datosDescuento=array(
            'id' => $descuento->id,
            'sid' => $descuento->sid,            
            'fecha' => $descuento->fecha,
            'horas' => $descuento->horas,
            'minutos' => $descuento->minutos,
            'total' => date('H:i', mktime($descuento->horas,$descuento->minutos)),
            'observacion' => $descuento->observacion,
            'trabajador' => $descuento->trabajadorDescuentoHora()
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosDescuento,
            'mesActual' => $mesActual
        );
        
        return Response::json($datos);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($sid)
    {
        $descuento = DescuentoHora::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = DescuentoHora::errores($datos);       
        
        if(!$errores and $descuento){
            $descuento->trabajador_id = $datos['trabajador_id'];
            $descuento->fecha = $datos['fecha'];
            $descuento->horas = $datos['horas'];
            $descuento->minutos = $datos['minutos'];
            $descuento->observacion = $datos['observacion'];
            $descuento->save();
            
            $trabajador = $descuento->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#sueldo-hora', $trabajador->id, $ficha->nombreCompleto(), 'Update', $descuento->id, $descuento->horas . ':' . $descuento->minutos, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $descuento->sid
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
        $descuento = DescuentoHora::whereSid($sid)->first();
        
        $trabajador = $descuento->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#sueldo-hora', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $descuento['id'], $descuento['dias'], NULL);
        
        $descuento->delete();
        
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