<?php

class Glosa extends Eloquent {
    
    protected $table = 'tipos_estructura_glosa';
    protected $connection = "principal";
    
    public function tabla(){
        return $this->belongsTo('Tabla','tipo_estructura_id');
    }
        
    public function recaudadores(){
        return $this->hasManyThrough('Recaudador','Codigo', 'id', 'id');
    }
    
    public function codigos(){
        return $this->hasMany('Codigo','glosa_id');
    }
    
    public function codigo($recaudador=null){
        if(!$recaudador){
            $recaudador = 1;
        }
        $id = $this->id;
        $codigo = Codigo::where('glosa_id', $id)->where('recaudador_id', $recaudador)->first();
        
        return $codigo;
    }
    
    static function codigosNacionalidades(){
    	$codigosNacionalidades = array();
    	$nacionalidades = Glosa::where('tipo_estructura_id', 2)->orderBy('id', 'ASC')->get();
    	if( $nacionalidades->count() ){
            foreach( $nacionalidades as $nacionalidad ){
                $codigosNacionalidades[]=array(
                    'codigo' => $nacionalidad->id,
                    'glosa' => $nacionalidad->glosa
                );
            }
    	}
    	return $codigosNacionalidades;
    }
    
    static function listaNacionalidades(){
    	$listaNacionalidades = array();
    	$nacionalidades = Glosa::where('tipo_estructura_id', 2)->orderBy('id', 'ASC')->get();
    	if( $nacionalidades->count() ){
            foreach( $nacionalidades as $nacionalidad ){
                $listaNacionalidades[]=array(
                    'id' => $nacionalidad->id,
                    'nombre' => $nacionalidad->glosa
                );
            }
    	}
    	return $listaNacionalidades;
    }
    
    static function listaTiposTrabajador(){
    	$listaTiposTrabajador = array();
    	$tiposTrabajador = Glosa::where('tipo_estructura_id', 5)->orderBy('id', 'ASC')->get();
    	if( $tiposTrabajador->count() ){
            foreach( $tiposTrabajador as $tipoTrabajador ){
                $listaTiposTrabajador[]=array(
                    'id' => $tipoTrabajador->id,
                    'nombre' => $tipoTrabajador->glosa
                );
            }
    	}
    	return $listaTiposTrabajador;
    }
    
    static function listaPrevisiones(){
    	$listaPrevisiones = array();
    	$previsiones = Glosa::where('tipo_estructura_id', 4)->orderBy('id', 'ASC')->get();
        $mes = \Session::get('mesActivo')->mes;
    	if( $previsiones->count() ){
            foreach( $previsiones as $prevision ){
                $listaPrevisiones[]=array(
                    'id' => $prevision->id,
                    'nombre' => $prevision->glosa
                );
            }
    	}
    	return $listaPrevisiones;
    }
    
    static function listaExCajas(){
    	$listaExCajas = array();
    	$exCajas = Glosa::where('tipo_estructura_id', 13)->orderBy('id', 'ASC')->get();
    	if( $exCajas->count() ){
            foreach( $exCajas as $exCaja ){
                if($exCaja->id!=105){
                    $listaExCajas[]=array(
                        'id' => $exCaja->id,
                        'nombre' => $exCaja->glosa
                    );
                }
            }
    	}
    	return $listaExCajas;
    }  
    
    static function listaAfps(){
    	$listaAfps = array();
    	$afps = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get();
        $mes = \Session::get('mesActivo')->mes;
    	if( $afps->count() ){
            foreach( $afps as $afp ){
                if($afp->id!=35){
                    $tasa = TasaCotizacionObligatorioAfp::where('afp_id', $afp->id)->where('mes', $mes)->first();
                    $listaAfps[]=array(
                        'id' => $afp->id,
                        'nombre' => $afp->glosa,
                        'tasa' => $tasa['tasa_afp']
                    );
                }
            }
    	}
    	return $listaAfps;
    }
    
    static function codigosAfps(){
    	$codigosAfps = array();
    	$afps = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get();
    	if( $afps->count() ){
            foreach( $afps as $afp ){
                if($afp->id!=35){
                    $codigosAfps[]=array(
                        'codigo' => $afp->id,
                        'glosa' => $afp->glosa
                    );
                }
            }
    	}
    	return $codigosAfps;
    }
    
    static function codigosExCajas(){
    	$codigosExCajas = array();
    	$exCajas = Glosa::where('tipo_estructura_id', 13)->orderBy('id', 'ASC')->get();
    	if( $exCajas->count() ){
            foreach( $exCajas as $exCaja ){
                if($exCaja->id!=105){
                    $codigosExCajas[]=array(
                        'codigo' => $exCaja->id,
                        'glosa' => $exCaja->glosa
                    );
                }
            }
    	}
    	return $codigosExCajas;
    }
    
    static function codigosTiposEmpleado(){
    	$codigosTiposEmpleado = array();
    	$tipos = Glosa::where('tipo_estructura_id', 5)->orderBy('id', 'ASC')->get();
    	if( $tipos->count() ){
            foreach( $tipos as $tipo ){
                $codigosTiposEmpleado[]=array(
                    'codigo' => $tipo->id,
                    'glosa' => $tipo->glosa
                );
            }
    	}
    	return $codigosTiposEmpleado;
    }
    
    static function codigosAfpsSeguro(){
    	$codigosAfps = array();
    	$afps = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get();
    	if( $afps->count() ){
            foreach( $afps as $afp ){
                if($afp->id!=35){
                    $codigosAfps[]=array(
                        'codigo' => $afp->id,
                        'glosa' => $afp->glosa
                    );
                }
            }
    	}
    	return $codigosAfps;
    }
    
    static function listaMutuales(){
    	$listaMutuales = array();
    	$mutuales = Glosa::where('tipo_estructura_id', 18)->orderBy('id', 'ASC')->get();
    	if( $mutuales->count() ){
            foreach( $mutuales as $mutual ){
                $listaMutuales[]=array(
                    'id' => $mutual->id,
                    'nombre' => $mutual->glosa
                );
            }
    	}
    	return $listaMutuales;
    }  
    
    static function listaCajas(){
    	$listaCajas = array();
    	$cajas = Glosa::where('tipo_estructura_id', 17)->orderBy('id', 'ASC')->get();
    	if( $cajas->count() ){
            foreach( $cajas as $caja ){
                $listaCajas[]=array(
                    'id' => $caja->id,
                    'nombre' => $caja->glosa
                );
            }
    	}
    	return $listaCajas;
    }    
    
    static function listaFormasPago(){
    	$listaFormasPago = array();
    	$formasPago = Glosa::where('tipo_estructura_id', 11)->orderBy('id', 'ASC')->get();
        $alias = array( 'Directa', 'Indirecta' );
        $i = 0;
    	if( $formasPago->count() ){
            foreach( $formasPago as $formaPago ){
                $listaFormasPago[]=array(
                    'id' => $formaPago->id,
                    'nombre' => $formaPago->glosa,
                    'alias' => $alias[$i]
                );
                $i++;
            }
    	}
    	return $listaFormasPago;
    }
    
    static function listaAfpsApvs(){
    	$listaAfpsApvs = array();
    	$afpsApvs = Glosa::where('tipo_estructura_id', 10)->orderBy('id', 'ASC')->get();
    	if( $afpsApvs->count() ){
            foreach( $afpsApvs as $afpApv ){
                if($afpApv->id!=42){
                    $listaAfpsApvs[]=array(
                        'id' => $afpApv->id,
                        'nombre' => $afpApv->glosa
                    );
                }
            }
    	}
    	return $listaAfpsApvs;
    }
    
    static function listaAfpsSeguro(){
    	$listaAfpsSeguro = array();
    	$afpsSeguro = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get();
    	if( $afpsSeguro->count() ){
            foreach( $afpsSeguro as $afpSeguro ){
                if($afpSeguro->id!=35){
                    $listaAfpsSeguro[]=array(
                        'id' => $afpSeguro->id,
                        'nombre' => $afpSeguro->glosa
                    );
                }
            }
    	}
    	return $listaAfpsSeguro;
    }
    
    static function listaIsapres(){
    	$listaIsapres = array();
    	$isapres = Glosa::where('tipo_estructura_id', 15)->orderBy('id', 'ASC')->get();
    	if( $isapres->count() ){
            foreach( $isapres as $isapre ){
                $listaIsapres[]=array(
                    'id' => $isapre->id,
                    'nombre' => $isapre->glosa
                );
            }
    	}
    	return $listaIsapres;
    }
    
    static function codigosIsapres(){
    	$codigosIsapres = array();
    	$isapres = Glosa::where('tipo_estructura_id', 15)->orderBy('glosa', 'ASC')->get();
    	if( $isapres->count() ){
            foreach( $isapres as $isapre ){
                $codigosIsapres[]=array(
                    'codigo' => $isapre->id,
                    'glosa' => $isapre->glosa
                );
            }
    	}
    	return $codigosIsapres;
    }
    
    static function codigosPrevision(){
    	$codigosPrevision = array();
    	$previsiones = Glosa::where('tipo_estructura_id', 4)->orderBy('glosa', 'ASC')->get();
    	if( $previsiones->count() ){
            foreach( $previsiones as $prevision ){
                $codigosPrevision[]=array(
                    'codigo' => $prevision->id,
                    'glosa' => $prevision->glosa
                );
            }
    	}
    	return $codigosPrevision;
    }
    
    public function misCodigos(){
        $codigos = $this->recaudadores;
        $listaCodigos=array();
        if( $codigos->count() ){
            foreach( $codigos as $codigo ){
                $listaCodigos[]=array(
                    'id' => $codigo->id,
                    'codigo' => $codigo->codigo
                );
            }
        }
                
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCodigos
        );
        
        return Response::json($codigos);
    }
    /*
    static function tablaRutPagadoresDeSubsidio(){
    	$listaRutPagadoresDeSubsidio = array();
    	$rutPagadoresDeSubsidio = RutPagadorDeSubsidio::orderBy('id', 'ASC')->get();
    	if( $rutPagadoresDeSubsidio->count() ){
            foreach( $rutPagadoresDeSubsidio as $rutPagadorDeSubsidio ){
                $listaRutPagadoresDeSubsidio[]=array(
                    'id' => $rutPagadorDeSubsidio->id,
                    'codigo' => $rutPagadorDeSubsidio->codigo,
                    'nombre' => $rutPagadorDeSubsidio->glosa
                );
            }
    	}
        $datos = array(
            'info' => array(
                'numero' => 20,
                'encabezado' => 'RUT Pagador de Subsidio',
                'tabla' => 'RUT Pagadores de Subsidio'
            ),
            'datos' => $listaRutPagadoresDeSubsidio
        );
    	return $datos;
    }
    */
        
    static function errores($datos){
         
        $rules = array(
            'tipo_estructura_id' => 'required',
            'glosa' => 'required'
        );

        $message = array(
            'glosa.required' => 'Obligatorio!'
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->messages();
        }else{
            return false;
        }
    }
}