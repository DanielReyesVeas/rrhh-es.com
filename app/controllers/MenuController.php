<?php
class MenuController extends BaseController {
    public function objetosFormulario(){
    	$padres= array_merge( array( array('id' => 0, 'value' => "Raiz") ), MenuSistema::get_all_padres());
    	$posiciones=array();
    	for($a=1; $a < 50; $a++) $posiciones[] = array("id" => $a, "value" => $a);
    	
    	$datos=array(
    		'padres' => $padres,
    		'tipos' => Config::get('constants.tipo_menu'),
    		'posiciones' => ( $posiciones ),
    		'categorias'  => Config::get('constants.categoria_menu')
    	);
    	return Response::json($datos);
    }
    
    public function index() {

        $menu = MenuSistema::estructura_menu(0);
        $posicion=1;
        if( count($menu) ){
            foreach($menu as $item){
                $datos['datos'][] = array(
                    'posicion' => $posicion,
                    'id' => $item['datos']->id,
                    'sid' => $item['datos']->sid,
                    'menu' => $item['datos']->menu,
                    'href' => $item['datos']->href,
                    'click' => $item['datos']->onclick,
                    'acceso' => $item['datos']->administrador == "1"? "Administración" : ( $item['datos']->administrador == "2"?  "Empresas" : "Todos" ),
                    'title' => $item['datos']->title,
                    'fontawesome' => $item['datos']->fontawesome,
                    'tipo' => 1
                );

                if(count($item['hijos'])){
                    $posicionHij=1;
                    foreach($item['hijos'] as $ind2 =>$item2){
                        $datos['datos'][] = array(
                            'posicion' => $posicionHij,
                            'sid' => $item2['datos']->sid,
                            'id' => $item2['datos']->id,
                            'menu' => $item2['datos']->menu,
                            'href' => $item2['datos']->href,
                            'click' => $item2['datos']->onclick,
                            'acceso' => $item2['datos']->administrador == "1"? "Administración" :  ( $item2['datos']->administrador == "2"?  "Empresas" : "Todos" ),
                            'title' => $item2['datos']->title,
                            'fontawesome' => $item2['datos']->fontawesome,
                            'tipo' => 2
                        );

                        if(count($item2['hijos'])){
                            $posicionHij2=1;
                            foreach($item2['hijos'] as $ind3 =>$item3){
                                $datos['datos'][] = array(
                                    'posicion' => $posicionHij2,
                                    'id' => $item3['datos']->id,
                                    'sid' => $item3['datos']->sid,
                                    'menu' => $item3['datos']->menu,
                                    'href' => $item3['datos']->href,
                                    'click' => $item3['datos']->onclick,
                                    'acceso' => $item3['datos']->administrador == "1"? "Administración" :  ( $item3['datos']->administrador == "2"?  "Empresas" : "Todos" ),
                                    'title' => $item3['datos']->title,
                                    'fontawesome' => $item3['datos']->fontawesome,
                                    'tipo' => 3
                                );

                                $posicionHij2++;
                            }
                        }
                        $posicionHij++;
                    }
                }
                $posicion++;
            }
        }

        $padres= array_merge( array( array('id' => 0, 'value' => "Raiz") ), MenuSistema::get_all_padres());
        $posiciones=array();
        for($a=1; $a < 50; $a++) $posiciones[] = array("id" => $a, "value" => $a);

        $datos['opciones']=array(
            'padres' => $padres,
            'tipos' => Config::get('constants.tipo_menu'),
            //'departamentos' =>array_merge( array( array('id' => 0, 'dependencia' => 'Generico', 'institucion' => 'Generico')), Departamento::listaDepartamentos() ),
            'posiciones' => ( $posiciones ),
            'categorias'  => Config::get('constants.categoria_menu'),
            'tipos_opciones' => Config::get('constants.tipos_opciones')
        );
        return Response::json($datos);
    }
    
    public function store() { 
        $datos = $this->get_datos_formulario();
        $erroresMen = MenuSistema::errores($datos);
        if(!$erroresMen){
            // se almacena la informacion
            $menu = new MenuSistema();
            $menu->sid = Funciones::generarSID();
            $menu->menu = $datos['nombre'];
            $menu->tipo = $datos['tipo'];
            $menu->padre_id = $datos['padre'];
            $menu->href = $datos['href'];
            $menu->onclick = $datos['onclick'];
            $menu->title = $datos['titulo'];
            $menu->administrador = $datos['administrador'];
            $menu->posicion = $datos['posicion'];
            $menu->fontawesome = $datos['font-awesome'];
            $menu->estado = 1;
            $menu->save();

            if( is_array($datos['permisos']) ){
                if( count($datos['permisos']) ){
                    foreach( $datos['permisos'] as $permiso => $valor ){
                        if( $valor ){
                            $per = new MenuSistemaPermiso();
                            $per->menu_id = $menu->id;
                            $per->opcion = $permiso;
                            $per->save();
                        }
                    }
                }
            }

            MenuSistema::reordenar($datos['padre'], $datos['posicion'], 1000, $menu->id);

            $MENUSISTEMA = $this->generarMenuSistema(0,false);
            $respuesta = array(
                'success' => true,
                'mensaje' => "La Información fue almacenada correctamente",
                'menu'    => $MENUSISTEMA['menu'],
                'sid' => $menu->sid
            );
            
        }else{
        	// existen errores
        	$respuesta = array(
        			'success' => false,
        			'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
        			'errores' => $erroresMen
        	);
        }
        return Response::json( $respuesta );
    }
    
    public function show($id) { 
        $menu = MenuSistema::whereSid($id)->first();
        $datos = array(
        	'sid' => $menu->sid,
        	'nombre' => $menu->menu,
        	'titulo' => $menu->title,
        	'tipo' => $menu->tipo,
        	'padre' => $menu->padre_id,
        	'posicion' => $menu->posicion,
        	'administrador' => $menu->administrador,
            'permisos' => $menu->listaPermisos(),
        	'href' => $menu->href,
        	'onclick' => $menu->onclick,
            'fontawesome' => $menu->fontawesome
        );
        return Response::json($datos);
    }



    public function update($id) { 
        $menu = MenuSistema::whereSid($id)->first();
        $datos = $this->get_datos_formulario();
    
        $erroresMen = MenuSistema::errores($datos);
        if(!$erroresMen){
            // se almacena la informacion
            MenuSistema::reordenar($datos['padre'], $datos['posicion'], $menu->posicion, $id);
            $menu->menu = $datos['nombre'];
            $menu->tipo = $datos['tipo'];
            $menu->padre_id = $datos['padre'];
            $menu->href = $datos['href'];
            $menu->onclick = $datos['onclick'];
            $menu->title = $datos['titulo'];
            $menu->administrador = $datos['administrador'];
            $menu->posicion = $datos['posicion'];
            $menu->fontawesome = $datos['font-awesome'];
            $menu->save();


            $MENUSISTEMA = $this->generarMenuSistema(0,false);

           	$respuesta = array(
           		'success' => true,
           		'mensaje' => "La Información fue actualizada correctamente",
                'menu'    => $MENUSISTEMA['menu'],
           			'sid' => $menu->sid
           	);
        }else{
        	// existen errores
	        $respuesta = array(
	        	'success' => false,
	        	'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
	        	'errores' => json_decode($erroresMen)
	        );
        }
        
        return Response::json( $respuesta );
   
    }
    
    public function destroy($id) { 
        $mensaje="La Información fue eliminada correctamente";
        MenuSistema::where('sid', $id)->delete();
        return Response::json( array( 'success' => true, 'mensaje' => $mensaje));
    }


    public function menu_perfil_usuario() {

        $menu = MenuSistema::estructura_menu(0);
        $posicion=1;
        if( count($menu) ){
            foreach($menu as $item){
                if($item['datos']->administrador!=4){
                    if( !in_array( $item['datos']->href, array('#funcionarios', '#perfiles', '#recaudadores', '#tipos-carga') ) ) {

                        $datos['datos'][] = array(
                            'posicion' => $posicion,
                            'sid' => $item['datos']->sid,
                            'menu' => $item['datos']->menu,
                            'title' => $item['datos']->title,
                            'tipo' => 1
                        );

                        if (count($item['hijos'])) {
                            $posicionHij = 1;
                            foreach ($item['hijos'] as $ind2 => $item2) {
                                if( !in_array( $item2['datos']->href, array('#funcionarios', '#perfiles', '#recaudadores', '#tipos-carga') ) ) {
                                    $datos['datos'][] = array(
                                        'posicion' => $posicionHij,
                                        'sid' => $item2['datos']->sid,
                                        'menu' => $item2['datos']->menu,
                                        'title' => $item2['datos']->title,
                                        'tipo' => 2
                                    );
                                    $posicionHij++;
                                }

                            }
                        }
                    }
                }
            }
        }

        return Response::json($datos);
    }

    public function get_datos_formulario(){
        $datos  =  array(
			'nombre' => Input::get('nombre'),
			'titulo' => Input::get('titulo'),
			'tipo' => Input::get('tipo')['id'],
			'padre' => Input::get('padre')['id'],
			'posicion' => Input::get('posicion')['id'],
			'administrador' => Input::get('administrador')['id'],
            'permisos' => Input::get('permisos'),
			'href' => Input::get('href'),
			'onclick' => Input::get('onclick'),
            'font-awesome' => Input::get('fontawesome')
		);
        return $datos;
    }

    public function generarMenuSistema($empresa_id=0, $menuAdmin=false){
        // obtenemos el menu del usuario
        $opcionesMenu=array();
        $permisos = array();
        $opciones=array();
        $a = 0;
        if( $empresa_id ){
            $empresa = Empresa::find($empresa_id);
            \Session::put('empresa', $empresa);
            
            if(Auth::usuario()->user()->id == 1){
                
                if( $menuAdmin ){
                    $opciones = MenuSistema::where('administrador', '<=', '2')->get();
                }else{
                    $opciones = MenuSistema::where('administrador', '<>', '4')->get();
                }
                
                if(count($opciones)){
                    foreach($opciones as $datos){
                        $opcionesMenu[]=$datos->id;
                    }
                }                
                // para manejar el menu de opciones
                $opcionesMenu[]=1000000;
            }else if(Auth::usuario()->user()->perfil_id==2){
                $opciones = MenuSistema::where('administrador', '4')->where('tipo', '2')->get();
                $padre = MenuSistema::where('administrador', '4')->where('tipo', '1')->first();
                $accesos = array();
                $subopciones = array();
                $MENU_USUARIO = array();
                if(count($opciones)){
                    foreach($opciones as $datos){
                        if($datos->menu=='Mis Liquidaciones de Sueldo'){
                            $accesos[]=str_replace("#", "/", $datos->href);
                            $subopciones[] = array(
                                'fontawesome' => $datos->fontawesome,
                                'link' => $datos->href,
                                'onclick' => '',
                                'opcion' => $datos->menu,
                                'subopciones' => array()
                            );
                        }
                    }
                }
                
                $MENU_USUARIO[] = array(
                    'fontawesome' => $padre['fontawesome'],
                    'link' => $padre['href'],
                    'onclick' => '',
                    'opcion' => $padre['menu'],
                    'subopciones' => $subopciones
                );
                
                $data = array(
                    'menu' => $MENU_USUARIO, 
                    'inicio' => "/mis-liquidaciones",
                    'secciones' => $accesos,
                    'opciones' => $opciones
                );

                return $data;
            }else{
                if( $menuAdmin ){ 
                    $opciones = MenuSistema::where('administrador', '<=', '2')->get();
                }else{
                    $opciones = MenuSistema::where('administrador', '<>', '4')->get();
                }                
                $permisos = Auth::usuario()->user()->misPermisos();
                $opcionesMenu = Auth::usuario()->user()->listaAccesosEmpresa($empresa_id);
            }
        }else{
            if(Auth::usuario()->user()->id == 1){  
                if( $menuAdmin ){
                    $opciones = MenuSistema::where('administrador', '!=', '2')->where('administrador', '<>', '4')->get();
                }else{
                    $opciones = MenuSistema::where('administrador', '<>', '4')->get();
                }

                if(count($opciones)){
                    foreach($opciones as $datos){
                        $opcionesMenu[]=$datos->id;
                    }
                }
                // para manejar el menu de opciones
                $opcionesMenu[]=1000000; 
            }else{
                if( $menuAdmin ){
                $a = 1;
                    $opciones = MenuSistema::where('administrador', '>=', '2')->get();                    
                }else{                   
                $a = 2;
                    $opciones = MenuSistema::where('administrador', '<>', '4')->where('administrador', '<>', '2')->get();
                }
                if(count($opciones)){
                    foreach($opciones as $datos){
                        $opcionesMenu[]=$datos->id;
                    }
                }
                $permisos = Auth::usuario()->user()->misPermisos();
            }            
        }
        $option = $opciones;        
        // se obtiene el menu
        $indice=0;
        // se obtiene el menu
        $menu = MenuSistema::estructura_menu(0);
        $MENU_USUARIO=array();
        $primeraOpcion="";
        $secciones=array();
        if($empresa_id){
            $empresa = \Session::get('empresa');
            $isCME = $empresa->cme;
            $portal = $empresa->portal ? true : false;
            $centroCosto = $empresa->centro_costo ? true : false;
            if($empresa->gratificacion=='e'){
                $gratificacionAnual = ($empresa->tipo_gratificacion=='a') ? true : false;
            }else{
                $gratificacionAnual = true;            
            }
        }else{
            $isCME = false;
            $portal = false;
            $gratificacionAnual = false;
            $centroCosto = false;
        }
        if(count($menu)){
            $indice=0;            
            foreach($menu as $datomen){
                
                if (in_array($datomen['id'], $opcionesMenu)) {                    
                    if (count($datomen['hijos'])) {
                        if(Auth::usuario()->user()->id==1 || Auth::usuario()->user()->id>1 && in_array($datomen['id'], $permisos)){
                            $MENU_USUARIO[$indice] = array(
                                "link" => $datomen['datos']->href,
                                "onclick" => $datomen['datos']->onclick,
                                "opcion" => $datomen['datos']->menu,
                                "fontawesome" => $datomen['datos']->fontawesome,
                                "subopciones" => array(),
                                'data' => $a
                            );
                        }
                        $indice2 = 0;
                        foreach ($datomen['hijos'] as $datoOpc) {
                            if(Auth::usuario()->user()->id==1 || Auth::usuario()->user()->id>1 && in_array($datoOpc['id'], $permisos)){
                                if (in_array($datoOpc['id'], $opcionesMenu)) {
                                    $bool = ($datoOpc['id']!=142 || $gratificacionAnual);
                                    if($bool){
                                        $isPortal = ($datoOpc['id']!=150 || $portal);
                                        $isCentroCosto = ($datoOpc['id']!=146 || $centroCosto);
                                        $isCuentas = ($datoOpc['id']!=148 || !$isCME);
                                        if($isPortal && $isCuentas && $isCentroCosto){
                                            $MENU_USUARIO[$indice]['subopciones'][$indice2] = array(
                                                "link" => $datoOpc['datos']->href,
                                                "onclick" => $datoOpc['datos']->onclick,
                                                "opcion" => $datoOpc['datos']->menu,
                                                "fontawesome" => $datoOpc['datos']->fontawesome,
                                                "subopciones" => array()
                                            );                                

                                            if( count($datoOpc['hijos']) ){
                                                $indice3 = 0;
                                                foreach ($datoOpc['hijos'] as $datoOpc2) {

                                                    if (in_array($datoOpc2['id'], $opcionesMenu)) {
                                                        $MENU_USUARIO[$indice]['subopciones'][$indice2]['subopciones'][$indice3] = array(
                                                            "link" => $datoOpc2['datos']->href,
                                                            "onclick" => $datoOpc2['datos']->onclick,
                                                            "opcion" => $datoOpc2['datos']->menu,
                                                            "fontawesome" => $datoOpc2['datos']->fontawesome,
                                                            "subopciones" => array()
                                                        );

                                                        if (!$primeraOpcion and strlen($datoOpc2['datos']->href) > 3) {
                                                            $primeraOpcion = $datoOpc2['datos']->href;
                                                        }
                                                        $secciones[] = str_replace("#", "/", $datoOpc2['datos']->href);

                                                        $indice3++;
                                                    }
                                                }
                                            }else{
                                                if (!$primeraOpcion and strlen($datoOpc['datos']->href) > 3) {
                                                    $primeraOpcion = $datoOpc['datos']->href;
                                                }
                                                $secciones[] = str_replace("#", "/", $datoOpc['datos']->href);
                                            }

                                            $indice2++;
                                        }
                                    }
                                }
                            }
                        }

                    } else {                        
                        if (!count($MENU_USUARIO)) {
                            $MENU_USUARIO[$indice] = array(
                                "link" => $datomen['datos']->href,
                                "onclick" => $datomen['datos']->onclick,
                                "opcion" => $datomen['datos']->menu,
                                "fontawesome" => $datomen['datos']->fontawesome,
                                "subopciones" => array()
                            );
                        } else {
                            $MENU_USUARIO[$indice] = array(
                                "link" => $datomen['datos']->href,
                                "onclick" => $datomen['datos']->onclick,
                                "opcion" => $datomen['datos']->menu,
                                "fontawesome" => $datomen['datos']->fontawesome,
                                "subopciones" => array()
                            );
                        }

                        if (!$primeraOpcion and strlen($datomen['datos']->href) > 3) {
                            $primeraOpcion = $datomen['datos']->href;
                        }
                        $secciones[] = str_replace("#", "/", $datomen['datos']->href);
                    }
                    $indice++;
                }
            }            
        }

        if(in_array(1000000, $opcionesMenu)){
            $MENU_USUARIO[$indice]=array(
                "link" => "#menu",
                "opcion" => "Adm. Menú",
                "fontawesome" => "fa-list",
                "subopciones" => array()
            );
            $indice++;
            $MENU_USUARIO[$indice]=array(
                "link" => "#reportes",
                "opcion" => "Reportes",
                "fontawesome" => "fa-eye",
                "subopciones" => array()
            );
            $secciones[]=str_replace("#", "/", "#menu");
            $secciones[]=str_replace("#", "/", "#reportes");
        }
        
        $data = array(
            'menu' => $MENU_USUARIO, 
            'inicio' => str_replace("#", "/", $primeraOpcion), 
            'secciones' => $secciones
        );
        
        return $data;
    }
    
    public function generarMenuEmpleado($empresa_id){
        // obtenemos el menu del usuario
        $opciones=array();
            
        $empresa = Empresa::find($empresa_id);
            
        $opciones = MenuSistema::where('administrador', '4')->where('tipo', '2')->get();
        $padre = MenuSistema::where('administrador', '4')->where('tipo', '1')->first();
        $accesos = array();
        $subopciones = array();
        $MENU_USUARIO = array();
        
        if(count($opciones)){
            foreach($opciones as $datos){
                if($datos->menu!='Solicitudes'){
                    $accesos[]=str_replace("#", "/", $datos->href);
                    $subopciones[] = array(
                        'fontawesome' => $datos->fontawesome,
                        'link' => $datos->href,
                        'onclick' => '',
                        'opcion' => $datos->menu,
                        'subopciones' => array()
                    );
                }
            }
        }

        $MENU_USUARIO[] = array(
            'fontawesome' => $padre['fontawesome'],
            'link' => $padre['href'],
            'onclick' => '',
            'opcion' => $padre['menu'],
            'subopciones' => $subopciones
        );

        $data = array(
            'menu' => $MENU_USUARIO, 
            'inicio' => "/mis-liquidaciones",
            'secciones' => $accesos,
            'opciones' => $opciones
        );

        return $data;
    }
}
