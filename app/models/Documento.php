<?php

class Documento extends Eloquent {
    
    protected $table = 'documentos';

    public function tipoDocumento(){
        return $this->belongsTo('TipoDocumento','tipo_documento_id');
    }
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function extension()
    {
        $extension = $this->nombre;
        $info = new SplFileInfo($extension);
        
        return $info->getExtension();
    }
    
    public function eliminarDocumento()
    {
        $idTipo = $this->tipo_documento_id;
        
        if($idTipo==1){
            $idDoc = $this->id;
            $contrato = Contrato::where('documento_id', $idDoc)->first();
            if($contrato){
                $contrato->delete();
            }
        }else if($idTipo==2){
            $idDoc = $this->id;
            $certificado = Certificado::where('documento_id', $idDoc)->first();
            if($certificado){
                $certificado->delete();
            }
        }else if($idTipo==3){
            $idDoc = $this->id;
            $cartaNotificacion = CartaNotificacion::where('documento_id', $idDoc)->first();
            if($cartaNotificacion){
                $cartaNotificacion->delete();
            }
        }else if($idTipo==4){
            $idDoc = $this->id;
            $liquidacion = Liquidacion::where('documento_id', $idDoc)->first();
            if($liquidacion){
                $detalles = $liquidacion->detalles;
                if($detalles){
                    foreach($detalles as $detalle){
                        $detalle->delete();
                    }
                }
                
                $detalleAfiliadoVoluntario = $liquidacion->detalleAfiliadoVoluntario;
                if($detalleAfiliadoVoluntario){
                    foreach($detalleAfiliadoVoluntario as $detalle){
                        $detalle->delete();
                    }
                }
                
                $detalleAfp = $liquidacion->detalleAfp;
                if($detalleAfp){
                    $detalleAfp->delete();
                }
                
                $detalleApvc = $liquidacion->detalleApvc;
                if($detalleApvc){
                    foreach($detalleApvc as $detalle){
                        $detalle->delete();
                    }
                }
                
                $detalleApvi = $liquidacion->detalleApvi;
                if($detalleApvi){
                    foreach($detalleApvi as $detalle){
                        $detalle->delete();
                    }
                }
                
                $detalleCaja = $liquidacion->detalleCaja;
                if($detalleCaja){
                    $detalleCaja->delete();
                }
                
                $detalleIpsIslFonasa = $liquidacion->detalleIpsIslFonasa;
                if($detalleIpsIslFonasa){
                    $detalleIpsIslFonasa->delete();
                }
                
                $detalleMutual = $liquidacion->detalleMutual;
                if($detalleMutual){
                    $detalleMutual->delete();
                }
                
                $detalleSalud = $liquidacion->detalleSalud;
                if($detalleSalud){
                    $detalleSalud->delete();
                }
                
                $detalleSeguroCesantia = $liquidacion->detalleSeguroCesantia;
                if($detalleSeguroCesantia){
                    $detalleSeguroCesantia->delete();
                }
                
                $detallePagadorSubsidio = $liquidacion->detallePagadorSubsidio;
                if($detallePagadorSubsidio){
                    $detallePagadorSubsidio->delete();
                }
                
                $liquidacion->delete();
                
            }
        }else if($idTipo==5){
            $idDoc = $this->id;
            $finiquito = Finiquito::where('documento_id', $idDoc)->first();
            if($finiquito){
                $finiquito->delete();
            }
        }
        
        
        $nombre = $this->nombre;
        $this->delete();
        
        if(file_exists(public_path() . '/stories/' . $nombre)){
            if(unlink(public_path() . '/stories/' . $nombre)){
                return true;
            }
        }else{
            return false;
        }
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'glosa_id' => 'required',
            'racaudador_id' => 'required'*/
        );

        $message = array(
            'documento.required' => 'Obligatorio!'
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