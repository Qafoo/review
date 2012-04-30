qaReview
========

Helper tool to do code reviews.

Currently supports:

- PDepend
- PHPMD
- Diff (if a path to an old version is provided)

Add more analyzers, if required. For thos extend the ``Analyzer`` base class or
take a look at one of the example analyzers -- ``Analyzer\\Phpmd`` is a good
starting point.

Installation
------------

Configure the database connection in ``src/config/config.ini``. Configure your
webserver, so that the Web UI is accessible. An example configuration for
Lighttpd can be found in ``doc/lighttpd.conf``.

Usage
-----

./src/bin/analyze <path> [<oldPath>]

Wait and watch the results in the Web UI.


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
