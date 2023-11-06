const { defineConfig } = require('cypress')

module.exports = defineConfig({
  chromeWebSecurity: false,
  experimentalMemoryManagement: true,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 0,
  defaultCommandTimeout: 9000,
  projectId: 'xb89dr',
  retries: 1,
  videoCompression: 8,
  viewportHeight: 1080,
  viewportWidth: 1920,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      on('after:spec', (spec, results) => {
        if (results && results.video) {
          // Do we have failures for any retry attempts?
          const failures = results.tests.some((test) =>
            test.attempts.some((attempt) => attempt.state === 'failed')
          )
          if (!failures) {
            // delete the video if the spec passed and no tests retried
            fs.unlinkSync(results.video)
          }
        }
      })
      require('./cypress/plugins/index.js')(on, config)
      require("cypress-fail-fast/plugin")(on, config);
      require('cypress-terminal-report/src/installLogsPrinter')(on);
      return config;
    },
    // setupNodeEvents(on, config) {
    //   require("cypress-fail-fast/plugin")(on, config);
    //   return config;
    // },
    experimentalMemoryManagement: true,
    excludeSpecPattern: ['index.php'],
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
  },
})
