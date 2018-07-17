<?php

class RespaldoController extends \BaseController {

	public function index()
	{
		$datosCliente = Config::get('cliente.CLIENTE');
		/**
		 * Define database parameters here
		 */
        define("DB_USER", Config::get('cliente.CLIENTE.USUARIO') );
        define("DB_PASSWORD", Config::get('cliente.CLIENTE.PASS'));
        define("DB_HOST", 'localhost');
        define("OUTPUT_DIR", '/home/rrhhes/public_html/respq1w2e3y6t5r4');
		define("TABLES", '*');



		$listaArchivos=array();

		$DB_NAME = Config::get('cliente.CLIENTE.EMPRESA')."_principal";
		
		$archivoZip = "respaldo_".$DB_NAME."_".date("d-m-Y_H-i").".sql.gz";
        shell_exec("mysqldump -u ".DB_USER." -p".DB_PASSWORD." ".$DB_NAME." | gzip -9 > ".OUTPUT_DIR."/".$archivoZip);

        $pathToFile = OUTPUT_DIR."/".$archivoZip;
        if( file_exists($pathToFile) ){
            $listaArchivos[]=$archivoZip;
        }


		$empresas = Empresa::all();
		if( $empresas->count() ) {
			foreach ($empresas as $empresa) {
				$DB_NAME = $empresa->base_datos;
				$archivoZip = "respaldo_".$DB_NAME."_".date("d-m-Y_H-i").".sql.gz";
                shell_exec("mysqldump -u ".DB_USER." -p".DB_PASSWORD." ".$DB_NAME." | gzip -9 > ".OUTPUT_DIR."/".$archivoZip);
                $pathToFile = OUTPUT_DIR."/".$archivoZip;
                if( file_exists($pathToFile) ){
                    $listaArchivos[]=$archivoZip;
                }
			}
		}

		if( count($listaArchivos) ){
			//se genera un archivo zip
			$archivoZip = Config::get('cliente.CLIENTE.EMPRESA')."_".date("d-m-Y_H-i").".zip";
			$zip = new ZipArchive();
			if(($zip->open($archivoZip, ZipArchive::CREATE))!==true){ die('Error: Unable to create zip file');}

			foreach($listaArchivos as $archivo ){
				$file = OUTPUT_DIR."/".$archivo;
                if( file_exists($file) ) {
                    $zip->addFile($file, $archivo);
                }
			}
			$zip->close();

			$pathToFile = public_path()."/".$archivoZip;
			$data = array(
					'archivo' => $archivoZip,
					'cliente' => $datosCliente['EMPRESA']
			);



			// se respalda la carpeta stories
			$zip = new ZipArchive();

			$dir = 'stories/';

			$rutaFinal = public_path();

			$archivoZip = "stories.zip";

			$archivoStories="";

			if ($zip->open($archivoZip, ZIPARCHIVE::CREATE) === true) {
				Funciones::agregar_zip($dir, $zip);
				$zip->close();
			//	rename($archivoZip, $rutaFinal."/".$archivoZip);
				if (file_exists($rutaFinal. "/" . $archivoZip)) {
					$archivoStories= $rutaFinal. "/" . $archivoZip;
				}
			}


			// se envia a los correos electronicos
			Mail::send('correo_respaldos', $data, function($message) use($pathToFile, $datosCliente, $archivoStories)
			{
				$message->to('backup@rrhh-es.com')->cc('max_nzgz@yahoo.com');
				$message->attach($pathToFile);
				if( $archivoStories ){
					$message->attach($archivoStories);
				} 
				$message->subject("EasySystems RRHH Respaldo Base Datos ". $datosCliente['NOMBRE']);
			});

			if( file_exists($pathToFile) ) {
                unlink($pathToFile);
            }
			if( $archivoStories ) {
                if( file_exists($archivoStories) ) {
                    unlink($archivoStories);
                }
			}
			foreach($listaArchivos as $archivo ){
                if( file_exists(OUTPUT_DIR . "/" . $archivo) ) {
                    unlink(OUTPUT_DIR . "/" . $archivo);
                }
			}

			echo "finalizado";
		}
	}

}