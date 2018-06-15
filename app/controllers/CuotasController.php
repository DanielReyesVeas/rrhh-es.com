<?php

class CuotasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $cuotas = Cuota::all();
        $listaCuotas=array();
        if( $cuotas->count() ){
            foreach( $cuotas as $cuota ){
                $listaCuotas[]=array(
                    'id' => $cuota->id,
                    'sid' => $cuota->sid,
                    'mes' => $cuota->mes,
                    'monto' => $cuota->monto,
                    'moneda' => $cuota->moneda,
                    'prestamo' => array(
                        'id' => $cuota->prestamoCuota->id,
                        'sid' => $cuota->prestamoCuota->sid,
                        'id' => $cuota->prestamoCuota->id,
                        'glosa' => $cuota->glosa,
                        'nombreLiquidacion' => $cuota->nombre_liquidacion
                    )
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCuotas
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
        $errores = Cuota::errores($datos);      
        
        if(!$errores){
            $cuota = new Cuota();
            $cuota->sid = Funciones::generarSID();
            $cuota->prestamo_id = $datos['prestamo_id'];
            $cuota->monto = $datos['monto'];
            $cuota->moneda = $datos['moneda'];
            $cuota->mes = $datos['mes'];
            $cuota->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $prestamo->sid
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
        $cuota = Cuota::whereSid($sid)->first();

        $datos=array(
            'id' => $cuota->id,
            'sid' => $cuota->sid,
            'mes' => $cuota->mes,
            'monto' => $cuota->monto,
            'moneda' => $cuota->moneda,
            'prestamo' => array(
                'id' => $cuota->prestamoCuota->id,
                'sid' => $cuota->prestamoCuota->sid,
                'id' => $cuota->prestamoCuota->id,
                'glosa' => $cuota->glosa,
                'nombreLiquidacion' => $cuota->nombre_liquidacion
            )
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
        $cuota = Cuota::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = null;       
        
        if(!$errores and $prestamo){
            $cuota->prestamo_id = $datos['prestamo_id'];
            $cuota->monto = $datos['monto'];
            $cuota->moneda = $datos['moneda'];
            $cuota->mes = $datos['mes'];
            $cuota->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $cuota->sid
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
        Cuota::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'prestamo_id' => Input::get('idPrestamo'),
            'monto' => Input::get('monto'),
            'moneda' => Input::get('moneda'),
            'mes' => Input::get('mes')
        );
        return $datos;
    }

}