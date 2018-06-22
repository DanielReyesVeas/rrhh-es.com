<?php

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class Cuenta extends Eloquent {
    
    protected $table = 'cuentas';
    
    function cuentaCentroCosto(){
        return $this->hasOne('CuentaCentroCosto', 'cuenta_id');
    }
    
    public function aportes(){
        return $this->hasMany('Aporte', 'cuenta_id');
    }
    
    public function descuentos(){
        return $this->hasMany('TipoDescuento', 'cuenta_id');
    }
    
    public function haberes(){
        return $this->hasMany('Haber', 'cuenta_id');
    }
    
    public function cuentaCC(){
        return $this->hasMany('cuentaCentroCosto', 'cuenta_id');
    }
    
    static function listaCuentas1(){
        $datosConexionCME = array();
        $empresa = \Session::get('empresa');
        $isCME = $empresa->isCME;
        if($isCME){
            //$rutEmpresa = $empresa->rut;
            $rutEmpresa = 111111111;
            $client = new Client(); //GuzzleHttp\Client
            $result = $client->post('http://demo.cme-es.com/rest/rrhh/plan-de-cuentas', [
            //$result = $client->post('http://demo.cme-es.com/empresas', [
                'auth' => ['restfull', '1234'],
                'json' => [
                    'rutEmpresa' => $rutEmpresa
                ],
                'debug' => false
            ]);
            $datosConexionCME = $result->json(); 
        }
        
        return $datosConexionCME; 
    }


    static function listaCuentas()
    {
    	$listaCuentas = array();        
        $empresa = \Session::get('empresa');
        $isCME = $empresa->cme;            
        $isSuccessCME = Empresa::isSuccessCME();            
    	
        if($isCME && $isSuccessCME){
            $rutEmpresa = $empresa->rut;
            $sub = str_replace('rrhhes_', '', Config::get('cliente.CLIENTE.EMPRESA'));
            $client = new Client(); //GuzzleHttp\Client
            $result = $client->post('http://' . $sub . '.cme-es.com/rest/rrhh/plan-de-cuentas', [
            //$result = $client->post('http://demo.cme-es.com/empresas', [
                'auth' => ['user@interconexion', 'cme@123'],
                'json' => [
                    'rutEmpresa' => $rutEmpresa
                ],
                'debug' => false
            ]);
            $resultado = $result->json(); 
            if($resultado['success']){
                $cuentasOrigen = $resultado['cuentas']; 
                $listaCuentas=array();
                if(count($cuentasOrigen)){
                    foreach($cuentasOrigen as $datoCta){
                        $index = $datoCta['id'];
                        if(!$datoCta['cuenta']){
                            $index += (1000 * $datoCta['nivel']);
                        }
                        $listaCuentas[$index]=$datoCta;
                    }
                }
            }
        }else{
            $cuentas = Cuenta::orderBy('nombre', 'ASC')->get();
            if( $cuentas->count() ){
                foreach( $cuentas as $cuenta ){
                    $listaCuentas[$cuenta->id]=array(
                        'id' => $cuenta->id,
                        'nombre' => $cuenta->nombre,
                        'comportamiento' => $cuenta->comportamiento,
                        'codigo' => $cuenta->codigo,
                        'orden' => 1,
                        'nivel' => 1,
                        'cuenta' => true
                    );
                }
            }
        }
    	return $listaCuentas;
    }
    
    public function comprobarDependencias()
    {
        $empresa = \Session::get('empresa');
        if($empresa->centro_costo){
            $aportes = $this->cuentaCC;    
            $descuentos = array();
            $haberes = array();
        }else{
            $aportes = $this->aportes;
            $descuentos = $this->descuentos;
            $haberes = $this->haberes;
        }
        
        if($aportes->count() || $descuentos->count() || $haberes->count()){
            $errores = new stdClass();
            $errores->error = array("La Cuenta <b>" . $this->nombre . "</b> se encuentra asignada.<br /> Debe <b>reasignar</b> las cuentas primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
        
    static function errores($datos){
         
        if($datos['id']){
            $rules =    array(
                'nombre' => 'required',
                'codigo' => 'required|unique:cuentas,codigo,'.$datos['id']
            );
        }else{
            $rules =    array(
                'nombre' => 'required',
                'codigo' => 'required|unique:cuentas,codigo'
            );
        }

        $message =  array(
            'nombre.required' => 'Obligatorio!',
            'codigo.required' => 'Obligatorio!',
            'codigo.unique' => 'El Código ya se encuentra registrado!'
        );

        $verifier = App::make('validation.presence');

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->messages();
        }else{
            return false;
        }
    }

    
}