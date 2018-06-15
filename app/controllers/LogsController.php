<?php

class LogsController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $mes = date('Y-m-d');
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
        $logs = Logs::where('created_at', '>=', new DateTime($mesAnterior))->orderBy('menu_id')->orderBy('created_at', 'DESC')->get();
        $listaLogs=array();
        
        if( $logs->count() ){
            foreach( $logs as $log ){
                $listaLogs[]=array(
                    'id' => $log->id,
                    'accion' => $log->accion,                    
                    'datoOrden' => $log->dato_nombre,
                    'dato' => array(
                        'id' => $log->dato_id,
                        'nombre' => $log->dato_nombre
                    ),
                    'dato2Orden' => $log->dato2_nombre,
                    'dato2' => array(
                        'id' => $log->dato2_id,
                        'nombre' => $log->dato2_nombre
                    ),
                    'dato3Orden' => $log->dato3_nombre,
                    'dato3' => array(
                        'id' => $log->dato3_id,
                        'nombre' => $log->dato3_nombre
                    ),
                    'encargadoOrden' => $log->encargado,
                    'encargado' => array(
                        'id' => $log->encargado_id,
                        'nombre' => $log->encargado
                    ),
                    'idMenu' => $log->menuSistema->padre->id,
                    'menuOrden' => $log->menuSistema->padre->menu,
                    'menu' => array(
                        'id' => $log->menuSistema->padre->id,
                        'nombre' => $log->menuSistema->padre->menu
                    ),
                    'idSubmenu' => $log->menu_id,
                    'submenuOrden' => $log->menu,
                    'submenu' => array(
                        'id' => $log->menu_id,
                        'nombre' => $log->menu
                    ),
                    'idSeccion' => $log->submenu_id,
                    'seccionOrden' => $log->submenu,
                    'seccion' => array(
                        'id' => $log->submenu_id,
                        'nombre' => $log->submenu
                    ),
                    'fechaIngreso' => date('d-m-Y H:i', strtotime($log->created_at)),
                    'fechaOrden' => date('U', strtotime($log->created_at))
                );
            }
        }
        
        $menus = MenuSistema::orderBy('posicion')->get();
        $listaMenus=array();
        
        if( $menus->count() ){
            $listaMenus[] = array('id' => 0, 'nombre' => 'TODOS', 'submenus' => array());
            foreach( $menus as $menu ){
                if($menu->tipo==1 && $menu->administrador<3){
                    $listaMenus[]=array(
                        'id' => $menu->id,
                        'nombre' => $menu->menu,
                        'submenus' => $menu->submenu()                        
                    );
                }
            }            
        }
                
        $datos = array(
            'datos' => $listaLogs,
            'menus' => $listaMenus,
            'date' => $mesAnterior
        );
        
        return Response::json($datos);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
       
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($sid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($sid)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {
        
    }
    

}