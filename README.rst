qaReview
========

Helper tool to do code reviews. This software is *just a hack* to visualizing
some metrics.

Currently supports:

- PDepend
- PHPMD
- oxPHPMD (private tool)
- PHPLint (private tool)
- PHPCPD
- UML
- Diff (if a path to an old version is provided)

Add more analyzers, if required. For those extend the ``Analyzer`` base class
or take a look at one of the example analyzers -- ``Analyzer\\Phpmd`` might be
a good starting point.

Disclaimer
----------

This software might change any time. We provide no guarantee that it still will
do the same things tomorrow. It has been developed as an internal tool and we
will continue to develop it likewise. It has been published, so that our
customer can use a snapshot of this tool to validate results of code reviews.

License
-------

This tool is under copyright of Qafoo GmbH. There is no license. This tool is
**not Open Source**.

The reason for this simply is, that we currently do not have the resources to
maintain such a tool in a way that the general public is able to use it
sensibly. Everything else would not line up with our own requirements for
quality.

We probably will not complain if you play around with it. Please keep the
copyright notices intact, though.

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
