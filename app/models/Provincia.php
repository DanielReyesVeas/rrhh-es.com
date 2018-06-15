<?php

class Provincia extends \Eloquent {
	protected  $table = "provincias";
    protected $connection = "principal";

    public function region(){
		return $this->belongsTo('Region', 'region_id');
	}
}