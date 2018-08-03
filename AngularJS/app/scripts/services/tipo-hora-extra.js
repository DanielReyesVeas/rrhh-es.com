'use strict';

/**
 * @ngdoc service
 * @name angularjsApp.tipoHoraExtra
 * @description
 * # tipoHoraExtra
 * Factory in the angularjsApp.
 */
angular.module('angularjsApp')
  .factory('tipoHoraExtra', function (constantes, $resource) {
        return {
            datos: function () {
                return $resource(constantes.URL + 'tipos-hora-extra/:sid',
                    {sid : '@sid'},
                    {   
                        update : { 'method': 'PUT' },
                        delete : { 'method': 'DELETE' },
                        create : { 'method': 'POST' }
                    }
                );
            },
            cuenta : function(){
              return $resource(constantes.URL + 'tipos-hora-extra/cuentas/obtener/:sid');
            },
            centroCosto : function(){
              return $resource(constantes.URL + 'tipos-hora-extra/centro-costo/obtener/:sid');
            },
            updateCuenta : function(){
              return $resource(constantes.URL + 'tipos-hora-extra/cuentas/actualizar',
                  {},
                  { post : { 'method': 'POST' } }
              );
            },
            updateCuentaCentrosCosto : function(){
              return $resource(constantes.URL + 'tipos-hora-extra/cuentas-centros-costos/actualizar',
                {},
                { post : { 'method': 'POST' } }
              );
            },
            updateCuentaMasivo : function(){
              return $resource(constantes.URL + 'tipos-hora-extra/cuentas-masivo/actualizar',
                {},
                { post : { 'method': 'POST' } }
              );
            }
        };
  });