<?php

class ReportesController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#generar-reportes');
        
        $conceptos = array(
            array( 'id' => 1, 'nombre' => 'Haberes', 'concepto' => 'haberes'),
            array( 'id' => 2, 'nombre' => 'Descuentos', 'concepto' => 'descuentos'),
            array( 'id' => 3, 'nombre' => 'Aportes', 'concepto' => 'aportes')
        );
        
        $haberes = TipoHaber::listaHaberes();
        $descuentos = TipoDescuento::listaTiposDescuento();
        $aportes = Aporte::listaAportes();
        $trabajadores = Trabajador::listaTrabajadores();
        
        $datos = array(
            'conceptos' => $conceptos,
            'haberes' => $haberes,
            'descuentos' => $descuentos,
            'aportes' => $aportes,
            'trabajadores' => $trabajadores,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function generar()
    {
        $datos = Input::all();
        $tipo = $datos['tipo'];
        $destination = public_path('planillas/reporte.xlsx');
        
        switch($tipo){
            case 'haberes':
                $conceptos = TipoHaber::whereIn('sid', $datos['conceptos'])->get();
                break;
            case 'descuentos':
                $conceptos = TipoDescuento::whereIn('sid', $datos['conceptos'])->get();
                break;
            case 'aportes':
                $conceptos = Aporte::whereIn('sid', $datos['conceptos'])->get();
                break;
            default:
                $conceptos = null;
                break;
        }

        $trabajadores = Trabajador::whereIn('sid', $datos['trabajadores'])->get();
        
        $data = array(
            'trabajadores' => $trabajadores,
            'conceptos' => $conceptos,
            'tipo' => $tipo
        );
        
        //return Response::json($data);
        
        
        
        Excel::create('reporte', function($reader) use ($trabajadores, $conceptos) {
            $reader->sheet('Reporte', function($sheet) use ($trabajadores, $conceptos) {
                $i = 3;
                $letter = 'C';
                $lista = array();
                
                if($trabajadores){
                    foreach($trabajadores as $trabajador){
                        $empleado = $trabajador->ficha();
                        if($empleado){
                            $lista[] = array(
                                'rut' => $trabajador->rut_formato(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'nombreCompleto' => $empleado->apellidos . ', ' . $empleado->nombres
                            );
                        }                        
                    }
                    $lista = Funciones::ordenar($lista, 'apellidos');   
                }
                
                if(count($lista)){
                    foreach($lista as $trab){        
                       $sheet->cell('A'.$i, function($cell) use ($i, $trab) {
                            $cell->setValue($trab['rut']);
                        });
                        $sheet->cell('B'.$i, function($cell) use ($i, $trab) {
                            $cell->setValue($trab['nombreCompleto']);
                        });
                        $i++;
                    }
                }
                
                if(true){
                    if($conceptos){
                        foreach($conceptos as $concepto){
                            if($concepto->id>15 || $concepto->id==10 || $concepto->id==11){
                                $sheet->cell($letter.'1', function($cell) use ($letter, $concepto) {
                                    $cell->setValue($concepto->codigo);                       
                                });
                                $sheet->cell($letter.'2', function($cell) use ($letter, $concepto) {
                                    $cell->setValue($concepto->nombre);                       
                                });
                                $letter++;
                            }
                        }
                    }
                }else{
                    $descuentos = TipoDescuento::all();
                    if($descuentos){
                        foreach($descuentos as $tipoDescuento){
                            if($tipoDescuento->id!=3){
                                $nombre = '';
                                if($tipoDescuento->estructura_descuento_id<3){                   
                                    $nombre = $tipoDescuento->nombre;
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }else if($tipoDescuento->estructura_descuento_id==6){
                                    if($tipoDescuento->nombre!='Caja de Compensación'){
                                        $nombre = $tipoDescuento->nombre;
                                        $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                            $cell->setValue($tipoDescuento->codigo);                       
                                        });
                                        $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                            $cell->setValue($nombre);                       
                                        });
                                        $letter++;
                                    }
                                }else if($tipoDescuento->estructura_descuento_id==3){
                                    $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }else if($tipoDescuento->estructura_descuento_id==7){
                                    $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }
                            }                                
                        }
                    }
                }
                
            });

        })->export('xlsx');  
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
    

}