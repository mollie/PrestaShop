# Header Stamp Integration tests

These tests run the Header Stamp on sample modules to validate its ability to fix license headers.

- `module-samples` folder contain module samples with issues in license headers
- `expected` folder contain the same module samples, with valid (fixed) license headers
- `runner` folder contains the test script and util classes
-  `workspace` is a git-ignored folder where files are copied then cleaned for the test needs

The test `runner/run.php` does the following, for each sample module:
- copy it into a dedicated `workspace` folder
- run the Header Stamp application on this folder
- compare 'expected' folder with the result
- delete `workspace` folder

You can simply run it like this:
```bash
$ php runner/run.php
```

If you want to add a module sample to the test suite, you need to:
- add the 'raw' version in `module-samples`
- add the 'cleaned' version in `expected`
- update the module list in `runner/run.php`
