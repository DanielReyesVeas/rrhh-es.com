    <!DOCTYPE html>
    <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Libro de Remuneraciones</title>
      <style type="text/css">  
        #mes td 
        {
            text-align:center; 
            vertical-align:middle;
        }    
          #libro td, th {
            border:1px solid #000000; 
        }   

      </style>
    </head>
    <body>    
        <table>
            <tbody>
                <tr>
                    <td colspan="4">{{ $datos->empresa['razon_social'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">RUT: {{ $datos->empresa['rutFormato'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">{{ $datos->empresa['actividad_economica'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">{{ $datos->empresa['domicilio'] }}</td>
                </tr>
            </tbody>
        </table>
        <table id="mes">
            <tbody>
                <tr>
                    <td colspan="22"><b>LIBRO DE REMUNERACIONES</b></td>
                </tr>
                <tr>
                    <td colspan="22"><b>{{ $datos->mes }}</b></td>
                </tr>
            </tbody>
        </table>
        <table id="libro">
            <thead>
                <tr>
                    <th>RUT</th>        
                    <th>Nombre</th>        
                    @if($datos->conceptos['sueldo_base'])<th>S. Base</th>@endif
                    @if($datos->conceptos['dias_trabajados'])<th>DT</th>@endif        
                    @if($datos->conceptos['inasistencias'])<th>Inasist.</th>@endif        
                    @if($datos->conceptos['horas_extra'])<th>H. Extras</th>@endif        
                    @if($datos->conceptos['sueldo'])<th>Sueldo</th>@endif        
                    @if($datos->conceptos['gratificacion'])<th>Grat. Legal</th>@endif        
                    @if($datos->conceptos['total_imponibles'])<th>Total Imp.</th>@endif         
                    @if($datos->conceptos['asignacion_familiar'])<th>Asig. Fam.</th>@endif        
                    @if($datos->conceptos['no_imponibles'])<th>Tot. No Imp.</th>@endif        
                    @if($datos->conceptos['total_haberes'])<th>Tot. Haberes</th>@endif        
                    @if($datos->conceptos['afp'])<th>AFP</th>@endif        
                    @if($datos->conceptos['apv'])<th>APVs</th>@endif        
                    @if($datos->conceptos['salud'])<th>Salud</th>@endif        
                    @if($datos->conceptos['seguro_cesantia'])<th>Seg. Ces.</th>@endif         
                    @if($datos->conceptos['anticipos'])<th>Anticipos</th>@endif        
                    @if($datos->conceptos['impuesto'])<th>Imp. Unico</th>@endif        
                    @if($datos->conceptos['otros_descuentos'])<th>Otros. Desc.</th>@endif         
                    @if($datos->conceptos['total_descuentos'])<th>Tot. Desc.</th>@endif         
                    @if($datos->conceptos['sueldo_liquido'])<th>Líquido</th>@endif         
                </tr>
            </thead>
            <tbody>
                @foreach($datos->liquidaciones as $dato)
                    <tr>
                        <td>{{ Funciones::formatear_rut($dato->trabajador_rut) }}</td>
                        <td>{{ $dato->trabajador_nombres }} {{ $dato->trabajador_apellidos }}</td>
                        @if($datos->conceptos['sueldo_base'])
                            <td>{{ Funciones::formatoPesos($dato->sueldo_base) }}</td>
                        @endif
                        @if($datos->conceptos['dias_trabajados'])
                            <td>{{ $dato->dias_trabajados }}</td>
                        @endif
                        @if($datos->conceptos['inasistencias'])
                            <td>{{ $dato->inasistencias }}</td>
                        @endif
                        @if($datos->conceptos['horas_extra'])
                            <td>{{ $dato->total_horas_extra }}</td>
                        @endif
                        @if($datos->conceptos['sueldo'])
                            <td>{{ Funciones::formatoPesos($dato->sueldo) }}</td>
                        @endif
                        @if($datos->conceptos['gratificacion'])
                            <td>{{ Funciones::formatoPesos($dato->gratificacion) }}</td>
                        @endif
                        @if($datos->conceptos['total_imponibles'])
                            <td>{{ Funciones::formatoPesos($dato->imponibles) }}</td>
                        @endif
                        @if($datos->conceptos['asignacion_familiar'])
                            <td>{{ Funciones::formatoPesos($dato->total_cargas) }}</td>
                        @endif
                        @if($datos->conceptos['no_imponibles'])
                            <td>{{ Funciones::formatoPesos($dato->no_imponibles) }}</td>
                        @endif
                        @if($datos->conceptos['total_haberes'])
                            <td>{{ Funciones::formatoPesos($dato->total_haberes) }}</td>
                        @endif
                        @if($datos->conceptos['afp'])
                            <td>{{ Funciones::formatoPesos($dato->totalAfp) }}</td>
                        @endif
                        @if($datos->conceptos['apv'])
                            <td>{{ Funciones::formatoPesos($dato->totalApvs) }}</td>
                        @endif
                        @if($datos->conceptos['salud'])
                            <td>{{ Funciones::formatoPesos($dato->totalSalud) }}</td>
                        @endif
                        @if($datos->conceptos['seguro_cesantia'])
                            <td>{{ Funciones::formatoPesos($dato->totalSeguroCesantia) }}</td>
                        @endif
                        @if($datos->conceptos['anticipos'])
                            <td>{{ Funciones::formatoPesos($dato->total_anticipos) }}</td>
                        @endif
                        @if($datos->conceptos['impuesto'])
                            <td>{{ Funciones::formatoPesos($dato->impuesto_determinado) }}</td>
                        @endif
                        @if($datos->conceptos['otros_descuentos'])
                            <td>{{ Funciones::formatoPesos($dato->total_otros_descuentos) }}</td>
                        @endif
                        @if($datos->conceptos['total_descuentos'])
                            <td>{{ Funciones::formatoPesos($dato->total_descuentos) }}</td>
                        @endif
                        @if($datos->conceptos['sueldo_liquido'])
                            <td>{{ Funciones::formatoPesos($dato->sueldo_liquido) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>        
            <tfoot>
                <tr>
                    <td></td>
                    <td><b>Totales:</b></td>
                    @if($datos->conceptos['sueldo_base'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSueldoBase']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['dias_trabajados'])
                        <td><b>{{ $datos->totales['totalDiasTrabajados'] }}</b></td>
                    @endif 
                    @if($datos->conceptos['inasistencias'])
                        <td><b>{{ $datos->totales['totalInasistenciasAtrasos'] }}</b></td>
                    @endif 
                    @if($datos->conceptos['horas_extra'])
                        <td><b>{{ $datos->totales['totalHorasExtra'] }}</b></td>
                    @endif 
                    @if($datos->conceptos['sueldo'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSueldo']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['gratificacion'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalGratificacion']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['total_imponibles'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalImponibles']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['asignacion_familiar'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalAsignacionFamiliar']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['no_imponibles'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalNoImponibles']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['total_haberes'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalHaberes']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['afp'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalAfp']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['apv'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalApv']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['salud'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSalud']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['seguro_cesantia'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSeguroCesantia']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['anticipos'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalAnticipos']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['impuesto'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalImpuestoRenta']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['otros_descuentos'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalOtrosDescuentos']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['total_descuentos'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalTotalDescuentos']) }}</b></td>
                    @endif 
                    @if($datos->conceptos['sueldo_liquido'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSueldoLiquido']) }}</b></td>
                    @endif 
                </tr>
            </tfoot>
        </table>

    </body>
    </html>