<?php
//rrhhes_principal
//rrhhes_principal_1234

$sistemas=array(
    'ac',
    'asccs',
    'barritelli',
    'cl075',
    'cl083',
    'cl091',
    'cl107',
    'cl115',
    'cl123',
    'cl131',
    'cl162',
    'cl169',
    'cl176',
    'demo',
    'dimet',
    'estay',
    'ibs',
    'jc',
    'master',
    'ofjuce',
    'pancho',
    'prueba4',
    'prueba7',
    'prueba11',
    'prueba14',
    'transearch'
);

/*$sistemas=array(
    'demo'
);*/
    
foreach( $sistemas as $sistema ){
  //  $directorioRaiz = '/home/easysystems/public_html/'.$sistema;
    $directorioRaiz = '../'.$sistema;
    if( $directorioRaiz ){
        $controladores = $directorioRaiz."/app/controllers";
        $modelos = $directorioRaiz."/app/models";
        $composer = $directorioRaiz."/vendor/composer";
        $vistas = $directorioRaiz."/app/views";
        $planillas = $directorioRaiz."/public/planillas";
        $scriptAngularJS = $directorioRaiz."/public/scripts";
        $vistaAngularJS = $directorioRaiz."/public/views";
        $imagesAngularJS = $directorioRaiz."/public/images";
        $stylesAngularJS = $directorioRaiz."/public/styles";
        $actualizacionesSQL = $directorioRaiz."/public/actualizacionesSQL";
        $publicRoot = $directorioRaiz."/public";
        //$stylosPlantilla = $directorioRaiz."/public/assets/global/css";
        //$logosPlantilla = $directorioRaiz."/public/assets/global/images/logo";
        $appRoot = $directorioRaiz."/app";
    
        
        // actualizacionesSQL
        if ( is_dir('php/actualizacionesSQL') ) {
            if ( is_dir($scriptAngularJS) ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('php/actualizacionesSQL');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $actualizacionesSQL );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $actualizacionesSQL.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('php/actualizacionesSQL/' . $entrada, $actualizacionesSQL . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }
        
        // vistas de angularJS
       $dirViewAngularJS=array('comun', 'forms');
        foreach( $dirViewAngularJS as $dirView ) {
            if (is_dir('angularjs/'.$dirView)) {
                if (is_dir($vistaAngularJS . "/".$dirView)) {
                    // se recorren los archivos del origen y se reemplazan
                    $gestor = opendir('angularjs/'.$dirView);
                    if ($gestor) {
                        while (false !== ($entrada = readdir($gestor))) {
                            if ($entrada != "." && $entrada != "..") {
                                copy('angularjs/'.$dirView.'/' . $entrada, $vistaAngularJS . "/".$dirView."/" . $entrada);
                            }
                        }
                        closedir($gestor);
                    }
                }
            }
        }

        // scripts de angularJS
        if ( is_dir('dist/scripts') ) {
            if ( is_dir($scriptAngularJS) ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/scripts');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $scriptAngularJS );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $scriptAngularJS.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('dist/scripts/' . $entrada, $scriptAngularJS . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }
    
        
        
        // styles de angularJS
        if( is_dir('dist/styles') ) {
            if ( is_dir($stylesAngularJS) ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/styles');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $stylesAngularJS );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $stylesAngularJS.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }                            
                            copy('dist/styles/' . $entrada, $stylesAngularJS . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }

        // planillas
        if ( is_dir('php/planillas') ) {
            if ( is_dir($planillas) ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('php/planillas');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $planillas );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $planillas.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('php/planillas/' . $entrada, $planillas . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }
        
        // imagenes de angular
        if ( is_dir('dist/images') ) {
            if ( is_dir($imagesAngularJS) ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/images');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $imagesAngularJS );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $imagesAngularJS.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('dist/images/' . $entrada, $imagesAngularJS . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }

        

        // imagenes de angular accesos
        if ( is_dir('dist/images/accesos') ) {
            if ( is_dir($imagesAngularJS.'/accesos') ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/images/accesos');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $imagesAngularJS.'/accesos' );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $imagesAngularJS.'/accesos'.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('dist/images/accesos/' . $entrada, $imagesAngularJS.'/accesos' . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }
        
        // imagenes de angular dashboard
        if ( is_dir('dist/images/dashboard') ) {
            if ( is_dir($imagesAngularJS.'/dashboard') ) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/images/dashboard');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $imagesAngularJS.'/dashboard' );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $imagesAngularJS.'/dashboard'.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('dist/images/dashboard/' . $entrada, $imagesAngularJS.'/dashboard' . "/". $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }

        // controllers de laravel
        if(is_dir('php/controllers')) {
            if (is_dir($controladores)) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('php/controllers');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $controladores );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $controladores . '/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('php/controllers/'. $entrada, $controladores . "/" . $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }

        // models de laravel
        if (is_dir('php/models')) {
            if (is_dir($modelos)) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('php/models');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $modelos );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $modelos . '/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('php/models/'. $entrada, $modelos . "/" . $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }


        // views de laravel
        $dirViewPHP=array('excel', 'pdf');
        foreach( $dirViewPHP as $dirView ) {
            if (is_dir('php/views/'.$dirView)) {
                if (is_dir($vistas . "/".$dirView)) {
                    // se recorren los archivos del origen y se reemplazan
                    $gestor = opendir('php/views/'.$dirView);
                    if ($gestor) {
                        $limpiarDir=true;
                        while (false !== ($entrada = readdir($gestor))) {
                            if ($entrada != "." && $entrada != "..") {
                                if( $limpiarDir ){
                                    // se limpia el directorio de destino
                                    $gestorDest = opendir( $vistas . "/".$dirView );
                                    if ($gestorDest) {
                                        while (false !== ($entradaDest = readdir($gestorDest))) {
                                            if ($entradaDest != "." && $entradaDest != "..") {
                                                unlink( $vistas . "/".$dirView . '/'.$entradaDest );
                                            }
                                        }
                                    }
                                    $limpiarDir=false;
                                }
                                copy('php/views/'.$dirView.'/' . $entrada, $vistas . "/".$dirView."/" . $entrada);
                            }
                        }
                        closedir($gestor);
                    }
                }
            }
        }


        // stylos de la plantilla
        if (is_dir('dist/assets')) {
            if (is_dir($stylosPlantilla)) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/assets');
                if ($gestor) {
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            copy('dist/assets/'. $entrada, $stylosPlantilla . "/" . $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }


        if (is_dir('dist/assets/logos')) {
            if (is_dir($logosPlantilla)) {
                $limpiarDir=true;
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('dist/assets/logos');
                if ($gestor) {
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $logosPlantilla );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $logosPlantilla.'/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('dist/assets/logos/'. $entrada, $logosPlantilla . "/" . $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }

        // se reemplaza el archivo index.html por index.php
        if( file_exists('dist/index.html') ){
            copy('dist/index.html', $vistas.'/index.php');
        }


        // se reemplaza el archivo routes.php
        if( file_exists('php/routes.php') ){
            copy('php/routes.php', $directorioRaiz."/app/routes.php");
        }

        if( file_exists('php/config/constants.php') ){
            copy('php/config/constants.php', $directorioRaiz."/app/config/constants.php");
        }
        
        /*if( file_exists('php/config/mail.php') ){
            copy('php/config/mail.php', $directorioRaiz."/app/config/mail.php");
        }*/


        if( file_exists('php/views/correo_respaldos.blade.php') ){
            copy('php/views/correo_respaldos.blade.php', $directorioRaiz."/app/views/correo_respaldos.blade.php");
        }
        
        if( file_exists('php/libraries/funciones.php') ){
            copy('php/libraries/funciones.php', $directorioRaiz."/app/libraries/funciones.php");
        }
        
        if( file_exists('php/libraries/backup.php') ){
            copy('php/libraries/backup.php', $directorioRaiz."/app/libraries/backup.php");
        }

        if( file_exists('php/estructura_empresa.sql') ){
            copy('php/estructura_empresa.sql', $publicRoot."/estructura_empresa.sql");
        }

        if( file_exists('VERSIONAPP.dat') ){
            copy('VERSIONAPP.dat', $appRoot."/VERSIONAPP.dat");
        }

        // se borran las sesiones de usuario abiertas en el sistema
        
        $gestorDest = opendir( $appRoot.'/storage/sessions' );
        if ($gestorDest) {
            while (false !== ($entradaDest = readdir($gestorDest))) {
                if ($entradaDest != "." && $entradaDest != ".." && $entradaDest != '.gitignore') {
                    unlink( $appRoot.'/storage/sessions/'.$entradaDest );
                }
            }
        }
        
        
        //Composer
        if (is_dir('php/vendor/composer')) {
            if (is_dir($composer)) {
                // se recorren los archivos del origen y se reemplazan
                $gestor = opendir('php/vendor/composer');
                if ($gestor) {
                    $limpiarDir=true;
                    while (false !== ($entrada = readdir($gestor))) {
                        if ($entrada != "." && $entrada != "..") {
                            if( $limpiarDir ){
                                // se limpia el directorio de destino
                                $gestorDest = opendir( $composer );
                                if ($gestorDest) {
                                    while (false !== ($entradaDest = readdir($gestorDest))) {
                                        if ($entradaDest != "." && $entradaDest != "..") {
                                            unlink( $composer . '/'.$entradaDest );
                                        }
                                    }
                                }
                                $limpiarDir=false;
                            }
                            copy('php/vendor/composer/'. $entrada, $composer . "/" . $entrada);
                        }
                    }
                    closedir($gestor);
                }
            }
        }
        
        if( file_exists('php/vendor/autoload.php') ){
            copy('php/vendor/autoload.php', $directorioRaiz."/vendor/autoload.php");
        }
        
        

        /*
            ACTUALIZACION DEL CONFIG PARA AGREGAR BASE DE DATOS GLOBAL
        */

        /*$configuracion = "
        'global' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'cmees_global',
            'username'  => 'cmees_global',
            'password'  => 'easy1q2w3e4r',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
        'principal' => array(";

        $path = $appRoot."/config";
        $contenido = file_get_contents($path."/database.php");
        $contenido = str_replace("'principal' => array(", $configuracion, $contenido);

        // sobre-escribir archivo de configuraciones
        file_put_contents( $path."/database.php", $contenido );*/

        echo "ACTUALIZO SISTEMA : ".$sistema."<br/>";
    }
}


?>