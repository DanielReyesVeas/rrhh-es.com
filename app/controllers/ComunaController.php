<?php
class ComunaController extends BaseController {
    public function buscador_comunas(){
        $termino = Input::get('termino');
        $comunas = Comuna::where('comuna', 'LIKE', '%'.$termino.'%')->orderBy('comuna', 'ASC')->get();
        $lista=array();
        if( $comunas->count() ){
            foreach( $comunas as $item ){
                $lista[]=array(
                    'id' => $item->id,
                    'nombre' => $item->localidad()
                );
            }
        }
        return Response::json($lista);
    }
    
    public function ajaxSelectListaComunas($provincia) {
        $html="";
        $comunas = Comuna::listaComunas($provincia);
        if(count($comunas)){
            $html.="<option value='0'>[Seleccione]</option>";
            foreach($comunas as $id=>$comuna)
            {
                $html.="<option value='".$id."'>".$comuna."</option>";
            }
        }
        return $html;
    }
}
