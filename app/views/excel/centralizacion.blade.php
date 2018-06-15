  <!DOCTYPE html>
    <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Centralización</title>
      <style type="text/css">  
        .encabezado td 
        {
            text-align:center; 
						font-size: 16px;
        }  
				.subtitulo td 
        {
						font-size: 12px;
        }  
        .resumen td, th {
            border:1px solid #000000; 
        }  
      </style>
    </head>
		<body>
				
				<table class="encabezado">
            <tbody>
                <tr>
                    <td colspan="{{ count($columnasCC) + 5 }}">{{ $comprobante->Comentario }}</td>
                </tr>
								<tr>
                    <td class="subtitulo" colspan="{{ count($columnasCC) + 5 }}">Empresa: {{ $empresa }}</td>
                </tr>
								<tr>
                    <td class="subtitulo" colspan="{{ count($columnasCC) + 5 }}">Fecha: {{ $comprobante->Fecha }}</td>
                </tr>
            </tbody>
        </table>

					<table class="resumen">
						<tbody>
							<tr>
								<th>Cuenta</th>
								<th>Comentario</th>
								<th>Debe</th>
								<th>Haber</th>
								<th>Referencia</th>
								@foreach($columnasCC as $column)
										<th>{{ $column }}</th>
								@endforeach
							</tr>
							@foreach($comprobante->Detalle as $dato)
							<tr>
								<td>{{ $dato->Cuenta }}</td>                
								<td>{{ $dato->Comentario }}</td>                
								<td>{{ Funciones::formatoPesos($dato->Debe) }}</td>                
								<td>{{ Funciones::formatoPesos($dato->Haber) }}</td>                
								<td>{{ $dato->Referencia }}</td>  
								@foreach($columnasCC as $key => $column)
										<?php $var = 'CentroCosto' . ($key + 1); ?>
										<td>{{ ($dato->$var) }}</td>
								@endforeach
							</tr>
							@endforeach
							<tr class="pie">
								<td><b>Totales:</b></td>
								<td></td>
								<td><b>${{ Funciones::formatoPesos($sumaDebe, false) }}</b></td>
								<td><b>${{ Funciones::formatoPesos($sumaHaber, false) }}</b></td>
								@for($i=0 ; $i <= count($columnasCC); $i++)
									<td></td>
								@endfor

							</tr>
						</tbody>
					</table>

		</body>
</html>