<?php

class AportesController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::user(), '#aportes');
        $aportes = Aporte::all();
        $cuentas = Cuenta::listaCuentas();
        $listaAportes=array();
        $listaHaberes=array();
        $listaDescuentos=array();
        $haberes = TipoHaber::all();
        $descuentos = TipoDescuento::all();
        
        if( $haberes->count() ){
            foreach( $haberes as $haber ){
                $listaHaberes[]=array(
                    'id' => $haber->id,
                    'sid' => $haber->sid,
                    'cuenta' => $haber->cuenta($cuentas),
                    'nombre' => $haber->nombre
                );
            }
        }
        
        if( $descuentos->count() ){
            foreach( $descuentos as $descuento ){
                $listaDescuentos[]=array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'cuenta' => $descuento->cuenta($cuentas),
                    'nombre' => $descuento->nombre
                );
            }
        }

        
        if( $aportes->count() ){
            foreach( $aportes as $aporte ){
                if($aporte->id<5){
                    $listaAportes[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => $aporte->nombre
                    );
                }else{
                    $listaAfps[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'AFP ' . $aporte->afp()
                    );
                }
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'aportes' => $listaAportes,
            'afps' => $listaAfps,
            'haberes' => $listaHaberes,
            'descuentos' => $listaDescuentos,
            'isCuentas' => Aporte::isCuentas()
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
        $errores = Aporte::errores($datos);      
        
        if(!$errores){
            $aporte = new Aporte();
            $aporte->sid = Funciones::generarSID();
            $aporte->nombre = $datos['nombre'];
            $aporte->cuenta_id = $datos['cuenta_id'];
            $aporte->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $aporte->sid
            );
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no puedo ser completada debido a errores en la información ingresada",
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
        $aporte = Aporte::whereSid($sid)->first();
        $cuentas = Cuenta::listaCuentas();
        
        $datosAporte=array(
            'id' => $aporte->id,
            'sid' => $aporte->sid,
            'nombre' => ($aporte->id<5) ? $aporte->nombre : 'AFP ' . $aporte->afp(),
            'cuenta' => $aporte->cuenta($cuentas)
        );
        
        $datos = array(
            'datos' => $datosAporte,
            'cuentas' => $cuentas
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
        $aporte = Aporte::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Aporte::errores($datos);       
        
        if(!$errores and $aporte){
            $aporte->cuenta_id = $datos['cuenta_id'];
            $aporte->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $aporte->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no puedo ser completada debido a errores en la información ingresada",
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
        Aporte::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'cuenta_id' => Input::get('cuenta')['id']
        );
        return $datos;
    }

}