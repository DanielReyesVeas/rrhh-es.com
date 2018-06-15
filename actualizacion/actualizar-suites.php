<?php
//rrhhes_principal
//rrhhes_principal_1234


$sistemas=array(
    'aaae',
    'ac',
    'asccs',
    'barritelli',
    'chargello',
    'cl083',
    'cl091',
    'cl107',
    'cl115',
    'cl123',
    'cl131',
    'cl162',
    'cl169',
    'cl176',
    'cl197',
    'cl204',
    'cl211',
    'cl218',
    'cl225',
    'demo',
    'dimet',
    'dssanfelipe',
    'ecoalliance',
    'grupodaso',
    'ibs',
    'jc',
    'master',
    'ofjuce',
    'pancho',
    'prueba2',
    'prueba4',
    'prueba7',
    'prueba8',
    'prueba11',
    'prueba12',
    'prueba13',
    'prueba14',
    'prueba15',
    'prueba16',
    'prueba18',
    'prueba19',
    'prueba21',
    'prueba22',
    'prueba23',
    'prueba24',
    'prueba25',
    'prueba26',
    'prueba27',
    'prueba28',
    'prueba29',
    'prueba30',
    'transearch'
);


/*
$sistemas=array(
    'demo'
);
*/

if(date("d-m-Y")=='25-05-2018'){
    foreach( $sistemas as $sistema ){
        
        $respuesta = file_get_contents('http://'.$sistema.'.rrhh-es.com/actualizacion-sql');

        echo $respuesta."<br/><p>&nbsp;</p>";
        echo "ACTUALIZO SISTEMA : ".$sistema."<br/>";
    }
}else{
    echo "No se realizó la actualización";
}


?>