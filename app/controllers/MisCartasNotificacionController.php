<?php

class MisCartasNotificacionController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {        
        $trabajador = Auth::empleado()->user()->trabajador;
        $empleado = $trabajador->ultimaFicha();
        
        $trabajadorCartasNotificacion = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'cartasNotificacion' => Auth::empleado()->user()->misCartasNotificacion()
        );

        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $trabajadorCartasNotificacion
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

    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {
        
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