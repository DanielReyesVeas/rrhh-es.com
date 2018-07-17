<?php

class TiposDescuentoController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'accesosTabla' => array(), 'accesosIngreso' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tabla-descuentos');
        
        $tiposDescuento = TipoDescuento::all()->sortBy("codigo");
        $estructuras = EstructuraDescuento::estructuras();
        $listaDescuentos = array();
        $listaDescuentosLegales = array();
        $listaDescuentosCaja = array();
        $listaDescuentosAfp = array();
        
        if( $tiposDescuento->count() ){
            foreach( $tiposDescuento as $tipoDescuento ){
                if($tipoDescuento->estructura_descuento_id<3 || $tipoDescuento->estructura_descuento_id==10){
                    $listaDescuentos[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => $tipoDescuento->nombre,
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    );                    
                }else if($tipoDescuento->estructura_descuento_id==3){
                    $listaDescuentosAfp[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => 'APVC AFP ' . $tipoDescuento->nombreAfp(),
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    ); 
                }else if($tipoDescuento->estructura_descuento_id==4){
                    $listaDescuentosAfp[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => 'APV Régimen A AFP ' . $tipoDescuento->nombreAfp(),
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    ); 
                }else if($tipoDescuento->estructura_descuento_id==5){
                    $listaDescuentosAfp[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => 'APV Régimen B AFP ' . $tipoDescuento->nombreAfp(),
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    ); 
                }else if($tipoDescuento->estructura_descuento_id==6){
                    $listaDescuentosCaja[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => $tipoDescuento->nombre,
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    );                    
                }else if($tipoDescuento->estructura_descuento_id==7){
                    $listaDescuentosAfp[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp(),
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    ); 
                }else if($tipoDescuento->estructura_descuento_id==8){
                    $listaDescuentosLegales[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'codigo' => $tipoDescuento->codigo,
                        'nombre' => $tipoDescuento->nombre,
                        'caja' => $tipoDescuento->caja ? true : false,
                        'idEstructura' => $tipoDescuento->estructura_descuento_id,
                        'descripcion' => $tipoDescuento->descripcion
                    );                    
                } 
            }
        }
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaDescuentos,
            'legales' => $listaDescuentosLegales,
            'caja' => $listaDescuentosCaja,
            'afp' => $listaDescuentosAfp,
            'tipos' => $estructuras
        );
        
        return Response::json($datos);
    }
    
    public function ingresoDescuentos()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'accesosTabla' => array(), 'accesosIngreso' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-descuentos');
        
        $tiposDescuento = TipoDescuento::all()->sortBy("codigo");
        $listaTiposDescuento=array();
        $listaTiposDescuentoLegales=array();
        $listaTiposDescuentoCCAF=array();
        
        if( $tiposDescuento->count() ){
            foreach( $tiposDescuento as $tipoDescuento ){
                if($tipoDescuento->id!=3){
                    if($tipoDescuento->estructura_descuento_id<3 || $tipoDescuento->estructura_descuento_id==10){                   
                        $listaTiposDescuento[]=array(
                            'id' => $tipoDescuento->id,
                            'sid' => $tipoDescuento->sid,
                            'codigo' => $tipoDescuento->codigo,
                            'nombre' => $tipoDescuento->nombre
                        );                    
                    }else if($tipoDescuento->estructura_descuento_id==6){
                        if($tipoDescuento->nombre!='Caja de Compensación'){
                            $listaTiposDescuentoCCAF[]=array(
                                'id' => $tipoDescuento->id,
                                'sid' => $tipoDescuento->sid,
                                'codigo' => $tipoDescuento->codigo,
                                'nombre' => $tipoDescuento->nombre
                            );
                        }
                    }else if($tipoDescuento->estructura_descuento_id==3){
                        $listaTiposDescuentoLegales[]=array(
                            'id' => $tipoDescuento->id,
                            'sid' => $tipoDescuento->sid,
                            'codigo' => $tipoDescuento->codigo,
                            'nombre' => 'APVC AFP ' . $tipoDescuento->nombreAfp()
                        );
                    }
                }
            }
        }
        
        $listaTiposDescuentoLegales[]=array(
            'id' => 0,
            'sid' => 'cuentaAhorro',
            'codigo' => '',
            'nombre' => 'Cuenta de Ahorro AFP'
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTiposDescuento,
            'datosAfp' => $listaTiposDescuentoLegales,
            'datosCCAF' => $listaTiposDescuentoCCAF
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
        $errores = TipoDescuento::errores($datos);      
        
        if(!$errores){
            $tipoDescuento = new TipoDescuento();
            $tipoDescuento->sid = Funciones::generarSID();
            $tipoDescuento->estructura_descuento_id = $datos['estructura_descuento_id'];
            $tipoDescuento->codigo = $datos['codigo'];
            $tipoDescuento->nombre = $datos['nombre'];
            $tipoDescuento->caja = false;
            $tipoDescuento->descripcion = $datos['descripcion'];
            $tipoDescuento->save();
            
            Logs::crearLog('#tabla-descuentos', $tipoDescuento->id, $tipoDescuento->nombre, 'Create', $tipoDescuento->codigo, $tipoDescuento->cuenta_id);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoDescuento->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-descuentos');
        $cuentas = Cuenta::listaCuentas();
        $datosDescuento = null;

        if($sid){
            if($sid=='cuentaAhorro'){
                $cuentas = TipoDescuento::where('estructura_descuento_id', 7)->get();
                $datosDescuento=array(
                    'id' => 0,
                    'sid' => 'cuentaAhorro',
                    'codigo' => '',
                    'nombre' => 'Cuenta de Ahorro AFP',
                    'caja' => false,
                    'descripcion' => '',
                    'descuentos' => TipoDescuento::descuentosCuentaAhorro(),
                    'idEstructura' => 7,
                    'cuentas' => $cuentas
                );                
            }else{
                $tipoDescuento = TipoDescuento::whereSid($sid)->first();
                if($tipoDescuento->estructuraDescuento->id==3){
                    $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
                }else if($tipoDescuento->estructuraDescuento->id==4){
                    $nombre = 'APV Régimen A AFP ' . $tipoDescuento->nombreAfp();
                }else if($tipoDescuento->estructuraDescuento->id==5){
                    $nombre = 'APV Régimen B AFP ' . $tipoDescuento->nombreAfp();
                }else if($tipoDescuento->estructuraDescuento->id==7){
                    $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
                }else if($tipoDescuento->estructuraDescuento->id==9){
                    $nombre = $tipoDescuento->nombreIsapre();
                }else{
                    $nombre = $tipoDescuento->nombre;
                }
                $datosDescuento=array(
                    'id' => $tipoDescuento->id,
                    'sid' => $tipoDescuento->sid,
                    'codigo' => $tipoDescuento->codigo,
                    'nombre' => $nombre,
                    'caja' => $tipoDescuento->caja ? true : false,
                    'descripcion' => $tipoDescuento->descripcion,
                    'descuentos' => $tipoDescuento->misDescuentos(),
                    'idEstructura' => $tipoDescuento->estructura_descuento_id,
                    'tipo' => array(
                        'id' => $tipoDescuento->estructuraDescuento->id,
                        'nombre' => $tipoDescuento->estructuraDescuento->nombre
                    )
                );
            }
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosDescuento
        );
        
        return Response::json($datos);
    }
    
    public function cuentaDescuento($sid)
    {
        $tipoDescuento = TipoDescuento::whereSid($sid)->first();
        $cuentas = Cuenta::listaCuentas();
        $cuentasIndexadas = Funciones::array_column($cuentas, 'nombre', 'id');
        $datosDescuento = null;

        if($sid){
            if($tipoDescuento->estructuraDescuento->id==3){
                $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==4){
                $nombre = 'APV Régimen A AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==5){
                $nombre = 'APV Régimen B AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==7){
                $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==9){
                $nombre = $tipoDescuento->nombreIsapre();
            }else{
                $nombre = $tipoDescuento->nombre;
            } 
            $datosDescuento=array(
                'id' => $tipoDescuento->id,
                'sid' => $tipoDescuento->sid,
                'codigo' => $tipoDescuento->codigo,
                'nombre' => $nombre,
                'caja' => $tipoDescuento->caja ? true : false,
                'descripcion' => $tipoDescuento->descripcion,
                'tipo' => array(
                    'id' => $tipoDescuento->estructuraDescuento->id,
                    'nombre' => $tipoDescuento->estructuraDescuento->nombre
                ),
                'cuenta' => array(
                    'id' => $tipoDescuento->cuenta_id,
                    'nombre' => array_key_exists($tipoDescuento->cuenta_id, $cuentasIndexadas)? $cuentasIndexadas[$tipoDescuento->cuenta_id] : ""
                )
            );
        }
        
        $datos = array(
            'cuentas' => array_values($cuentas),
            'datos' => $datosDescuento
        );
        
        return Response::json($datos);
    }
    
    public function cuentaDescuentoCentroCosto($sid)
    {
        $tipoDescuento = TipoDescuento::whereSid($sid)->first();
        $cuentas = Cuenta::listaCuentas();
        $cuentasIndexadas = Funciones::array_column($cuentas, 'nombre', 'id');
        $datosDescuento = null;

        if($sid){
            if($tipoDescuento->estructuraDescuento->id==3){
                $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==4){
                $nombre = 'APV Régimen A AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==5){
                $nombre = 'APV Régimen B AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==7){
                $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
            }else if($tipoDescuento->estructuraDescuento->id==9){
                $nombre = $tipoDescuento->nombreIsapre();
            }else{
                $nombre = $tipoDescuento->nombre;
            } 
            $datosDescuento=array(
                'id' => $tipoDescuento->id,
                'sid' => $tipoDescuento->sid,
                'codigo' => $tipoDescuento->codigo,
                'nombre' => $nombre,
                'caja' => $tipoDescuento->caja ? true : false,
                'descripcion' => $tipoDescuento->descripcion,
                'tipo' => array(
                    'id' => $tipoDescuento->estructuraDescuento->id,
                    'nombre' => $tipoDescuento->estructuraDescuento->nombre
                ),
                'cuenta' => array(
                    'id' => $tipoDescuento->cuenta_id,
                    'nombre' => array_key_exists($tipoDescuento->cuenta_id, $cuentasIndexadas)? $cuentasIndexadas[$tipoDescuento->cuenta_id] : ""
                )
            );
        }
        
        $centrosCostos = CentroCosto::listaCentrosCostoCuentas($tipoDescuento->id, 'descuento', true);        
        
        $datos = array(
            'cuentas' => array_values($cuentas),
            'centrosCostos' => $centrosCostos,
            'datos' => $datosDescuento
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
        $tipoDescuento = TipoDescuento::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoDescuento::errores($datos);  
        
        if(!$errores and $tipoDescuento){
            $tipoDescuento->estructura_descuento_id = $datos['estructura_descuento_id'];
            $tipoDescuento->codigo = $datos['codigo'];
            $tipoDescuento->nombre = $datos['nombre'];
            $tipoDescuento->descripcion = $datos['descripcion'];      
            $tipoDescuento->save();
            
            Logs::crearLog('#tabla-descuentos', $tipoDescuento->id, $tipoDescuento->nombre, 'Update', $tipoDescuento->codigo, $tipoDescuento->cuenta_id);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoDescuento->sid
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
    
    public function updateCuenta()
    {
        $datos = Input::all();
        $tipoDescuento = TipoDescuento::whereSid($datos['sid'])->first();
        $cuenta = NULL;
        if(isset($datos['cuenta'])){
            $cuenta = $datos['cuenta']['id'];
        }
        $tipoDescuento->cuenta_id = $cuenta;      
        $tipoDescuento->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $tipoDescuento->sid
        );
        
        return Response::json($respuesta);
    }
    
    public function updateCuentaCentroCosto()
    {
        $datos = Input::all();
        
        $ccc = CuentaCentroCosto::where('concepto_id', $datos['idConcepto'])->where('concepto', $datos['concepto'])->get();
        
        if($ccc->count()){
            foreach($ccc as $c){
                $c->delete();
            }
        }
        
        foreach($datos['centrosCosto'] as $dato){
            if($dato['cuenta']){
                $cuentaCentroCosto = new CuentaCentroCosto();
                $cuentaCentroCosto->centro_costo_id = $dato['id'];
                $cuentaCentroCosto->cuenta_id = $dato['cuenta']['id'];
                $cuentaCentroCosto->concepto_id = $datos['idConcepto'];
                $cuentaCentroCosto->concepto = $datos['concepto'];
                $cuentaCentroCosto->save();
            }
        }

        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function updateCuentaMasivo()
    {
        $datos = Input::all();
        $sid = $datos['sid'];
        $idCuenta = $datos['idCuenta'];
        
        $descuentos = TipoDescuento::whereIn('sid', $sid)->get();
        
        if($descuentos->count()){
            foreach($descuentos as $descuento){
                $descuento->cuenta_id = $idCuenta;
                $descuento->save();
            }
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'a' => $descuentos
        );
        
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
        $tipoDescuento = TipoDescuento::whereSid($sid)->first();
        
        $errores = $tipoDescuento->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#tabla-descuentos', $tipoDescuento->id, $tipoDescuento->nombre, 'Delete', $tipoDescuento->codigo, $tipoDescuento->cuenta_id);       
            $tipoDescuento->delete();
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
            'id' => Input::get('id'),
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'estructura_descuento_id' => Input::get('tipo')['id'],
            'descripcion' => Input::get('descripcion'),
            'cuenta_id' => Input::get('cuenta')['id'],
            'afp_id' => Input::get('afp')['id'],
            'forma_pago' => Input::get('formaPago')['id']
        );
        return $datos;
    }

}