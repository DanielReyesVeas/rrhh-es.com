<?php

class TiposHaberController extends \BaseController {
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
        $permisosTabla = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tabla-haberes');
        $permisosIngreso = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        $tiposHaber = TipoHaber::all()->sortBy("codigo");
        $listaImponibles=array();
        $listaNoImponibles=array();
        $cuentas = Cuenta::listaCuentas();
        $horasExtra = TipoHoraExtra::all();
        
        if($tiposHaber->count()){
            foreach($tiposHaber as $tipoHaber){
                if($tipoHaber->id!=7){
                    if($tipoHaber->imponible){
                        $isEditNombre = true;
                        $isEdit = true;
                        if($tipoHaber->id<=15){
                            $isEditNombre = false;
                            if($tipoHaber->nombre=='Colación' || $tipoHaber->nombre=='Movilización' || $tipoHaber->nombre=='Viático'){
                                $isEdit = false;                            
                            }
                        }
                        if($tipoHaber->nombre=='Gratificación'){
                            $gratificacion = 3;
                        }else{
                            $gratificacion = $tipoHaber->gratificacion ? true : false;
                        }
                        $listaImponibles[]=array(
                            'id' => $tipoHaber->id,
                            'sid' => $tipoHaber->sid,
                            'codigo' => $tipoHaber->codigo,
                            'nombre' => $tipoHaber->nombre,
                            'tributable' => $tipoHaber->tributable ? true : false,
                            'calculaHorasExtras' => $tipoHaber->calcula_horas_extras ? true : false,
                            'proporcionalDiasTrabajados' => $tipoHaber->proporcional_dias_trabajados ? true : false,
                            'calculaSemanaCorrida' => $tipoHaber->calcula_semana_corrida ? true : false,
                            'imponible' => $tipoHaber->imponible ? true : false,
                            'gratificacion' => $gratificacion,
                            'cuenta' => $tipoHaber->cuenta($cuentas),
                            'isHoraExtra' => false,
                            'isEdit' => $isEdit,
                            'isEditNombre' => $isEditNombre
                        );
                    }else{
                        $listaNoImponibles[]=array(
                            'id' => $tipoHaber->id,
                            'sid' => $tipoHaber->sid,
                            'codigo' => $tipoHaber->codigo,
                            'nombre' => $tipoHaber->nombre,
                            'tributable' => $tipoHaber->tributable ? true : false,
                            'calculaHorasExtras' => $tipoHaber->calcula_horas_extras ? true : false,
                            'proporcionalDiasTrabajados' => $tipoHaber->proporcional_dias_trabajados ? true : false,
                            'calculaSemanaCorrida' => $tipoHaber->calcula_semana_corrida ? true : false,
                            'imponible' => $tipoHaber->imponible ? true : false,
                            'gratificacion' => $tipoHaber->gratificacion ? true : false,
                            'cuenta' => $tipoHaber->cuenta($cuentas),
                            'isHoraExtra' => false,
                            'isEdit' => $isEdit,
                            'isEditNombre' => $isEditNombre
                        );
                    }
                }
            }
            foreach($horasExtra as $horaExtra){
                if($horaExtra->imponible){
                    $listaImponibles[]=array(
                        'id' => $horaExtra->id,
                        'sid' => $horaExtra->sid,
                        'codigo' => $horaExtra->codigo,
                        'nombre' => 'Hora Extra: ' . $horaExtra->nombre,
                        'tributable' => $horaExtra->tributable ? true : false,
                        'calculaHorasExtras' => 3,
                        'proporcionalDiasTrabajados' => $horaExtra->proporcional_dias_trabajados ? true : false,
                        'calculaSemanaCorrida' => $horaExtra->calcula_semana_corrida ? true : false,
                        'imponible' => $horaExtra->imponible ? true : false,
                        'gratificacion' => $horaExtra->gratificacion ? true : false,
                        'cuenta' => $horaExtra->cuenta($cuentas),
                        'isHoraExtra' => true,
                        'isEdit' => $isEdit,
                        'isEditNombre' => false
                    );
                }else{
                    $listaNoImponibles[]=array(
                        'id' => $horaExtra->id,
                        'sid' => $horaExtra->sid,
                        'codigo' => $horaExtra->codigo,
                        'nombre' => 'Hora Extra: ' . $horaExtra->nombre,
                        'tributable' => $horaExtra->tributable ? true : false,
                        'calculaHorasExtras' => 3,
                        'proporcionalDiasTrabajados' => $horaExtra->proporcional_dias_trabajados ? true : false,
                        'calculaSemanaCorrida' => $horaExtra->calcula_semana_corrida ? true : false,
                        'imponible' => $horaExtra->imponible ? true : false,
                        'gratificacion' => $horaExtra->gratificacion ? true : false,
                        'cuenta' => $horaExtra->cuenta($cuentas),
                        'isHoraExtra' => true,
                        'isEdit' => $isEdit,
                        'isEditNombre' => false
                    );
                }
            }
        }
        
        
        $datos = array(
            'accesosTabla' => $permisosTabla,
            'accesosIngreso' => $permisosIngreso,
            'imponibles' => $listaImponibles,
            'noImponibles' => $listaNoImponibles,
            'isCuentas' => TipoHaber::isCuentas()
        );
        
        return Response::json($datos);
    }    
    
    public function ingresoHaberes()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'accesosTabla' => array(), 'accesosIngreso' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        $tiposHaber = TipoHaber::all()->sortBy("codigo");
        $listaImponibles=array();
        $listaNoImponibles=array();
        
        if($tiposHaber->count()){
            foreach($tiposHaber as $tipoHaber){
                if($tipoHaber->id>15 || $tipoHaber->id==10 || $tipoHaber->id==11 || $tipoHaber->id==4 || $tipoHaber->id==3 || $tipoHaber->id==5){
                    if($tipoHaber->imponible){
                        $listaImponibles[]=array(
                            'id' => $tipoHaber->id,
                            'sid' => $tipoHaber->sid,
                            'codigo' => $tipoHaber->codigo,
                            'nombre' => $tipoHaber->nombre
                        );
                    }else{
                        $listaNoImponibles[]=array(
                            'id' => $tipoHaber->id,
                            'sid' => $tipoHaber->sid,
                            'codigo' => $tipoHaber->codigo,
                            'nombre' => $tipoHaber->nombre
                        );
                    }
                }
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'imponibles' => $listaImponibles,
            'noImponibles' => $listaNoImponibles
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
        $errores = TipoHaber::errores($datos);      
        
        if(!$errores){
            $tipoHaber = new TipoHaber();
            $tipoHaber->sid = Funciones::generarSID();
            $tipoHaber->codigo = $datos['codigo'];
            $tipoHaber->nombre = $datos['nombre'];
            $tipoHaber->tributable = $datos['tributable'];
            $tipoHaber->calcula_horas_extras = $datos['calcula_horas_extras'];
            $tipoHaber->proporcional_dias_trabajados = $datos['proporcional_dias_trabajados'];
            $tipoHaber->calcula_semana_corrida = $datos['calcula_semana_corrida'];
            $tipoHaber->imponible = $datos['imponible'];
            $tipoHaber->gratificacion = $datos['gratificacion'];
            $tipoHaber->cuenta_id = $datos['cuenta_id'];
            $tipoHaber->save();
            
            Logs::crearLog('#tabla-haberes', $tipoHaber->id, $tipoHaber->nombre, 'Create', $tipoHaber->codigo, $tipoHaber->cuenta_id);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoHaber->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        $datosHaber = null;
        $cuentas = Cuenta::listaCuentas();
        
        if($sid){
            $tipoHaber = TipoHaber::whereSid($sid)->first();
            $misHaberes = $tipoHaber->misHaberes();  
            $isEditNombre = true;
            $isEdit = true;
            if($tipoHaber->id<=15){
                $isEdit = false;                            
                $isEditNombre = false;
                if($tipoHaber->nombre=='Colación' || $tipoHaber->nombre=='Movilización' || $tipoHaber->nombre=='Viático'){
                    $isEdit = true;                            
                }
            }
            
            $datosHaber=array(
                'id' => $tipoHaber->id,
                'sid' => $tipoHaber->sid,
                'codigo' => $tipoHaber->codigo,
                'nombre' => $tipoHaber->nombre,
                'tributable' => $tipoHaber->tributable ? true : false,
                'calculaHorasExtras' => $tipoHaber->calcula_horas_extras ? true : false,
                'proporcionalDiasTrabajados' => $tipoHaber->proporcional_dias_trabajados ? true : false,
                'calculaSemanaCorrida' => $tipoHaber->calcula_semana_corrida ? true : false,
                'imponible' => $tipoHaber->imponible ? true : false,
                'gratificacion' => $tipoHaber->gratificacion ? true : false,
                'haberes' => $misHaberes,
                'cuenta' => $tipoHaber->cuenta(),
                'isEdit' => $isEdit,
                'isEditNombre' => $isEditNombre,
                'isHoraExtra' => false
            );
        }        
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHaber,
            'cuentas' => $cuentas
        );
        
        return Response::json($datos);
    }
    
    public function cuentaHaber($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        $datosHaber = null;
        $cuentas = Cuenta::listaCuentas();
        
        if($sid){
            $tipoHaber = TipoHaber::whereSid($sid)->first();
            $datosHaber=array(
                'id' => $tipoHaber->id,
                'sid' => $tipoHaber->sid,
                'codigo' => $tipoHaber->codigo,
                'nombre' => $tipoHaber->nombre,
                'tributable' => $tipoHaber->tributable ? true : false,
                'calculaHorasExtras' => $tipoHaber->calcula_horas_extras ? true : false,
                'proporcionalDiasTrabajados' => $tipoHaber->proporcional_dias_trabajados ? true : false,
                'calculaSemanaCorrida' => $tipoHaber->calcula_semana_corrida ? true : false,
                'imponible' => $tipoHaber->imponible ? true : false,
                'gratificacion' => $tipoHaber->gratificacion ? true : false,
                'haberes' => $tipoHaber->misHaberes(),
                'cuenta' => $tipoHaber->cuenta(),
                'isHoraExtra' => false
            );
        }
        
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHaber,
            'cuentas' => array_values($cuentas)
        );
        
        return Response::json($datos);
    }
    
    public function cuentaHaberCentroCosto($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        $datosHaber = null;
        $cuentas = Cuenta::listaCuentas();        
        
        if($sid){
            $tipoHaber = TipoHaber::whereSid($sid)->first();
            $datosHaber=array(
                'id' => $tipoHaber->id,
                'sid' => $tipoHaber->sid,
                'codigo' => $tipoHaber->codigo,
                'nombre' => $tipoHaber->nombre,
                'tributable' => $tipoHaber->tributable ? true : false,
                'calculaHorasExtras' => $tipoHaber->calcula_horas_extras ? true : false,
                'proporcionalDiasTrabajados' => $tipoHaber->proporcional_dias_trabajados ? true : false,
                'calculaSemanaCorrida' => $tipoHaber->calcula_semana_corrida ? true : false,
                'imponible' => $tipoHaber->imponible ? true : false,
                'gratificacion' => $tipoHaber->gratificacion ? true : false,
                'haberes' => $tipoHaber->misHaberes(),
                'cuenta' => $tipoHaber->cuenta(),
                'isHoraExtra' => false
            );
        }
        
        $centrosCostos = CentroCosto::listaCentrosCostoCuentas($tipoHaber->id, 'haber', true);        
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHaber,
            'cuentas' => array_values($cuentas),
            'centrosCostos' => $centrosCostos
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
        $tipoHaber = TipoHaber::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoHaber::errores($datos);    
        
        if(!$errores and $tipoHaber){
            $tipoHaber->codigo = $datos['codigo'];
            $tipoHaber->nombre = $datos['nombre'];
            $tipoHaber->tributable = $datos['tributable'];
            $tipoHaber->calcula_horas_extras = $datos['calcula_horas_extras'];
            $tipoHaber->proporcional_dias_trabajados = $datos['proporcional_dias_trabajados'];
            $tipoHaber->calcula_semana_corrida = $datos['calcula_semana_corrida'];
            $tipoHaber->imponible = $datos['imponible'];
            $tipoHaber->gratificacion = $datos['gratificacion'];
            $tipoHaber->cuenta_id = $datos['cuenta_id'];
            $tipoHaber->save();

            Logs::crearLog('#tabla-haberes', $tipoHaber->id, $tipoHaber->nombre, 'Update', $tipoHaber->codigo, $tipoHaber->cuenta_id);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoHaber->sid
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
        $haber = TipoHaber::whereSid($datos['sid'])->first();
        $cuenta = NULL;
        if(isset($datos['cuenta'])){
            $cuenta = $datos['cuenta']['id'];
        }
        $haber->cuenta_id = $cuenta;      
        $haber->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $haber->sid
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
        
        $haberes = TipoHaber::whereIn('sid', $sid)->get();
        
        if($haberes->count()){
            foreach($haberes as $haber){
                $haber->cuenta_id = $idCuenta;
                $haber->save();
            }
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
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
        $tipoHaber = TipoHaber::whereSid($sid)->first();
        
        $errores = $tipoHaber->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#tabla-haberes', $tipoHaber->id, $tipoHaber->nombre, 'Delete', $tipoHaber->codigo, $tipoHaber->cuenta_id);       
            $tipoHaber->delete();
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
            'tributable' => Input::get('tributable'),
            'calcula_horas_extras' => Input::get('calculaHorasExtras'),
            'proporcional_dias_trabajados' => Input::get('proporcionalDiasTrabajados'),
            'calcula_semana_corrida' => Input::get('calculaSemanaCorrida'),
            'imponible' => Input::get('imponible'),
            'gratificacion' => Input::get('gratificacion'),
            'cuenta_id' => Input::get('cuenta')['id']
        );
        return $datos;
    }

}