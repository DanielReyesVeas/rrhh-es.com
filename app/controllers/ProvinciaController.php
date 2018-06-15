<?php
class ProvinciaController extends BaseController {
    public function ajaxSelectListaProvincias($region) {
        $html="";
        $provincias = Provincia::listaProvincias($region);
        if(count($provincias)){
            $html.="<option value='0'>[Seleccione]</option>";
            foreach($provincias as $id=>$provincia)
            {
                $html.="<option value='".$id."'>".$provincia."</option>";
            }
        }
        return $html;
    }
}
