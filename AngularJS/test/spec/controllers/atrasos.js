'use strict';

describe('Controller: AtrasosCtrl', function () {

  // load the controller's module
  beforeEach(module('angularjsApp'));

  var AtrasosCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    AtrasosCtrl = $controller('AtrasosCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(AtrasosCtrl.awesomeThings.length).toBe(3);
  });
});
