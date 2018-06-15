  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <style type="text/css">       

    html, body {
      font-family: sans-serif;
      font-size: 12px;  
      padding: 0;
      height: 100%;
      margin: 0;
    }

    .page {
      margin: 20px;
    }

    .contenedor{  
      padding: 10px;
    }

    .resumen{
      border: 1px solid black;
      border-collapse: collapse;
      width: 100%;
      margin-top: 20px;
      font-size: 11px;
    }

    table.resumen tr{
      border: 1px solid black;
    }
		
    table{
      border: 1px solid black;
    }

    tr.valores td{
      border: 1px solid black;
      text-align: right;
      padding: 5px;
      font-size: 12px;
    }  
      
    .titulos th{
      border: 1px solid black;
      text-align: right;
      padding: 5px;
      font-size: 14px;
    }
      
    .totales td{
      text-align: right;
      padding: 5px;
      font-size: 14px;
    }
      
    .comentario{
      text-align: center;
      width: 100%;
      font-size: 20px;
    }
      
    .empresa{
      width: 50%;
      font-size: 15px;
      text-align: center;
      float: left;
    }
    
    .encabezado{
      margin-top: 20px;
      margin-bottom: 20px;
    }
		
		.concepto{
      text-align: left;
    }

  </style>
    <div class="page">

      <div class="contenedor">

        <div class="encabezado">
          <div class="comentario">
            <h3>{{ $comprobante->Comentario }}</h3>
          </div>
          <div class="empresa">
            Empresa: {{ $empresa }}
          </div>
          <div class="empresa">
            Fecha: {{ $comprobante->Fecha }}           
          </div>
        </div>

        <div>

          <table class="resumen">
            <tbody>
              <tr class="titulos">
                <th>Cuenta</th>
                <th>Comentario</th>
                <th>Debe</th>
                <th>Haber</th>
                <th>Referencia</th>
                @foreach($columnasCC as $column)
                    <th>{{ $column }}</th>
                @endforeach
              </tr>
              @foreach($comprobante->Detalle as $index => $dato)
								@if(($index + 1)%28!=0)
									<tr class="valores">
										<td>{{ $dato->Cuenta }}</td>                
										<td class="concepto">{{ $dato->Comentario }}</td>                
										<td>{{ Funciones::formatoPesos($dato->Debe) }}</td>                
										<td>{{ Funciones::formatoPesos($dato->Haber) }}</td>                
										<td>{{ $dato->Referencia }}</td>  
										@foreach($columnasCC as $key => $column)
												<?php $var = 'CentroCosto' . ($key + 1); ?>
												<td>{{ ($dato->$var) }}</td>
										@endforeach
									</tr>
								@else
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div style="page-break-after: always;"></div> 
					<div class="page">
						<div class="contenedor">										
							<div>
								<table class="resumen">
									<tbody>
										<tr class="valores">
											<td>{{ $dato->Cuenta }}</td>                
											<td class="concepto">{{ $dato->Comentario }}</td>                
											<td>{{ Funciones::formatoPesos($dato->Debe) }}</td>                
											<td>{{ Funciones::formatoPesos($dato->Haber) }}</td>                
											<td>{{ $dato->Referencia }}</td>  
											@foreach($columnasCC as $key => $column)
													<?php $var = 'CentroCosto' . ($key + 1); ?>
													<td>{{ ($dato->$var) }}</td>
											@endforeach
										</tr>
								@endif
							@endforeach
							<tr class="totales">
								<td colspan="2"><b>Totales:</b></td>
								<td><b>${{ Funciones::formatoPesos($sumaDebe, false) }}</b></td>
								<td><b>${{ Funciones::formatoPesos($sumaHaber, false) }}</b></td>
								<td colspan="{{ count($columnasCC) + 1 }}"></td>
							</tr>								
            </tbody>
          </table>

        </div>


      </div>

    </div>