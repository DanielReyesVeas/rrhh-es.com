'use strict';

describe('Service: atraso', function () {

  // load the service's module
  beforeEach(module('angularjsApp'));

  // instantiate service
  var atraso;
  beforeEach(inject(function (_atraso_) {
    atraso = _atraso_;
  }));

  it('should do something', function () {
    expect(!!atraso).toBe(true);
  });

});
