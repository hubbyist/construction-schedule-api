USAGE
-----

Use `cd` command to navigate into the project root folder where index.php resides.

Then start PHP built-in server on port 8000 to evaluate API usage.
```
php -S localhost:8000 -t $PWD
```
To see API navigate to `http://localhost:8000/constructionStages`

TESTING
-------

You can run tests using PHP built-in server on port 4000.
IMPORTANT: Run test server in testing folder to prevent evaluation database erasure.
NOTE: Tests will not run on evaluation port.

```
php -S localhost:4000 -t $PWD/testing
```
To run tests  issue following command.

```
php $PWD/testing/tester.php
```

DOCUMENTATION
-------------

Documentation is in the documentation folder.

Documentation can be recreated using the command below.

```
php $PWD/documentor/documentor.php
```

--------------------------------------------------------------------------------

author : Mehmet Durgel <md@legrud.net>
