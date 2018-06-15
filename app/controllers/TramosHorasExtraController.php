<?php

class TramosHorasExtraController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $tramosHorasExtra = TramoHoraExtra::all();
        $listaTramosHorasExtra=array();
        if( $tramosHorasExtra->count() ){
            foreach( $tramosHorasExtra as $tramoHoraExtra ){
                $listaTramosHorasExtra[]=array(
                    'id' => $tramoHoraExtra->id,
                    'sid' => $tramoHoraExtra->sid,
                    'jornada' => $tramoHoraExtra->jornada,
                    'factor' => $tramoHoraExtra->factor
                );
            }
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaTramosHorasExtra
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
        $errores = TramoHoraExtra::errores($datos);      
        
        if(!$errores){
            $tramoHoraExtra = new TramoHoraExtra();
            $tramoHoraExtra->sid = Funciones::generarSID();
            $tramoHoraExtra->jornada = $datos['jornada'];
            $tramoHoraExtra->factor = $datos['factor'];
            $tramoHoraExtra->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tramoHoraExtra->sid
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
        $tramoHoraExtra = TramoHoraExtra::whereSid($sid)->first();

        $datosTramoHoraExtra=array(
            'id' => $tramoHoraExtra->id,
            'sid' => $tramoHoraExtra->sid,
            'jornada' => $tramoHoraExtra->jornada,
            'factor' => $tramoHoraExtra->factor
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosTramoHoraExtra
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
        $tramoHoraExtra = TramoHoraExtra::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TramoHoraExtra::errores($datos);       
        
        if(!$errores and $tramoHoraExtra){
            $tramoHoraExtra->jornada = $datos['jornada'];
            $tramoHoraExtra->factor = $datos['factor'];
            $tramoHoraExtra->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tramoHoraExtra->sid
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
        TramoHoraExtra::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'factor' => Input::get('factor'),
            'jornada' => Input::get('jornada')
        );
        return $datos;
    }

}