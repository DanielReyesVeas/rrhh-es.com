<?php

class DeclaracionesTrabajadoresController extends \BaseController {

    public function eliminarMasivo()
    {
        $sids = (array) Input::get('trabajadores');
   
        foreach($sids as $sid){
            $sids[] = $sid['sid'];
        }
        $declaraciones = DeclaracionTrabajador::whereIn('sid', $sids)->get();
        $mensaje = "La Información fue eliminada correctamente";
        
        foreach($declaraciones as $declaracion){            
            if(file_exists(public_path() . '/stories/' . $declaracion->nombre_archivo)){
                unlink(public_path() . '/stories/' . $declaracion->nombre_archivo);
            }
            $declaracion->delete();  
        }

        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function destroy($sid)
    {
        $mensaje = "La Información fue eliminada correctamente";
        $declaracion = DeclaracionTrabajador::whereSid($sid)->first();
        
        $trabajador = $declaracion->trabajador;
        $ficha = $trabajador->ficha();
        
        if(file_exists(public_path() . '/stories/' . $declaracion->nombre_archivo)){
            unlink(public_path() . '/stories/' . $declaracion->nombre_archivo);
        }
        
        $declaracion->delete();  
        
        Logs::crearLog('#f1887', $declaracion->id, $declaracion->nombre_archivo, 'Delete', $declaracion->trabajador_id, $ficha->nombreCompleto(), 'F1887');
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }

}