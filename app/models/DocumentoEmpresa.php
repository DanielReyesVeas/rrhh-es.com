<?php

class DocumentoEmpresa extends Eloquent {
    
    protected $table = 'documentos_empresa';

    public function eliminarDocumento()
    {
        $nombre = $this->nombre;
        $this->delete();
        
        if(unlink(public_path() . '/stories/empresa/' . $nombre)){
            return true;
        }else{
            return false;
        }
    }
    
    public function extension()
    {
        $extension = $this->nombre;
        $info = new SplFileInfo($extension);
        
        return $info->getExtension();
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'glosa_id' => 'required',
            'racaudador_id' => 'required'*/
        );

        $message = array(
            'documentoEmpresa.required' => 'Obligatorio!'
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