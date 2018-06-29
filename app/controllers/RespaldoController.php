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
        define("OUTPUT_DIR", 'respq1w2e3y6t5r4');
		define("TABLES", '*');



		$listaArchivos=array();

		$DB_NAME = Config::get('cliente.CLIENTE.EMPRESA')."_principal";
		$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, $DB_NAME);
		$nombreArchivo = $backupDatabase->backupTables(TABLES, OUTPUT_DIR);
		if( $nombreArchivo ){
			$listaArchivos[]=$nombreArchivo;
		}



		$empresas = Empresa::all();
		if( $empresas->count() ) {
			foreach ($empresas as $empresa) {
				$DB_NAME = $empresa->base_datos;
				$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, $DB_NAME);
				$nombreArchivo = $backupDatabase->backupTables(TABLES, OUTPUT_DIR);
				if( $nombreArchivo ){
					$listaArchivos[]=$nombreArchivo;
				}
			}
		}

		if( count($listaArchivos) ){
			//se genera un archivo zip
			$archivoZip = "respaldo_".date("d-m-Y_H-i").".zip";
			$zip = new ZipArchive();
			if(($zip->open($archivoZip, ZipArchive::CREATE))!==true){ die('Error: Unable to create zip file');}

			foreach($listaArchivos as $archivo ){
				$file = public_path()."/".OUTPUT_DIR."/".$archivo;
				$zip->addFile($file, $archivo);
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
				$message->to('dreyes@easysystems.cl');
				$message->attach($pathToFile);
				if( $archivoStories ){
					$message->attach($archivoStories);
				} 
				$message->subject("EasySystems RRHH Respaldo Base Datos ". $datosCliente['NOMBRE']);
			});

			unlink($pathToFile);
			if( $archivoStories ) {
				unlink($archivoStories);
			}
			foreach($listaArchivos as $archivo ){
				unlink( public_path()."/".OUTPUT_DIR."/".$archivo );
			}
			echo "finalizado";
		}
	}

}