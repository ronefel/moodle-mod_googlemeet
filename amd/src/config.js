define([], function() {
  window.requirejs.config({
    baseUrl: 'js',
    paths: {
      'gapi': [
        'https://apis.google.com/js/api',
        'https://accounts.google.com/gsi/client'
      ]
    },
    shim: {
      'gapi': {exports: 'gapi'},
    }
  });
});
