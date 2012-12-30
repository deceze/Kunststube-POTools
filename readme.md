Kunststube\POTools
==================

A collection of tools for working with gettext PO files in PHP.


Classes
-------

### `POString`

Models one entry in a PO file with all its possible attributes.


### `Catalog`

Groups and categorizes `POString` objects by category, domain and context. Merges attributes of strings occurring more than once. Can use a `POWriter` to output the catalog to disk.


### `POWriter`

Writes a `POString` instance to a stream in gettext PO format.


Scripts
-------

### `update`

Merges a directory structure of POT files into PO files.


Usage/workflow
--------------

The gettext workflow requires/expects that you have the gettext tools (<http://www.gnu.org/software/gettext/>) for your platform installed, including the `msginit`, `msgmerge` and `msgfmt` command line utilities. For the full manual, see http://www.gnu.org/software/gettext/manual/gettext.html.

On OS X, the easiest way to install these tools in through Homebrew (<http://mxcl.github.com/homebrew/>):

    $ brew install gettext
    
Most Linux package managers should make it similarly simple.

The Kunststube\POTools are designed for situations where the gettext `xgettext` utility does not work or is not sufficient, for example for templates whose syntax `xgettext` does not support. In these cases a custom parser/extractor can quickly be written, while the Kunststube\POTools can be used to handle the minutiae of the gettext file format. For the rest of the workflow the regular gettext utilities can be used.

The full workflow looks something like this:

1. Prepare strings in source code by wrapping them in gettext functions.
2. Extract strings from source using appropriate parser, create `POString` objects for each.
3. Add `POString` objects to `Catalog`.
4. Write `Catalog` to locale directory as source/master locale POT files.
   - 4b. First time only: create localization PO files using `msginit` utility.
5. Translate PO files.
6. Keep PO files in sync with updated POT files using `update` script.
7. Quality check and compile PO files to MO files using `msgfmt` utility.
8. Rinse, repeat.


### Creating master catalog POT files

```php
// Extraction of strings from source is not part of Kunststube\POTools,
// bring your own extractor specific to your needs.
$extractedStrings = my_string_extractor('template.php');

$catalog = new Kunststube\POTools\Catalog('My Project 1.0', 'en_US');

foreach ($extractedStrings as $extractedString) {
    $POString = new Kunststube\POTools\POString($extractedString);

    // set as many additional attributes as you can extract
    $POString->setDomain('errors');
    ...

    $catalog->add($POString);
}
    
$catalog->writeToDirectory('locale/en_US');
```
    
This process creates a directory structure which should look something like this:

    locale/
        en_US/
            ...
            LC_MESSAGES/
                messages.pot
                ...
            LC_MONETARY/
                messages.pot
                ...

### Creating and updating PO files

Initially, use the `msginit` utility to create PO files for all your target locales. When you subsequently change and update the source files, repeat the extraction process to recreate the master POT files, then use the `update` script to propagate those updates to each locale. Assuming a directory structure like this:

    locale/
        en_US/
            LC_MESSAGES/
                messages.pot
            LC_MONETARY/
                messages.pot
        de_DE/
            LC_MESSAGES/
                messages.po
            LC_MONETARY/
                messages.po
        fr_FR/
            LC_MESSAGES/
                messages.po
            LC_MONETARY/
                messages.po

Run the `update` script like this:

    update path/to/locale en_US
    
The first parameter (`path/to/locale`) is the path to the whole `locale` directory, the second parameter (`en_US`) the name of the directory containing the master POT files within the locale directory. The `update` script will iterate through all POT files in the master locale directory and merge the changes into identically named PO files in other locale directories.

It requires the `msgmerge` utility to be in the shell `$PATH`.