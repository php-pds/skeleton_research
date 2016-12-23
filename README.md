# Research Report for pds/skeleton

## Introduction and Summary

There is at least some level of interest across the PHP ecosystem in how
packages are, or ought to be, organized. Here are a handful of pages regarding
that topic:

- https://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873
- https://github.com/bdunn313/php-project-skeleton
- https://github.com/imarc/php-package
- https://github.com/johnkary/skeleton
- https://github.com/koriym/Koriym.PhpSkeleton
- https://github.com/thephpleague/skeleton
- https://github.com/zendframework/ZendSkeletonApplication
- https://groups.google.com/forum/#!msg/php-fig/qinsV68M-DI/AF2PUGT7HQAJ
- https://wiki.php.net/pear/rfc/pear2_standards#directory_structure
- https://www.sitepoint.com/community/t/php-web-project-directory-structure/5240

Thus, the motivation is to examine existing PHP packages and find commonalities
between them, so as to discern what they each use for directory and file names.

This research started with the idea that there was a common need for the
following kinds of files in a package:

- a directory for PHP source code,
- a directory for test files,
- a directory for documentation files,
- a directory for command-line executable files,
- a directory for files to be served on the web,
- a README file, and
- a LICENSE file.

On examining project packages, and after the public review period, some
additional common directories and files were noted:

- a directory for configuration files,
- a directory for resource files,
- a CONTRIBUTING file, and
- a CHANGELOG file.

After collection and analysis, the conclusion was that the following names
should be used in the standard:

```
bin/              # command-line files
config/           # configuration files
docs/             # documentation files
public/           # web files
src/              # PHP source files
resources/        # other resource files
tests/            # test files
CHANGELOG(.*)     # change notes
CONTRIBUTING(.*)  # contribution guidelines
LICENSE(.*)       # licensing information
README(.*)        # read-me-first file
```

Of the collected packages, 69% of them appear to already comply with these
naming standards.

## Methodology

### Collection

1. Get the list of all packages on Packagist (cf. [`results/list.json`](./results/list.json)).

2. Parse the list to find all vendors with at least 4 packages; having at least
   4 implies a minimum level of experience and practice with building and
   publishing packages.

3. For each of those, fetch the package JSON files from Packagist ...
   `<https://packagist.org/p/{$VENDOR}/{$PACKAGE}.json>` ... and retain them.

4. For each of the fetched package JSON files ...

    - skip the package if is marked as "abandoned";
    - otherwise, find its first "source" entry;
    - and skip the entry if it is not hosted at Github (this is to minimize
      tooling necessary to analyze repositories without downloading them).

5. For all of the fetched non-abandoned packages hosted at Github, scrape the
   Github page for the default branch, to retain the top-level files and
   directories in the repository.

Whereas the Packagist `list.json` file indicates 71746 packages total from 7682
vendors (each with at least 4 packages), there were some cases where downloading
didn't work:

- In step 2, sometimes the indicated package JSON file was not available.
- In step 4, sometimes the indicated "source" was a non-Github host.
- In step 5, sometimes the indicated "source" was not present on Github.

Taking into account unavailable package JSON files, non-Github hosts, and
missing source repositories, the sample ended up being 65617 packages. That is,
6129 packages were not retrievable during the collection process, for an
attrition rate of 8.5%.

For comparison, the `list.json` file indicates a nominal total of 120247
packages on Packagist (this includes abandoned and missing packages). Thus, the
collection process brought in 54.6% of the nominal total number of packages on
Packagist.

### Analysis

#### First Pass

This gives us an idea of what all the different top-level names are in the
downloaded packages, and how often they are used:

- From the collected top-level files and directories, list all the unique
  directory and file names, with a count of how many times they appear, in
  descending order.

Results:

- [results/01-dirs.txt](./results/01-dirs.txt)
- [results/01-files.txt](./results/01-files.txt)

It turns out there are 6082 unique top-level directories, and 30826 unique top-
level file names.

#### Second Pass

This groups the directories and files by their presumed intent, rather than by
their name:

- Review the listings, and group directories and files that appear to have
  similar purposes under a single category name, sorting them in descending order
  by category count.

- Ignore directories and files that occur only once.

- Ignore directories starting with a capital letter, on the presumption that
  they are PHP namespace directories.

Results:

- [results/02-dirs.txt](./results/02-dirs.txt)
- [results/02-files.txt](./results/02-files.txt)

For this, the collations into categories were necessarily "by hand," as there
was no automated way to do so. The categories were for the initial expectations:

- Directories:
    - PHP source code files (67% of packages)
    - Test files for that source code (38% of packages)
    - Files intended to be publicly available via a web server (13% of packages)
    - Documentation files (10% of packages)
    - Files intended for execution at the command line (5% of packages)
- Files:
    - a "read me first" file (90% of packages)
    - a licensing or copyright file (58% of packages)

On inspection of the results, this pass netted one highly-used directory not in
the original expectation:

- `config/` directory (8% of packages)

Looking past the files that are apparently tool-specific (`composer.json`,
`phpunit.xml`, etc.), this pass also netted some highly-used files not in the
original expectation:

- `CHANGELOG.md` file (9% of packages)
- `CONTRIBUTING.md` file (6% of packages)

The frequency of the occurrence of these elements would seem to warrant
inclusion in the analysis.

#### Third Pass

This brings the unexpected directories and files into the groupings:

- Collate the new categories of directories and files into the previous
  groupings, and sort again by descending order.

Results:

- [results/03-dirs.txt](./results/03-dirs.txt)
- [results/03-files.txt](./results/03-files.txt)

The resulting set of categories was:

- Directories:
    - PHP source code files (67% of packages)
    - Test files for that source code (38% of packages)
    - Files intended to be publicly available via a web server (13% of packages)
    - Configuration files (11% of packages)
    - Documentation files (10% of packages)
    - Files intended for execution at the command line (5% of packages)
- Files:
    - a "read me first" file (90% of packages)
    - a licensing or copyright file (58% of packages)
    - a file of change notes (13% of packages)
    - a "contribution guidelines" file (7% of packages)

#### Fourth Pass

Now that a set of categories is in place, pick an appropriate directory or file
name for each category. It seems reasonable that the name be the same as the
most-frequently occuring name within the category, resulting in:

```
src/
tests/
assets/
docs/
config/
bin/
README.md
LICENSE
CHANGELOG.md
CONTRIBUTING.md
```

However, some of these may not be appropriate:

- `assets` implies only static web assets, whereas there appears to be a
  need for other kinds of web files (e.g., an `index.php` file).

- Filename extensions, or the lack thereof, imply that a file must be in a
  particular format.

As such:

- Instead of `assets`, use a name that is slightly more generic; in this case,
  the 2nd-most-common name in the category, `public`.

- Instead of requiring or forbidding a filename extension, allow for any
  filename extension, or none at all.

#### Fifth Pass

This pass re-runs the analysis to incorporate feedback from the public review
period:

- Some reviewers pointed out that the `Resources/` directory, which appears as
  the 3rd-most common directory name, is not a PHP namespace directory (despite
  the fact that it starts with a capital letter).

- Other reviewers opined that `examples/` and `samples/` (and their variations)
  are not documentation per se, and do not properly belong in the `docs/`
  category.

Results:

- [results/05-dirs.txt](./results/05-dirs.txt)
- (No changes to the file listing)

That gave the following set of categories:

- Directories:
    - PHP source code files (67% of packages)
    - Test files for that source code (38% of packages)
    - Files intended to be publicly available via a web server (13% of packages)
    - Resource files (12% of packages)
    - Configuration files (11% of packages)
    - Documentation files (6% of packages)
    - Files intended for execution at the command line (5% of packages)
- Files:
    - a "read me first" file (90% of packages)
    - a licensing or copyright file (58% of packages)
    - a file of change notes (13% of packages)
    - a "contribution guidelines" file (7% of packages)

The most common name for a directory of resource files is `Resources/`. However,
for consistency with the other directory names, this report recommends the
lower-case form (which is the second-most frequent use).

## Conclusion

### Recommendation

Given the above collection and analysis, these names should be used for these
purposes:

```
bin/              # command-line files
config/           # configuration files
docs/             # documentation files
public/           # web files
src/              # PHP source code files
resources/        # other resource files
tests/            # test files
CHANGELOG(.*)     # change notes
CONTRIBUTING(.*)  # contribution guidelines
LICENSE(.*)       # licensing information
README(.*)        # read-me-first file
```

Since not all packages may need all these categories, they need not be required
to be present in a package. However, if a package *does* provide directories or
files of these categories, they should use the names listed.

### Current Compliance

Of the 65617 packages in the sample, 45191 (69%) of them appear compliant with
the above recommendation.

Results: [compliance.txt](./results/compliance.txt)

This does not mean that all the apparently compliant packages use all the
directores and all the files named in the conclusion. Rather, it means that
*when directories and files for the related purpose are present in the package*,
they use the names indicated above.

For example, a package is apparently compliant when a directory for executable
files is provided with the name `bin/`, and not `cli/` (or something else). If
no such directory is provided under any recognizable name, the package is still
apparently compliant, since not all packages may provide all the kinds of
directories and files named above.
