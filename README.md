Pelzini
=======

Pelzini is a code documentation tool. It is planned to support many different programming languages.
It stores all of its documentation in a database, and the documentation is viewed dynamically. This is
so that cool features like search can be created. Search was the main reason for the creation of Pelzini.

Features
--------

Pelzini currently supports all aspects of PHP, with the documentation being stored in a MySQL database.
Pelzini has a modular design, so other laoguages and output engines will be supported in the future.
Pelzini supports code search. You can search for class names, interface names and function names. The search
feature is planned to be upgraded in the future.

Installation
------------

1. Extract the archive somewhere.
2. Set up your web server to be able to view the "viewer" directory.
3. Configure the viewer by editing the file `viewer/config.viewer.php`.
4. Create a config file for your project. You can use the Pelzini one (pelzini.conf.php) as a guide.
5. Run the code documenter:
```shell
php src/processor/main.php path/to/your/config.conf.php
```

Running tests
-------------

You can run tests using PHPUnit:
```shell
phpunit
```

Notes
-----

 * It is possible for the processor directory and the viewer directory to reside in different areas of the filesystem if required. These two directories can even reside on different computers if nessasary. Compatibility between the viewer and the processor is only guarenteed for the same version, but may work for similar versions.
 
The name
--------
Pelzini is loosely named after one of the sub-species of the [Dorcas gazelle](http://en.wikipedia.org/wiki/Dorcas_gazelle).
