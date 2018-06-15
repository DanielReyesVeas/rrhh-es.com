<html>
  <head>
    <title>EasySystems - Contabilidad Multi Empresa</title>
    <style type="text/css">
      *{
          font-family: Arial;
      }
      p{
          color:#ffffff;  
      }
	.titulo{
		color:#ffffff;
		font-size:30px;
		font-weight:300;
		line-height:150%;
		margin:0;
		text-align:left;
	}
	.mensaje{
			margin:30px 0 16px; 
			color:#A9A9A9;
			font-size:14px;
			line-height:150%;
			text-align:left;
	}
	.mensaje1{
			color:#A9A9A9;
			font-size:14px;
			line-height:150%;
			text-align:left;
	}
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    <center>
      <div dir="ltr" style="background-color:#f7f7f7;margin:0;padding:70px 0 70px 0;width:100%">
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
          <tbody>
            <tr>
              <td align="center" valign="top">                
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px!important">
                  <tbody>
                    <tr>
                      <td align="center" valign="top">                
                        <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#f87f34;border-radius:3px 3px 0 0!important;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif">
                          <tbody>
                            <tr>
                              <td style="padding:36px 48px;display:block">
                                <h1 class="titulo">
                                  {{ $titulo }}
                                </h1>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="600">
                          <tbody>
                            <tr>
                              <td valign="top"  style="background-color:#ffffff">                        
                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                  <tbody>
                                    <tr>
                                      <td valign="top" style="padding:48px 48px 0">
                                        <div>  
                                          {{ $mensaje1 }}
                                          @if(count($accesos>0))
                                           {{ $mensaje2 }}
                                          @endif
                                          {{ $mensaje3 }}
                                          {{ $mensaje4 }}
                                        </div>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td align="center" valign="top">
                        <table border="0" cellpadding="10" cellspacing="0" width="600">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding:0">
                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                  <tbody>
                                    <tr>
                                      <td>
                                        <p>
                                            <center>
                                              <a href="{{ $url }}" target="_blank" data-saferedirecturl="{{ $url }}">
                                                <p style="margin-top:30px">
                                                    <center>
                                                    <img align="center" width="30%" heigh="14%" style="margin:0 auto; display: block;" src="<?php echo $message->embed($img2); ?>" tabindex="0">
                                                    </center>
                                                </p>    
                                              </a>
                                            </center>
                                            <center>
                                              <a href="http://www.easysystems.cl" target="_blank" data-saferedirecturl="http://www.easysystems.cl">
                                                <p style="margin-top:100px">
                                                  <img align="center" width="10%" heigh="4%" style="margin:0 auto; display: block;" src="<?php echo $message->embed($img); ?>" alt="Easy Systems Soluciones Fáciles" tabindex="0">
                                                </p>  
                                              </a>
                                            </center>
                                        </p>
                                      </td>
                                    </tr>
										<tr>
											<td style="background-color: #eee; padding: 10px; text-align: justify;" colspan="3">
												<span style="font-size:10px; color: #888; font-weight: bold; font-family: Arial; ">
													Favor no responder este correo, si tiene dudas puede visitar www.easysystems.cl.<br />
													Este email ha sido enviado a {{ $correo }} por Easy Systems SPA.
												</span>
											</td>
										</tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </center>
  </body>
</html>

