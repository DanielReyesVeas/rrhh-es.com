<?php

class MisLiquidacionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {        
        
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $trabajador = Auth::empleado()->user()->trabajador;
        $empleado = $trabajador->ultimaFicha();
        $aniosRemuneraciones = AnioRemuneracion::orderBy('anio', 'DESC')->get();
        
        if( $aniosRemuneraciones->count() ){
            foreach( $aniosRemuneraciones as $anioRemuneracion ){
                if($anioRemuneracion->anio!=2017){
                    $listaAniosRemuneraciones[]=array(
                        'id' => $anioRemuneracion->id,
                        'sid' => $anioRemuneracion->sid,
                        'nombre' => $anioRemuneracion->anio                    
                    );
                }
            }
        }
        
        if(!$sid){
            $mes = MesDeTrabajo::orderBy('mes', 'DESC')->first();  
            $id = $mes->anio_id;        
            $anioRemuneracion = AnioRemuneracion::find($id);
        }else{
            $anioRemuneracion = AnioRemuneracion::whereSid($sid)->first();
        }
        
        $trabajadorLiquidaciones = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'liquidaciones' => Auth::empleado()->user()->misLiquidaciones($anioRemuneracion)
        );

        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $trabajadorLiquidaciones,
            'anios' => $listaAniosRemuneraciones,
            'anio' => $anioRemuneracion
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