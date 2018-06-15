<?php

Validator::extend('rut', function($attribute, $value, $parameters)
{
    if( !$parameters[0] ){
        return Funciones::comprobarRut($value);    
    }else return true;
});  