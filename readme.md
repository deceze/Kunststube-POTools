Kunststube\POTools
==================

A collection of tools for working with gettext PO files in PHP.
Currently mostly a proof of concept implementation, not yet full featured.


POString
--------

Models one entry in a PO file with all its possible attributes.


Catalog
-------

Groups and categorizes `POString` objects by category, domain and context. Merges attributes of strings occuring more than once. Can use a `POWriter` to output the catalog to disk.


POWriter
--------

Writes a `POString` instance to a stream in gettext PO format.