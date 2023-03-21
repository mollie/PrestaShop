const { defineConfig } = require('cypress')

module.exports = defineConfig({
  chromeWebSecurity: false,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 5,
  defaultCommandTimeout: 7000,
  projectId: 'xb89dr',
  retries: 1,
  videoUploadOnPasses: false,
  videoCompression: 15,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
    setupNodeEvents(on, config) {
      require("cypress-fail-fast/plugin")(on, config);
      return config;
    },
    excludeSpecPattern: ['index.php'],
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
  },
})
