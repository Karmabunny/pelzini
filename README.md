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

2. Set up your web server to be avle to view the "viewer" directory.

3. Create a config file for you project. You can use the Pelzini one (pelzini.conf.php) as a guide.

4. Run the code documenter:
   # php src/processor/main.php --config path/to/your/config.conf.php


Notes
-----

 * It is possible for the processor directory and the viewer directory to reside in different areas of the filesystem if required
   These two directories can even reside on different computers if nessasary. Compatibility between the viewer and the processor
   is only guarenteed for the same version, but may work for similar versions.
