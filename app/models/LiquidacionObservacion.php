<?php
class LiquidacionObservacion extends Eloquent
{

    protected $table = 'liquidaciones_observaciones';

    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }

}