  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">  
    #mes td 
    {
        text-align:center; 
        vertical-align:middle;
    }        
    .encabezado{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
    }
    #mes{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        border: 1px solid black;
        position: static;
    }
    #libro{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;        
        border-collapse: collapse;
    }  
    .head td{
        font-weight: 900;
    }
    #libro td{
        border-bottom: 1px solid black;       
    }
    #libro td{
        border-bottom: 1px solid black; 
        padding-top: 10px;
        padding-bottom: 10px;
    } 
    #totales td{
        border-bottom: 0;  
    } 
    #bottom td{
        border-bottom: 0;
    } 
  </style>
  <div class="page">
      
      <table id="encabezado">
        <tbody>
            <tr>
                <td>Empleador, Habilitado o Pagador</td>
                <td>{{ $datos->empresa['razon_social'] }}</td>
            </tr>
            <tr>
                <td>RUT N°</td>
                <td>{{ $datos->empresa['rutFormato'] }}</td>
            </tr>
            <tr>
                <td>{{ $datos->empresa['actividad_economica'] }}</td>
                <td>{{ $datos->empresa['actividad_economica'] }}</td>
            </tr>
            <tr>
                <td>{{ $datos->empresa['domicilio'] }}</td>
            </tr>
        </tbody>
      </table>
      
    <div id="mes">
      <h4>CERTIFICADO N° 6 SOBRE SUELDOS Y OTRAS RENTAS SIMILARES</h4>
    </div>
      
    <table id="libro">
      <tbody>
          <tr class="head">                    
              <td>RUT</td>   
              <td>NOMBRE</td>                     
          </tr>               
      </tbody> 
    </table>
</div>