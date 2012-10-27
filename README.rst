========
qaReview
========

This software is a tool to visualize metrics and metrics and source code. We
use this software for Code Reviews together with our customers.

Currently supports:

- PDepend
  - Calc (Metric calculator based on PDepend metrics)
- PHPMD
- oxPHPMD (private tool)
- PHPLint (private tool)
- PHPCPD
- UML
- Diff (if a path to an old version is provided)

Add more analyzers, if required. For those extend the ``Analyzer`` base class
or take a look at one of the example analyzers -- ``Analyzer\\Phpmd`` might be
a good starting point.

Requirements
============

- Ant >= 1.8.0
- PHP >= 5.3
  - mysqli
  - dom
- MySql >= 5.1

Installation
============

To install qaReview, clone the repository and run the following commands::

    git submodule init
    git submodule update
    ant -Dcommons.env=testing install
    ant install

After that configure your webserver properly, and you should be done. You might
want to adapt the database connection settings first, though -- see
`Configuration`_ for details.

.. warning:: This Software is not intended for public exposure. Making an
    installation open to the public would be a serious security issue. The
    "Calculator" analyzer allows to ``eval()`` request variables.

Lighttpd Example
----------------

Example configuration for the lighttpd webserver::

    $HTTP["host"] =~ "review$" {
        server.document-root = "/path/to/review/htdocs"
        server.error-handler-404 = "/index.php"
        url.rewrite-once = (
            "^(\/templates\/|\/styles\/|\/images\/|\/scripts\/).*" => "$0",
            "(?:\?(.*))?$" => "/index.php?$1"
        )
    }

Nginx Example
-------------

Example configuration for the nginx webserver::

    server {
        server_name _;
        listen 80;

        root /path/to/review/htdocs/;

        location ~ ^\/(templates|styles|images|scripts) {
        }

        location / {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        }
    }

Configuration
-------------

To configure your qaReview instance copy the ``src/config/config.ini.dist`` to
``src/config/config.ini`` and edit the settings there. If you change the
database connection settings you might also want to do this in your
``build.properties.local`` -- see `Development`_ for details.

Development
===========

To set the application to development mode create a file
``build.properties.local`` containing ``commons.env = development`` in the
project root (just beside the ``build.properties`` file). You can set other
local build environment variables there, too.

To run the tests for qaReview just execute ``ant`` in the project root (where
the ``build.xml`` file resides). The first run may take a while, but subsequent
runs will be a lot faster.

Usage
=====

To analyze source code, run::

    ./src/bin/analyze <path> [<oldPath>]

**Warning:** This throws away all current results and user annotations. You
might want to backup the database and the ``results/`` folder.

Wait until the command has finished and watch the results in the Web UI. It is
fairly common that some of the used tools cause errors for strange source code.
Fix them. :)

Disclaimer
==========

This software might change any time. We provide no guarantee that it still will
do the same things tomorrow. It has been developed as an internal tool and we
will continue to develop it likewise. It has been published, so that our
customer can use a snapshot of this tool to validate results of code reviews.

License
=======

This tool is under copyright of Qafoo GmbH. It has been licensed under AGPL v3.
See the ``src/LICENSE`` file distributed with qaReview for details.

TODO
====

* Make it possible to provide custom settings for the executed tools. Then also
  something like PHPCS would make sense to include.

* Make it possible to use the reports coming out of an existing build tool.
  Maybe implement something like ``import`` as an aequivalent to ``analyze``.
  This is not our primary use case, though.


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
