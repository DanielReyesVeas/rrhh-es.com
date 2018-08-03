'use strict';

/**
 * @ngdoc service
 * @name angularjsApp.generar-reporte
 * @description
 * # generar-reporte
 * Factory in the angularjsApp.
 */
angular.module('angularjsApp')
  .factory('generarReporte', function (constantes, $resource) {
        return {
            datos: function () {
                return $resource(constantes.URL + 'generar-reportes/:sid',
                    {sid : '@sid'},
                    {   
                        update : { 'method': 'PUT' },
                        delete : { 'method': 'DELETE' },
                        create : { 'method': 'POST' }
                    }
                );
            },
            generar : function(){
              return $resource(constantes.URL + 'generar-reportes/obtener/generar',
                  {},
                  { post : { 'method': 'POST' } }
              );
            }
        };
  });