<?php

class MesDeTrabajo extends Eloquent {
    
    protected $table = 'meses_de_trabajo';
    
    public function anioRemuneracion(){
        return $this->belongsTo('AnioRemuneracion','anio_id');
    }
    
    public function haberes(){
        return $this->hasMany('Haber','mes_id');
    }
    
    public function descuentos(){
        return $this->hasMany('Descuento','mes_id');
    }
    
    public function estado(){
        $bool = false;
        $mes = ValorIndicador::where('fecha', $this->fecha_remuneracion)->get();
        if($mes->count()){
            $bool = true;
        }
        return $bool;
    }
    
    static function isUltimoMes()
    {
        $mes = \Session::get('mesActivo');
        $ultimoMes = MesDeTrabajo::orderBy('mes', 'DESC')->first();
        
        return ($mes->id==$ultimoMes->id);
    }
    
    static function isMesDisponible(&$notificaciones)
    {
        $empresa = \Session::get('empresa');
        $mes = \Session::get('mesActivo');
        $fecha = date('Y-m-d', strtotime('+' . 1 . ' month', strtotime($mes->mes)));
        Config::set('database.default', 'admin' );                
        $isDisponible = DB::table('meses')->where('mes', $fecha)->first() ? true : false;
        Config::set('database.default', $empresa->base_datos );
        if($isDisponible){
            $nombre = Funciones::obtenerMesAnioTexto($fecha);
            $notificaciones[] = array(
                'concepto' => 'MesDeTrabajo',
                'titulo' => "<a href='#cierre-mensual'>Mes de Trabajo</a>",
                'mensaje' => "<a href='#cierre-mensual'>El mes de " . $nombre . " ya se encuentra disponible para su apertura.</a>"
            );             
        }
        
        return;
    }
    
    static function listaMesesDeTrabajo()
    {
    	$listaMesesDeTrabajo = array();
        $idMes = \Session::get('mesActivo')->id;
    	$mesesDeTrabajo = MesDeTrabajo::orderBy('mes', 'DESC')->get();
    	if( $mesesDeTrabajo->count() ){
            foreach( $mesesDeTrabajo as $mesDeTrabajo ){
                if($mesDeTrabajo->id!=$idMes){
                    $listaMesesDeTrabajo[]=array(
                        'id' => $mesDeTrabajo->id,
                        'mes' => $mesDeTrabajo->mes,
                        'nombre' => $mesDeTrabajo->nombre,
                        'mesActivo' => $mesDeTrabajo->nombre . ' ' . $mesDeTrabajo->anioRemuneracion->anio,
                        'anio' => $mesDeTrabajo->anioRemuneracion->anio,
                        'idAnio' => $mesDeTrabajo->anio_id,
                        'fechaRemuneracion' => $mesDeTrabajo->fecha_remuneracion,
                        'isIngresado' => $mesDeTrabajo->estado()
                    );
                }
            }
    	}
    	return $listaMesesDeTrabajo;
    }
    
    static function semanas()
    {
        $mes = \Session::get('mesActivo');
        $fechaInicial = $mes->mes;
        $fechaFinal = $mes->fechaRemuneracion;
        $beg = (int) date('W', strtotime(date($fechaInicial)));
        $end = (int) date('W', strtotime(date($fechaFinal)));
        
        return (($end - $beg) + 1);
    }
    
    static function selectMes($id=null)
    {
        $datosMesDeTrabajo = new stdClass();
        $uf = NULL;
        $utm = NULL;
        $uta = NULL;
        $indicadores = array();
        
        if($id){
            $mesDeTrabajo = MesDeTrabajo::find($id);
        }else{            
            $mesDeTrabajo = MesDeTrabajo::orderBy('mes', 'DESC')->first();
        }
        
        if($mesDeTrabajo){
            $fecha = $mesDeTrabajo->mes;
            if($mesDeTrabajo->indicadores==0){
                $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
            }
            $indicadores = ValorIndicador::where('mes', $fecha)->orderBy('indicador_id', 'ASC')->get();
            
            if(count($indicadores)){
                $uf = $indicadores[0]->valor;
                $utm = $indicadores[1]->valor;
                $uta = $indicadores[2]->valor;
            }
            $datosMesDeTrabajo->id = $mesDeTrabajo->id;
            $datosMesDeTrabajo->mes = $mesDeTrabajo->mes;
            $datosMesDeTrabajo->mesActivo = $mesDeTrabajo->nombre . ' ' . $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->fecha = date('d-m-Y');
            $datosMesDeTrabajo->fechaPalabras = Funciones::obtenerFechaTexto();
            $datosMesDeTrabajo->nombre = $mesDeTrabajo->nombre;
            $datosMesDeTrabajo->idAnio = $mesDeTrabajo->anio_id;
            $datosMesDeTrabajo->indicadores = $mesDeTrabajo->indicadores ? true : false;
            $datosMesDeTrabajo->anio = $mesDeTrabajo->anioRemuneracion->anio;
            $datosMesDeTrabajo->fechaRemuneracion = $mesDeTrabajo->fecha_remuneracion;
            $datosMesDeTrabajo->uf = $uf;
            $datosMesDeTrabajo->utm = $utm;
            $datosMesDeTrabajo->uta = $uta;
        }
        
        return $datosMesDeTrabajo;
    }
    
    static function generarDocumentos($nombreEmpresa, $comprobante, $columnasCC, $sumaDebe, $sumaHaber)
    {
        $empresa = Session::get('empresa');
        $mes = Session::get('mesActivo');
        $periodo = date("m-Y", strtotime($mes->mes));
        $nombrePDF = 'centralizacion-' . $empresa->rut . '-' . $periodo . '.pdf';
        $nombreExcel = 'cent-' . $empresa->rut . '-' . $periodo;
        
        $view = View::make('pdf.centralizacion', [
            'comprobante' => $comprobante,
            'columnasCC' => $columnasCC,
            'sumaDebe' => $sumaDebe,
            'sumaHaber' => $sumaHaber,
            'empresa' => $nombreEmpresa
        ]);
        $html = $view->render();
        
        $destination = public_path() . '/stories/' . $nombrePDF;
        /*$pdf = new \Thujohn\Pdf\Pdf();
        $content = $pdf->load($html, 'A4', 'portrait')->output();
        File::put($destination, $content); */
        
        $datos = array(
            'comprobante' => $comprobante,
            'columnasCC' => $columnasCC,
            'sumaDebe' => $sumaDebe,
            'sumaHaber' => $sumaHaber,
            'empresa' => $nombreEmpresa
        );
                
        Excel::create($nombreExcel, function($excel) use($datos, $nombreExcel) {
            $excel->sheet($nombreExcel, function($sheet) use($datos) {
                $sheet->loadView('excel.centralizacion')->with($datos);
            });
        })->store('xls', public_path('stories'));
        
        $destination = public_path('stories/' . $nombreExcel);
        
        $respuesta = array(
            'pdf' => $nombrePDF,
            'excel' => $nombreExcel.'.xls'
        );
        
        return $respuesta;
    }
    
    static function errores($datos){
         
        $rules = array(
            'mes' => 'required',
            'nombre' => 'required',
            'fecha_remuneracion' => 'required',
            'anio_id' => 'required'
        );

        $message = array(
            'mesDeTrabajo.required' => 'Obligatorio!'
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