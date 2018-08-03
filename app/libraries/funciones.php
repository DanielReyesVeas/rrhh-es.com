<?php

class Funciones{
    
    static $numeros =    array("-", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve");
    static $numerosX =   array("-", "un", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve");
    static $numeros100 = array("-", "ciento", "doscientos", "trecientos", "cuatrocientos", "quinientos", "seicientos", "setecientos", "ochocientos", "novecientos");
    static $numeros11 =  array("-", "once", "doce", "trece", "catorce", "quince", "dieciseis", "diecisiete", "dieciocho", "diecinueve");
    static $numeros10 =  array("-", "-", "-", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa");
    static function datefix_excel($excel) {

        $dif=(41885-$excel)*86400;
        $seconds=1409737670-$dif;
        $seconds = $excel + (60*60*6);
        $date=date("Y-m-d",$seconds);
        return $date;
    }
    static function tresnumeros($n, $last) {
        
        if ($n == 100) return "cien ";
        if ($n == 0) return "cero ";
        $r = "";
        $cen = floor($n / 100);
        $dec = floor(($n % 100) / 10);
        $uni = $n % 10;
        
        if ($cen > 0) $r .= Funciones::$numeros100[$cen] . " ";

        switch ($dec) {
            case 0: $special = 0; break;
            case 1: $special = 10; break;
            case 2: $special = 20; break;
            default: $r .= Funciones::$numeros10[$dec] . " "; $special = 30; break;
        }
        if ($uni == 0) {
            if ($special==30);
            else if ($special==20) $r .= "veinte ";
            else if ($special==10) $r .= "diez ";
            else if ($special==0);
        } else {
            if ($special == 30 && !$last) $r .= "y " . Funciones::$numerosX[$n%10] . " ";
            else if ($special == 30) $r .= "y " . Funciones::$numeros[$n%10] . " ";
            else if ($special == 20) {
                if ($uni == 3) $r .= "veintitrés ";
                else if (!$last) $r .= "veinti" . Funciones::$numerosX[$n%10] . " ";
                else $r .= "veinti" . Funciones::$numeros[$n%10] . " ";
            } else if ($special == 10) $r .= Funciones::$numeros11[$n%10] . " ";
            else if ($special == 0 && !$last) $r .= Funciones::$numerosX[$n%10] . " ";
            else if ($special == 0) $r .= Funciones::$numeros[$n%10] . " ";
        }
        return $r;
    }

    static function seisnumeros($n, $last) {
        if ($n == 0) return "cero ";
        $miles = floor($n / 1000);
        $units = $n % 1000;
        $r = "";
        if ($miles == 1) $r .= "mil ";
        else if ($miles > 1) $r .= Funciones::tresnumeros($miles, false) . "mil ";
        if ($units > 0) $r .= Funciones::tresnumeros($units, $last);
        return $r;
    }

    static function docenumeros($n) {
        if ($n == 0) return "cero ";
        $millo = floor($n / 1000000);
        $units = $n % 1000000;
        $r = "";
        if ($millo == 1) $r .= "un millón ";
        else if ($millo > 1) $r .= Funciones::seisnumeros($millo, false) . "millones ";
        if ($units > 0) $r .= Funciones::seisnumeros($units, true);
        return $r;
    }  
    
    static function Mayus($variable) {
        $variable = strtr(strtoupper($variable),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ");
        return $variable;
    }
    
    static function quitarCeros($n){
        if(strpos($n, ".")){
            $num = rtrim($n, '0');
            if($num[strlen($num)-1]==".") $num = rtrim($num, '.');
        }else{
            $num = $n;
        }
        return $num;
    }
    
    static function redondearMonto($n) {
        $x=5;
        return (ceil($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
    }
    
    static function redondearMontoDecima($n) {
        return ceil($n/10)*10; 
    }
    
    
    
    static function nombreRealArchivo($nombre_actual){
        $nombreReal="";
        $nombres = explode("_", $nombre_actual);
        // el nombre a partir de la posicion 1 en adelante
        for($a=1; $a < count($nombres); $a++)
        {
            if( $a > 1 ) $nombreReal.="_";
            $nombreReal.=$nombres[$a];
        }
        return $nombreReal;
    }
    
    static function extensionArchivo($text, $dot = true)
    {
        $ext = // Extension natural
            substr($text, strrpos($text, '.'));
        if (!$dot)
        { // sin punto...
            return substr($ext, 1);
        }
        return $ext;
    }

    static function subirArchivoArreglo($nombre_archivo, $posicion, $ruta_destino)
    {
        $archivo = $_FILES[$nombre_archivo]['name'][$posicion];
        $newName = date("YmdHis_")."_".$archivo;
        if(move_uploaded_file($_FILES[$nombre_archivo]['tmp_name'][$posicion], utf8_decode($ruta_destino.$newName) ))
        {
            return $newName;
        }else{
            return "";
        }
    }
    
    static function subirArchivo($nombre_archivo, $ruta_destino)
    {
        $archivo = $_FILES[$nombre_archivo]['name'];
        $newName = date("YmdHis")."_".$archivo;
        if(move_uploaded_file($_FILES[$nombre_archivo]['tmp_name'], $ruta_destino.utf8_decode($newName) ))
        {
            return $newName;
        }else{
            return "";
        }
    }
    	
    /**
     * comprueba un rut para determinar si es válido
     */
    static function comprobarRut($rut)
    {
      /*if(!strpos($rut,"-")){
        $rut=substr($rut,0,strlen($rut)-1)."-".$rut[strlen($rut)-1];
      }  
          
      
      $combinacion= array(3,2,7,6,5,4,3,2);
      $valores=explode("-",$rut);
    
      while(strlen($valores[0]) < 8) {
          $valores[0]="0".$valores[0];
      }
    
      $valorrut=$valores[0];
      $sumatoria=0;
      
      for($a=0; $a < 8; $a++ ){
        $sumatoria+=(intval($valorrut[$a]) * $combinacion[$a]);          
      } 
    
      $verificador=11 - ($sumatoria % 11);
        
      if($verificador == 10){
        $verificador="k";          
      }else if($verificador == 11){
          $verificador="0";
      }else if($verificador == strtolower($valores[1])){
          return true; 
      }else{ 
          return false;
      }*/
        
        if($rut){
            $a = '';
            $resto = 0;
            $sumatoria = 0;
            $sumar = array(2,3,4,5,6,7,2,3);
            $dig = substr($rut,- 1);

            for($i=(strlen($rut) - 2), $j=0; $i>=0; $i--, $j++){
                $sumatoria = ($sumatoria + (substr($rut, $i, 1) * $sumar[$j]));
            }

            $resto = ($sumatoria % 11);
            $digito = (11 - $resto);
            
            if($digito==11){
              $digito = 0;
            }else if($digito==10){
              $digito = 'k';
            }

            if(strtolower($dig)=='k'){
              $dig = strtolower($dig);
            }

            if($digito == $dig){
              return true;
            }else{
              return false;
            }
          }else{
            return false;
          }
    }
    


    static function formatear_rut($rut){
        $rut = trim($rut);
        if (strpos($rut,"-"))
        {
            $aux=explode("-",$rut);
            $rut=$aux[0].$aux[1];
        }
        
        $rut=str_split($rut);
        $inverso = array_reverse($rut);
        $final="";
        
        for ($a=0; $a < count($inverso); $a++)
        {
            $final.=$inverso[$a];
            if ($a==0)
            {
                $final.="-";
            }
            elseif($a % 3 == 0)
            {
                $final.=".";
            }
        }
        return implode(array_reverse(str_split($final)));
    }
    
    static function formatear_rut_digito($rut){
        $rut = trim($rut);
        
        return substr($rut, -1);
    }
    
    static function formatear_rut_sin_digito($rut){
        $rut = trim($rut);
        
        return substr($rut, 0, -1);
    }
    
    static function quitar_formato_rut($rut){
        $rut = trim($rut);
        $rut = preg_replace("/\.|\-/", "", $rut);

        $rut1 = intval( substr( $rut,0, strlen($rut)-1));
        $rut2 = substr($rut, strlen($rut)-1, 1);

        $rutFin = $rut1.$rut2;

        return $rutFin;
    }

    static function quitar_formato_numero($numero){
        $numero = preg_replace("/\.|\,/", "", $numero);
        return $numero;
    }
    
    static function formatearNumero($valor)
    {
    	return number_format(floatval($valor), 0, ",", ".");
    }
    
    static public function postUpload(){
 
        $destinationPath = base_path(). '/' . "upload" . '/';

        if(Input::hasFile('myfile')){

            $file = Input::file('myfile'); // your file upload input field in the form should be named 'file'

            // Declare the rules for the form validation.
            $rules = array('myfile'  => 'mimes:jpg,jpeg,bmp,png');
            $data = array('myfile' => Input::file('myfile'));

            // Validate the inputs.
            $validation = Validator::make($data, $rules);

            if ($validation->fails())
            {
                return Response::json('error', 400);
            }

            if(is_array($file))
            {
                foreach($file as $part) {
                    $filename = $part->getClientOriginalName();
                    $part->move($destinationPath, $filename);
                }
            }
            else //single file
            {
                $filename = $file->getClientOriginalName();
                $uploadSuccess = Input::file('myfile')->move($destinationPath, $filename);
            }

            if( $uploadSuccess ) {
                return Response::json('success', 200);
            } else {
                return Response::json('error', 400);
            }

        }
    }
    
    static function datecheck($input, $format="")
    {
        $separator_type= array(
            "/",
            "-",
            "."
        );
        $separator_used="";
        foreach ($separator_type as $separator) {
            $find= stripos($input,$separator);
            if($find<>false){
                $separator_used= $separator;
            }
        }
        if( $separator_used ){
            $input_array= explode($separator_used,$input);
            if ($format=="mdy") {
                return checkdate(intval($input_array[0]),intval($input_array[1]),intval($input_array[2]));
            } elseif ($format=="ymd") {
                return checkdate(intval($input_array[1]),intval($input_array[2]),intval($input_array[0]));
            } else {
                return checkdate(intval($input_array[1]),intval($input_array[0]),intval($input_array[2]));
            }
        }else{
            return false;
        }
    }	
        
        
    static function convertirFecha($input, $format="", $salida="Y-m-d")
    {
        if(Funciones::datecheck($input, $format)){
            $fecha = str_replace("/", "-", $input);
            return date($salida, strtotime($fecha));
        }else{
            return "";
        }
    }
    
    static function formatoFecha($fecha, $formato=null)
    {
        $fecha = date('d-m-Y', strtotime(substr($fecha, 0, 10)));        
        
        /*switch ($formato) {
            case 'd-m-Y': 
                $dia = substr($fecha, 0, 2);
                $mes = substr($fecha, 3, 2);
                $anio = substr($fecha, 6, 4);
                break;
            case 'm-d-Y': 
                $mes = substr($fecha, 0, 2);
                $dia = substr($fecha, 3, 2);
                $anio = substr($fecha, 6, 4);
            case 'Y-m-d': 
                $anio = substr($fecha, 0, 4);
                $mes = substr($fecha, 5, 2);
                $dia = substr($fecha, 8, 2);
            case 'Y-d-m': 
                $anio = substr($fecha, 0, 4);
                $dia = substr($fecha, 5, 2);
                $mes = substr($fecha, 8, 2);
        }*/
        
        return $fecha;
    }
    
    static function generarSID(){
    	return chr(rand(65,90)).date("YmdHis").chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).rand(1000, 9999);
    }
    
    static function array_column($input = null, $columnKey = null, $indexKey = null)
    {
    	// Using func_get_args() in order to check for proper number of
    	// parameters and trigger errors exactly as the built-in array_column()
    	// does in PHP 5.5.
    	$argc = func_num_args();
    	$params = func_get_args();
    	if ($argc < 2) {
    		trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
    		return null;
    	}
    	if (!is_array($params[0])) {
    		trigger_error(
    				'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
    				E_USER_WARNING
    		);
    		return null;
    	}
    	if (!is_int($params[1])
    			&& !is_float($params[1])
    			&& !is_string($params[1])
    			&& $params[1] !== null
    			&& !(is_object($params[1]) && method_exists($params[1], '__toString'))
    	) {
    		trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
    		return false;
    	}
    	if (isset($params[2])
    			&& !is_int($params[2])
    			&& !is_float($params[2])
    			&& !is_string($params[2])
    			&& !(is_object($params[2]) && method_exists($params[2], '__toString'))
    	) {
    		trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
    		return false;
    	}
    	$paramsInput = $params[0];
    	$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
    	$paramsIndexKey = null;
    	if (isset($params[2])) {
    		if (is_float($params[2]) || is_int($params[2])) {
    			$paramsIndexKey = (int) $params[2];
    		} else {
    			$paramsIndexKey = (string) $params[2];
    		}
    	}
    	$resultArray = array();
    	foreach ($paramsInput as $row) {
    		$key = $value = null;
    		$keySet = $valueSet = false;
    		if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
    			$keySet = true;
    			$key = (string) $row[$paramsIndexKey];
    		}
    		if ($paramsColumnKey === null) {
    			$valueSet = true;
    			$value = $row;
    		} elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
    			$valueSet = true;
    			$value = $row[$paramsColumnKey];
    		}
    		if ($valueSet) {
    			if ($keySet) {
    				$resultArray[$key] = $value;
    			} else {
    				$resultArray[] = $value;
    			}
    		}
    	}
    	return $resultArray;
    }

    static function formatearFolio($numero, $largo){
        while( strlen($numero) < $largo) $numero="0".$numero;
        return $numero;
    }
    
    static function elimina_acentos($cadena){
        $tofind = " ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'´`";
        $replac = "_AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn___";
        return(strtr($cadena,$tofind,$replac));
    }

    static function subirImagenPerfil($carpeteDestino, $nombreArchivo, $base64){
        $base64_str = substr($base64, strpos($base64, ",")+1);
        $pos  = strpos($base64, ';');
        $info['mime'] = explode(':', substr($base64, 0, $pos))[1];

        // $info['mime']; contains the mimetype
        switch( $info['mime'] ){
            default:
            case 'image/jpeg' : $tipo = "jpg"; break;
            case 'image/png' : $tipo = "png"; break;
            case 'image/gif' : $tipo = "gif"; break;
        }

        $png_url = $nombreArchivo.".".$tipo;

        //decode base64 string
        $image = base64_decode($base64_str);
        $im = imagecreatefromstring($image);
        $origWidth = imagesx($im);
        $origHeight = imagesy($im);

        // creacion de la miniatura
        if($origWidth > 70 ){
            $destWidth = 70;
            $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
        }elseif( $origHeight > 70 ){
            $destHeight = 70;
            $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
        }
        $imNew = imagecreatetruecolor($destWidth, $destHeight);

        if( $tipo == "png" or $tipo == "gif" ){
            imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
            imagealphablending($imNew, false);
            imagesavealpha($imNew, true);
        }

        imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
        if( $tipo == "png") {
            imagepng($imNew,$carpeteDestino."/min_".$png_url, 0);
        }elseif( $tipo == "jpg" ){
            imagejpeg($imNew, $carpeteDestino."/min_".$png_url, 100);
        }elseif( $tipo == "gif" ){
            imagegif($imNew, $carpeteDestino."/min_".$png_url);
        }

        // creacion de imagen definitiva
        if($origWidth > 150 ){
            $destWidth = 150;
            $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
        }elseif( $origHeight > 150 ){
            $destHeight = 150;
            $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
        }
        $imNew = imagecreatetruecolor($destWidth, $destHeight);

        if( $tipo == "png" or $tipo == "gif" ){
            imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
            imagealphablending($imNew, false);
            imagesavealpha($imNew, true);
        }

        imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
        if( $tipo == "png") {
            imagepng($imNew, $carpeteDestino."/".$png_url, 0);
        }elseif( $tipo == "jpg" ){
            imagejpeg($imNew, $carpeteDestino."/".$png_url, 100);
        }elseif( $tipo == "gif" ){
            imagegif($imNew, $carpeteDestino."/".$png_url);
        }

        return $png_url;
    }

    static function xml2array ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? Funciones::xml2array ( $node ) : $node;

        return $out;
    }

    static function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                Funciones::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    static function array_to_xmlFull(array $arr, SimpleXMLElement $xml) {
        foreach ($arr as $k => $v) {

            $attrArr = array();
            $kArray = explode(' ',$k);
            $tag = array_shift($kArray);

            if (count($kArray) > 0) {
                foreach($kArray as $attrValue) {
                    $attrArr[] = explode('=',$attrValue);
                }
            }

            if (is_array($v)) {
                if (is_numeric($k)) {
                    Funciones::array_to_xmlFull($v, $xml);
                } else {
                    $child = $xml->addChild($tag);
                    if (isset($attrArr)) {
                        foreach($attrArr as $attrArrV) {
                            $child->addAttribute($attrArrV[0],$attrArrV[1]);
                        }
                    }
                    Funciones::array_to_xmlFull($v, $child);
                }
            } else {
                $child = $xml->addChild($tag, $v);
                if (isset($attrArr)) {
                    foreach($attrArr as $attrArrV) {
                        $child->addAttribute($attrArrV[0],$attrArrV[1]);
                    }
                }
            }
        }

        return $xml;
    }


    static function indent($text)
    {
        // Create new lines where necessary
        $find = array('><',  "\n\n");
        $replace = array(">\r\n<","\r\n");
        $text = str_replace($find, $replace, $text);
        $text = trim($text); // for the \n that was added after the final tag
        $tabs='';
        $text_array = explode("\n", $text);
        $open_tags = 0;
        foreach ($text_array AS $key => $line)
        {

            if (($key == 0) || ($key == 1)) // The first line shouldn't affect the indentation
                $tabs = '';
            else
            {
                $lineaSinEspacios = trim($line);
                if( $open_tags > 0 && ( substr($lineaSinEspacios,0,2) == "</" || substr($lineaSinEspacios, strlen($lineaSinEspacios)-2,2) != "/>" ) ){
                    $open_tags--;
                }

                for ($i = 1; $i <= $open_tags; $i++)
                    $tabs .= "\t";
            }

            if ($key != 0)
            {
                $lineaSinEspacios = trim($line);
                if ((strpos($line, '</') === false) && (strpos($line, '>') !== false)  ) {
                    $open_tags++;
                }
            }

            $new_array[] = $tabs . $line;

            $tabs='';
        }
        $indented_text = implode("\n", $new_array);

        return $indented_text;
    }


    static function saltoLineasLargoCaracteres($string, $largo=50){
        $arreglo=array();
        if( strlen($string) > $largo ){
            while( strlen($string) > $largo ){
                $arreglo[]=substr($string,0,$largo);
                $string = substr($string, $largo-1, strlen($string));
            }
        }
        $arreglo[]=$string;

        return implode("\n", $arreglo);
    }

    static function quitarFormatoXML($XML_STRING){
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML( $XML_STRING );
        $XML = $doc->saveXML();

        $newXML = Funciones::quitarHeaderXML($XML);


        $search = array("\n", "\r\n");
        $replace = array(" ", " ");
        return str_replace($search, $replace, $newXML);

    }

    static function quitarHeaderXML($XML_STRING){
        $customXML = new SimpleXMLElement($XML_STRING);
        $dom = dom_import_simplexml($customXML);
        return $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
    }

    static function getUltimoDiaMes($elAnio, $elMes) {
        return date("d",(mktime(0,0,0,$elMes+1,1,$elAnio)-1));
    }

    static function agregar_zip($dir, $zip) {
        //verificamos si $dir es un directorio
        if (is_dir($dir)) {
            //abrimos el directorio y lo asignamos a $da
            if ($da = opendir($dir)) {
                //leemos del directorio hasta que termine
                while (($archivo = readdir($da)) !== false) {
                    /*Si es un directorio imprimimos la ruta
                     * y llamamos recursivamente esta función
                     * para que verifique dentro del nuevo directorio
                     * por mas directorios o archivos
                     */
                    if (is_dir($dir . $archivo) && $archivo != "." && $archivo != "..") {
                        Funciones::agregar_zip($dir . $archivo . "/", $zip);

                        /*si encuentra un archivo imprimimos la ruta donde se encuentra
                         * y agregamos el archivo al zip junto con su ruta
                         */
                    } elseif (is_file($dir . $archivo) && $archivo != "." && $archivo != "..") {
                        $zip->addFile($dir . $archivo, $dir . $archivo);
                    }
                }
                //cerramos el directorio abierto en el momento
                closedir($da);
            }
        }
    }
    
    static function convertirUF($valor, $fecha=null)
    {
        if(!$fecha){
            $fecha = \Session::get('mesActivo')->fechaRemuneracion;
        }
        
        $uf = ValorIndicador::valorFecha($fecha)->uf['valor'];
        
        return round($uf * $valor);
    }
    
    static function convertirUTM($valor, $round, $fecha=null)
    {
        if(!$fecha){
            $fecha = \Session::get('mesActivo')->fechaRemuneracion;
        }
        
        $utm = ValorIndicador::valorFecha($fecha)->utm['valor'];
        $valorPesos = ($utm * $valor);
            
        if($round){
            $valorPesos = round($valorPesos);
        }
        return $valorPesos;
    }
    
    static function convertir($valor, $moneda, $fecha=null)
    {
        $pesos = 0;
        if($moneda==='$'){
            $pesos = $valor;
        }else if($moneda==='UF'){
            
            if(!$fecha){
                $fecha = \Session::get('mesActivo')->fechaRemuneracion;
            }
            
            $uf = ValorIndicador::valorFecha($fecha)->uf['valor'];
            $pesos = round($uf * $valor);
        }else if($moneda==='UTM'){
            
            if(!$fecha){
                $fecha = \Session::get('mesActivo')->fechaRemuneracion;
            }
            
            $utm = ValorIndicador::valorFecha($fecha)->utm['valor'];
            $pesos = round($utm * $valor);
        }
        
        return ($pesos + 0);
    }        
    
    static function formatoMoneda($valor, $moneda){
        if($moneda=='$'){
            $valor = intval($valor);   
        }
        
        return $valor;
    }
    
    static function obtenerMesTextoAbr($mes)
    {
        switch($mes){
            case '01':
              return 'Ene';
              break;
            case '02':
              return 'Feb';
              break;
            case '03':
              return 'Mar';
              break;
            case '04':
              return 'Abr';
              break;
            case '05':
              return 'May';
              break;
            case '06':
              return 'Jun';
              break;
            case '07':
              return 'Jul';
              break;
            case '08':
              return 'Ago';
              break;
            case '09':
              return 'Sept';
              break;
            case '10':
              return 'Oct';
              break;
            case '11':
              return 'Nov';
              break;
            case '12':
              return 'Dic';
              break;
        }
    }
    
    static function obtenerMesTexto($mes)
    {
        switch($mes){
            case '01':
              return 'Enero';
              break;
            case '02':
              return 'Febrero';
              break;
            case '03':
              return 'Marzo';
              break;
            case '04':
              return 'Abril';
              break;
            case '05':
              return 'Mayo';
              break;
            case '06':
              return 'Junio';
              break;
            case '07':
              return 'Julio';
              break;
            case '08':
              return 'Agosto';
              break;
            case '09':
              return 'Septiembre';
              break;
            case '10':
              return 'Octubre';
              break;
            case '11':
              return 'Noviembre';
              break;
            case '12':
              return 'Diciembre';
              break;
        }
    }
    
    static function obtenerDiaSemana($dia)
    {
        switch($dia){
            case 1:
              return 'Lunes';
              break;
            case 2:
              return 'Martes';
              break;
            case 3:
              return 'Miércoles';
              break;
            case 4:
              return 'Jueves';
              break;
            case 5:
              return 'Viernes';
              break;
            case 6:
              return 'Sábado';
              break;
            case 7:
              return 'Domingo';
              break;
        }
    }
    
    static function obtenerFechaTexto($fecha=null)
    {
        if(!$fecha){
            $fecha = date('Y-m-d');
        }         
        
        $mes = Funciones::obtenerMesTexto(date('m', strtotime($fecha)));
        $dia = date('d', strtotime($fecha));
        $diaSemana = Funciones::obtenerDiaSemana(date('w', strtotime($fecha)));
        $anio = date('Y', strtotime($fecha));
        $texto = $diaSemana . ' ' . $dia . ' de ' . $mes . ' de ' . $anio;

        return $texto;
    }
    
    static function obtenerMesAnioTexto($fecha=null)
    {
        if(!$fecha){
            $fecha = date('Y-m-d');
        }         
        
        $mes = Funciones::obtenerMesTexto(date('m', strtotime($fecha)));
        $anio = date('Y', strtotime($fecha));
        $texto = $mes . ' ' . $anio;

        return $texto;
    }
    
    static function obtenerMesAnioTextoAbr($fecha=null)
    {
        if(!$fecha){
            $fecha = date('Y-m-d');
        }         
        
        $mes = Funciones::obtenerMesTextoAbr(date('m', strtotime($fecha)));
        $anio = date('Y', strtotime($fecha));
        $texto = $mes . ' ' . $anio;

        return $texto;
    }
    
    static function primerDia($fecha)
    {
        $mes = date('m', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));
        
        return $anio . '-' . $mes . '-01';
    }
    
    static function obtenerFechaActual($fecha=null)
    {
        if(!$fecha){
            $fecha = date('d/m/Y');
        }         

        return $fecha;
    }
    
    static function centena($valor, $milesima, $millonesima, $mil)
    {
        $dec = substr($valor, 1, 2);
        $cen = substr($valor, 0, 1);
        $centena = '';

        switch($cen){
            case '0':
                $centena = '';
                break;
            case '1':
                if($dec>0){
                    $centena = 'ciento ';
                }else{
                    $centena = 'cien ';
                }
                break;
            case '2':
                $centena = 'doscientos ';
                break;
            case '3':
                $centena = 'trescientos ';
                break;
            case '4':
                $centena = 'cuatrocientos ';
                break;
            case '5':
                $centena = 'quinientos ';
                break;
            case '6':
                $centena = 'seiscientos ';
                break;
            case '7':
                $centena = 'setecientos ';
                break;
            case '8':
                $centena = 'ochocientos ';
                break;
            case '9':
                $centena = 'novecientos ';
                break;
        }

        if($milesima){
            $dec = substr($valor, 1, 5);
            return $centena . Funciones::decena($dec, $milesima, $millonesima, $mil);
        }else if($millonesima){
            $dec = substr($valor, 1, 8);
            return $centena . Funciones::decena($dec, $milesima, $millonesima, $mil);
        }
        
        return $centena . Funciones::decena($dec, $milesima, $millonesima, $mil);    
    }
    
    static function decena($valor, $milesima, $millonesima, $mil)
    {
        $dec = substr($valor, 0, 1);
        $un = substr($valor, 1, 1);
        $decena = "";
        $saltar = false;
        $tilde = false;

        switch($dec){
            case '0':
                $decena = '';
                break;
            case '1':
                if($un>5){
                    $decena = 'dieci';
                    $saltar = false;
                    $tilde = true;
                }else{
                    switch($un){
                        case '0':
                            if($millonesima){
                                $decena = 'diez millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'diez mil ';                                
                                }else{
                                    $decena = 'diez ';
                                }
                            }                    
                            break;
                        case '1':
                            if($millonesima){
                                $decena = 'once millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'once mil ';                                
                                }else{
                                    $decena = 'once ';
                                }
                            }   
                            break;
                        case '2':
                            if($millonesima){
                                $decena = 'doce millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'doce mil ';                                
                                }else{
                                    $decena = 'doce ';
                                }
                            }   
                            break;
                        case '3':
                            if($millonesima){
                                $decena = 'trece millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'trece mil ';                                
                                }else{
                                    $decena = 'trece ';
                                }
                            }   
                            break;
                        case '4':
                            if($millonesima){
                                $decena = 'catorce millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'catorce mil ';                                
                                }else{
                                    $decena = 'catorce ';
                                }
                            }  
                            break;
                        case '5':
                            if($millonesima){
                                $decena = 'quince millones ';
                            }else{
                                if($milesima){        
                                    $decena = 'quince mil ';                                
                                }else{
                                    $decena = 'quince ';
                                }
                            }
                            break;                    
                    }         
                    $saltar = true;                       
                }
                break;
            case '2':
                if($un=='0'){
                    $decena = 'veinte ';
                }else{
                    $decena = 'veinti';
                    $tilde = true;
                }
                break;
            case '3':
                if($un=='0'){
                    $decena = 'treinta ';
                }else{
                    $decena = 'treinta y ';
                }
                break;
            case '4':
                if($un=='0'){
                    $decena = 'cuarenta ';
                }else{
                    $decena = 'cuarenta y ';
                }
                break;
            case '5':
                if($un=='0'){
                    $decena = 'cincuenta ';
                }else{
                    $decena = 'cincuenta y ';
                }
                break;
            case '6':
                if($un=='0'){
                    $decena = 'sesenta ';
              }else{
                    $decena = 'sesenta y ';
                }
                break;
            case '7':
                if($un=='0'){
                    $decena = 'setenta ';
                }else{
                    $decena = 'setenta y ';
                }
                break;
            case '8':
                if($un=='0'){
                    $decena = 'ochenta ';
                }else{
                    $decena = 'ochenta y ';
                }
                break;
            case '9':
                if($un=='0'){
                    $decena = 'noventa ';
                }else{
                    $decena = 'noventa y ';
                }
                break;
        }

        if($milesima){
            $un = substr($valor, 1, 4);
            return $decena . Funciones::unidad($un, false, $saltar, $milesima, $millonesima, $mil, $tilde);
        }else if($millonesima){
            $un = substr($valor, 1, 7);
            return $decena . Funciones::unidad($un, true, $saltar, $milesima, $millonesima, $mil, $tilde);            
        }
        
        return $decena . Funciones::unidad($un, true, $saltar, $milesima, $millonesima, $mil, $tilde);
    }
    
    static function unidad($valor, $plural, $saltar, $milesima, $millonesima, $mil, $tilde)
    {
        $unidad = '';
        if($saltar){
            $unidad = '';
        }else{
            if($milesima || $millonesima){
                $un = substr($valor, 0, 1);
            }else{
                $un = $valor;
            }
            switch($un){
                case '0':
                    if($milesima){ 
                        if($mil){       
                            $unidad = 'mil ';                
                        }else{
                            $unidad = '';
                        }
                    }else{
                        if($millonesima){
                            $unidad = 'millones ';
                        }else{
                            if(!$plural){
                                $unidad = 'cero ';
                            }else{
                                $unidad = '';
                            }
                        }
                    }
                    break;
                case '1':      
                    if($milesima){                          
                        $unidad = 'mil ';  
                        if(!$plural){
                            $unidad = 'un mil '; 
                        }                 
                    }else{
                        if($millonesima){
                            $unidad = 'un millón ';
                        }else{
                            if($tilde){
                                $unidad = 'ún ';
                            }else{
                                $unidad = 'un ';                  
                            }
                        }
                    }
                    break;
                case '2':
                    if($milesima){        
                        $unidad = 'dos mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'dos millones ';
                        }else{
                            $unidad = 'dos ';
                        }
                    }
                    break;
                case '3':
                    if($milesima){        
                        $unidad = 'tres mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'tres millones ';
                        }else{
                            $unidad = 'tres ';
                        }
                    }
                    break;
                case '4':
                    if($milesima){        
                        $unidad = 'cuatro mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'cuatro millones ';
                        }else{
                            $unidad = 'cuatro ';
                        }
                    }
                    break;
                case '5':
                    if($milesima){        
                        $unidad = 'cinco mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'cinco millones ';
                        }else{
                            $unidad = 'cinco ';
                        }
                    }
                    break;
                case '6':
                    if($milesima){        
                        $unidad = 'seis mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'seis millones ';
                        }else{
                            if($tilde){
                                $unidad = 'séis ';
                            }else{
                                $unidad = 'seis ';                      
                            }
                        }
                    }
                    break;
                case '7':
                    if($milesima){        
                        $unidad = 'siete mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'siete millones ';
                        }else{
                            $unidad = 'siete ';
                        }
                    }
                    break;
                case '8':
                    if($milesima){        
                        $unidad = 'ocho mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'ocho millones ';
                        }else{
                            $unidad = 'ocho ';
                        }
                    }
                    break;
                case '9':
                    if($milesima){        
                        $unidad = 'nueve mil ';                
                    }else{
                        if($millonesima){
                            $unidad = 'nueve millones ';
                        }else{
                            $unidad = 'nueve ';
                        }
                    }
                    break;
            }
        }

        if($milesima){
            $valor = substr($valor, 1, 3);
            return $unidad . Funciones::centena($valor, false, false, $mil);
        }else if($millonesima){
            $valor = substr($valor, 1, 6);            
            if($valor=='000000'){
                return $unidad . 'de pesos';
            }else{
                if($valor[0]=='0' && $valor[1]=='0' && $valor[2]=='0'){
                    $mil = false;                
                }
                return $unidad . Funciones::centena($valor, true, false, $mil); 
            }
        }

        if(!$plural && $valor=='1'){
            $unidad = $unidad . 'peso';
        }else{
            $unidad = $unidad . 'pesos';
        }

        return $unidad;
    }
    
    static function convertirPalabras($valor)
    {
        $numero = strval($valor);
        
        if(strlen($numero)===1){
            $numero = Funciones::unidad($numero, false, false, false, false, true, false);
        }else if(strlen($numero)==2){
            $numero = Funciones::decena($numero, false, false, true);
        }else if(strlen($numero)==3){
            $numero = Funciones::centena($numero, false, false, true);
        }else if(strlen($numero)==4){
            $numero = Funciones::unidad($numero, true, false, true, false, true, false);
        }else if(strlen($numero)==5){
            $numero = Funciones::decena($numero, true, false, true);
        }else if(strlen($numero)==6){
            $numero = Funciones::centena($numero, true, false, true);
        }else if(strlen($numero)==7){
            $numero = Funciones::unidad($numero, true, false, false, true, true, false);
        }else if(strlen($numero)==8){
            $numero = Funciones::decena($numero, false, true, true);
        }else if(strlen($numero)==9){
            $numero = Funciones::centena($numero, false, true, true);
        }      
        
        $numero = ucfirst($numero);
        
        return $numero;
    }
    
    static function formatoHora($horas, $minutos)
    {
        if($horas<10){
            $horas = "0" . $horas;
        }
        if($minutos<10){
            $minutos = "0" . $minutos;
        }
        return $horas . ":" . $minutos;
    }
    
    static function formatoPesos($valor, $currency=true, $round=true)
    {
        if($round){
            $valor = round($valor);
        }
        $valor = strval($valor);
        $len=strlen($valor);
        
        if($len > 3){                        
            $rest = $len % 3;
            if($rest==0){
                $rest = 3;
            }
            $text = "";            
            for($i=3, $j=0; $i<$len; $i+=3, $j++){
                $post = substr($valor, ($len - $i), 3);
                $text = '.' . $post . $text;
            }
            $post = substr($valor, 0, $rest);
            $valor = $post . $text;
        }
        
        if($currency){
            $valor = '$' . $valor;
        }
        
        $valor = str_replace("..", ",", $valor);
                
        return $valor;
    }
    
    static function ordinalDecena($index)
    {
        $texto = '';
        $dec = substr($index, 0, 1);
        $un = substr($index, 1, 1);
        $saltar = false;
        
        switch($dec){
            case '1':        
                switch($un){
                    case '0':
                        $texto = 'DÉCIMA';
                        $saltar = true;       
                        break;
                    case '1':
                        $texto = 'UNDÉCIMA'; 
                        $saltar = true;       
                        break;
                    case '2':
                        $texto = 'DUODÉCIMA';
                        $saltar = true;       
                        break;     
                    default:
                        $texto = 'DÉCIMO';
                        break; 
                    }                                    
                break;
            case '2':
                $texto = 'VIGÉSIMO';                  
                break;
            case '3':
                $texto = 'TRIGÉSIMO';                  
                break;
            case '4':
                $texto = 'CUADRAGÉSIMO';                  
                break;
            case '5':
                $texto = 'QUINCUAGÉSIMO';                  
                break;
            case '6':
                $texto = 'SEXAGÉSIMO';                  
                break;
            case '7':
                $texto = 'SEPTUAGÉSIMO';                  
                break;
            case '8':
                $texto = 'OCTOGÉSIMO';                  
                break;
            case '9':
                $texto = 'NONAGÉSIMO';                  
                break;
        }
        
        return $texto . Funciones::ordinalUnidad($un, $saltar);
    }
    
    static function ordinalUnidad($index, $saltar)
    {
        $texto = '';
        if(!$saltar){
            switch($index){
                case '1':                      
                    $texto = 'PRIMERA';                  
                    break;
                case '2':
                    $texto = 'SEGUNDA';                  
                    break;
                case '3':
                    $texto = 'TERCERA';                  
                    break;
                case '4':
                    $texto = 'CUARTA';                  
                    break;
                case '5':
                    $texto = 'QUINTA';                  
                    break;
                case '6':
                    $texto = 'SEXTA';                  
                    break;
                case '7':
                    $texto = 'SÉPTIMA';                  
                    break;
                case '8':
                    $texto = 'OCTAVA';                  
                    break;
                case '9':
                    $texto = 'NOVENA';                  
                    break;
            }
        }
        
        return $texto;
    }
    
    static function obtenerOrdinalTexto($index)
    {        
        $numero = strval($index);
        
        if(strlen($numero)===1){
            $numero = Funciones::ordinalUnidad($numero, false);
        }else if(strlen($numero)==2){
            $numero = Funciones::ordinalDecena($numero);
        }
        
        return $numero;
    }
    
    static function obtenerFechasTexto($inasistencia)
    {
        $texto = "";
        if($inasistencia['dias']>3){
            $texto = 'Desde el día ' . Funciones::obtenerFechaTexto($inasistencia['desde']) . ' hasta el día ' . Funciones::obtenerFechaTexto($inasistencia['hasta']) . ' (' . $inasistencia['dias'] . ' días)';
        }else{
            for($i=0, $len=$inasistencia['dias']; $i<$len; $i++){
                $fecha = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($inasistencia['desde'])));
                $texto = $texto . '- ' . Funciones::obtenerFechaTexto($fecha) . '<br />';
            }
        }
        
        return $texto;
    }
    
    static function obtenerFechasTextoLineal($inasistencia)
    {
        $texto = "";
        if($inasistencia['dias']>3){
            $texto = 'desde el día ' . Funciones::obtenerFechaTexto($inasistencia['desde']) . ' hasta el día ' . Funciones::obtenerFechaTexto($inasistencia['hasta']);
        }else{
            for($i=0, $len=$inasistencia['dias']; $i<$len; $i++){
                $fecha = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($inasistencia['desde'])));
                $texto = $texto . Funciones::obtenerFechaTexto($fecha) . ', ';
            }
        }
        
        return $texto;
    }
    
    static function obtenerMes($mes)
    {
        switch($mes){
            case 'Enero':                      
                $mes = '01';                  
                break;
            case 'Febrero':
                $mes = '02';                
                break;
            case 'Marzo':
                $mes = '03'; 
                break;
            case 'Abril':
                $mes = '04'; 
                break;
            case 'Mayo':
                $mes = '05';  
                break;
            case 'Junio':
                $mes = '06'; 
                break;
            case 'Julio':
                $mes = '07'; 
                break;
            case 'Agosto':
                $mes = '08'; 
                break;
            case 'Septiembre':
                $mes = '09';   
                break;
            case 'Octubre':
                $mes = '10'; 
                break;
            case 'Noviembre':
                $mes = '11'; 
                break;
            case 'Diciembre':
                $mes = '12';   
                break;
        }
        
        return $mes;
    }
    
    static function obtenerFechaRemuneracion($mes, $anio)
    {
        $mes = Funciones::obtenerMes($mes);
        $dia = Funciones::getUltimoDiaMes($anio, $mes);
        
        return $anio . "-" . $mes . "-" . $dia;
    }
    
    static function obtenerFechaRemuneracionMes($mes, $anio)
    {
        $dia = Funciones::getUltimoDiaMes($anio, $mes);
        
        return $anio . "-" . $mes . "-" . $dia;
    }
    
    static function obtenerFechaMes($mes, $anio)
    {
        $mes = Funciones::obtenerMes($mes);
        
        return $anio . "-" . $mes . "-01";
    }
    
    static function listaMeses($anio)
    {
        $meses = Config::get('constants.meses');    
        $lista = array();
        foreach($meses as $mes){
            $fecha = Funciones::obtenerFechaMes($mes['mes'], $anio);
            $fechaRemuneracion = Funciones::obtenerFechaRemuneracionMes($mes['mes'], $anio);
            $lista[] = array(
                'nombre' => $mes['value'],
                'mes' => $fecha,
                'fechaRemuneracion' => $fechaRemuneracion
            );
        }
        
        return $lista;
    }
    
    static function regularizarFecha($fecha){
        if($fecha){
            if(is_array($fecha)){
                $fecha = $fecha['date'];
            }
            if(strlen($fecha)>10){
                return substr($fecha, 0, 10);
            }
            return $fecha;
        }
        
        return NULL;
    }
    
    static function comprobarFecha($fecha, $formato)
    {            
        /*if($fecha){
            $fecha = Funciones::regularizarFecha($fecha);
            switch ($formato) {
                case 'd-m-Y': 
                    $dia = substr($fecha, 0, 2);
                    $mes = substr($fecha, 3, 2);
                    $anio = substr($fecha, 6, 4);
                    break;
                case 'm-d-Y': 
                    $mes = substr($fecha, 0, 2);
                    $dia = substr($fecha, 3, 2);
                    $anio = substr($fecha, 6, 4);
                    break;
                case 'Y-m-d': 
                    $anio = substr($fecha, 0, 4);
                    $mes = substr($fecha, 5, 2);
                    $dia = substr($fecha, 8, 2);
                    break;
                case 'Y-d-m': 
                    $anio = substr($fecha, 0, 4);
                    $dia = substr($fecha, 5, 2);
                    $mes = substr($fecha, 8, 2);
                    break;
                default:
                    return false;
                    break;
            }
            return checkdate($mes, $dia, $anio);
        }*/
        
        return true;
    }
    
    static function rangoMeses($rango)
    {
        $meses = new stdClass();    
        
        $mesDesde = substr($rango, 0, 2);
        $anioDesde = substr($rango, 3, 4);
        $mesHasta = substr($rango, 10, 2);
        $anioHasta = substr($rango, 13, 4);
        $meses->desde = $anioDesde . '-' . $mesDesde . '-01';
        $meses->hasta = $anioHasta . '-' . $mesHasta . '-01';
        
        return $meses;
    }
    
    static function crearMesesSQL($mes)
    {
        $meses = "";
        
        for($i=1; $i<=12; $i++){
            $dato = "'0',";
            if($i >= $mes){
                if($i!=12){
                    $dato = "'1',";
                }else{
                    $dato = "'1'";                    
                }
            }
            $meses .= $dato;
        }
        
        return $meses;
    }
    
    static function crearMesesAnio($anio)
    {
        $meses = array();
        for($i=1; $i<=12; $i++){
            $mes = $i;
            if($i<10){
                $mes = '0' . $i;
            }
            $mes = Funciones::obtenerFechaMes($mes, $anio);
            $meses[] = $mes;
        }
        
        return $meses;
    }
    
    static function obtenerRangoFechas($anio)
    {
        $datos = array(
            'desde' => $anio . '-01-01',
            'hasta' => $anio . '-12-31'
        );
        
        return $datos;
    }
    
    static function ordenar($arr, $llave, $orden=null)
    {
        $aux = array();
        
        foreach ($arr as $key => $row) {
            $aux[$key] = strtolower($row[$llave]);
        }
        
        if(strtolower($orden)=='desc'){
            array_multisort($aux, SORT_DESC, $arr);
        }else{
            array_multisort($aux, SORT_ASC, $arr);
        }
        
        return $arr;
    }
}
