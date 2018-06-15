<?php
$sistemas=array(
    'ac',
    'prueba4',
    'prueba7',
    'prueba10',
    'prueba11',
    'prueba12',
    'prueba13',
    'prueba14',
    'prueba15',
    'prueba16',
    'prueba17',
    'prueba18',
    'prueba19',
    'prueba21'
);

$users=array(
    'rrhhes_ac',
    'rrhhes_p4',
    'rrhhes_p7',
    'rrhhes_p10',
    'rrhhes_p11',
    'rrhhes_p12',
    'rrhhes_p13',
    'rrhhes_p14',
    'rrhhes_p15',
    'rrhhes_p16',
    'rrhhes_p17',
    'rrhhes_p18',
    'rrhhes_p19',
    'rrhhes_p21'
);
$i=0;

/*$sistemas=array(
    'demo'
);*/
    

foreach( $sistemas as $sistema ){
    
    $archivoActualizacion = "actualizacion-sql-principal-".date("d-m-Y").".sql";

    if( file_exists(public_path()."/".$archivoActualizacion) ) {
        $userBaseDatos = $users[$i];

        // ejecucion local
       // $userBaseDatos = "root";
        /*echo "archivo SQL : ".$archivoActualizacion."<br/>";

        // obtengo las empresas
        $empresas = Empresa::all();
        if ($empresas->count()) {
            foreach ($empresas as $empresa) {
                $nombreBaseDatos = $empresa->base_datos;
                shell_exec("mysql -u ".$userBaseDatos." -p150611199 ".$nombreBaseDatos." < ".$archivoActualizacion);
                echo "Base de datos: " . $empresa->base_datos . " fue actualizada <br/>";
            }
        }*/
        echo "Finalizo el Proceso de Actualización";
    }else{
        echo "No se encontro el archivo de actualizacion";
    }
    
  //  $directorioRaiz = '/home/easysystems/public_html/'.$sistema;
    /*$directorioRaiz = '../'.$sistema;
    if( $directorioRaiz ){
        $controladores = $directorioRaiz."/app/controllers";
        $modelos = $directorioRaiz."/app/models";
        $vistas = $directorioRaiz."/app/views";
        $scriptAngularJS = $directorioRaiz."/public/scripts";
        $vistaAngularJS = $directorioRaiz."/public/views";
        $imagesAngularJS = $directorioRaiz."/public/images";
        $publicRoot = $directorioRaiz."/public";
        //$stylosPlantilla = $directorioRaiz."/public/assets/global/css";
        //$logosPlantilla = $directorioRaiz."/public/assets/global/images/logo";
        $appRoot = $directorioRaiz."/app";

        
        echo "ACTUALIZO SISTEMA : ".$sistema."<br/>";
    }*/
    $i++;
}


?>