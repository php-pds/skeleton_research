# Research Report for pds/skeleton

## Introduction and Summary

There is at least some level of interest across the PHP ecosystem in how
packages are, or ought to be, organized. Here are a handful of pages regarding
that topic:

- https://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873
- https://github.com/bdunn313/php-project-skeleton
- https://github.com/johnkary/skeleton
- https://github.com/koriym/PHP.Skeleton
- https://github.com/zendframework/ZendSkeletonApplication
- https://groups.google.com/forum/#!msg/php-fig/qinsV68M-DI/AF2PUGT7HQAJ
- https://www.sitepoint.com/community/t/php-web-project-directory-structure/5240

Thus, the motivation is to examine existing PHP packages and find commonalities
between them, so as to discern what they each use for directory and file names.

This research started with the idea that there was a common need for the
following kinds of files in a package:

- a directory for PHP source code
- a directory for test files
- a directory for documentation files
- a directory for command-line executable files
- a directory for a web document root,
- a README file, and
- a LICENSE file.

On examining project packages, some additional common directories and files
were noted:

- a directory for configurations,
- a CONTRIBUTING file, and
- a CHANGELOG file.

After collection and analysis, the conclusion was that the following names
should be used in the standard:

```
bin/              # command-line files
config/           # configuration files
docs/             # documentation files
public/           # web files
src/              # source files
tests/            # test files
CHANGELOG(.*)     # change notes
CONTRIBUTING(.*)  # contribution guidelines
LICENSE(.*)       # licensing information
README(.*)        # read-me-first file
```

Of the collected packages, 73% of them appear to already comply with these
naming standards.

## Methodology

### Collection

1. Get the list of all packages on Packagist (cf. `results/list.json`).

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

Whereas the Packagist `list.json` file indicates 71392 packages total from 7642
vendors (each with at least 4 packages), there were some cases where downloading
didn't work:

- In step 2, sometimes the indicated package JSON file was not available.
- In step 4, sometimes the indicated "source" was a non-Github host.
- In step 5, sometimes the indicated "source" was not present on Github.

Taking into account unavailable package JSON files, non-Github hosts, and
missing source repositories, the sample ended up being 63746 packages. That is,
7646 packages were not retrievable during the collection process, for an
attrition rate of 10.7%.

For comparison, the `list.json` file indicates a nominal total of 119718
packages on Packagist (this includes abandoned and missing packages). Thus, the
collection process brought in 53% of the nominal total number of packages on
Packagist.

### Analysis

#### First Pass

This gives us an idea of what all the different top-level names are in the
downloaded packages, and how often they are used:

- From the collected top-level files and directories, list all the unique
  directory and file names, with a count of how many times they appear, in
  descending order.

- Ignore those that occur only once.

Results: [results/01.txt](./results/01.txt)

It turns out there are 2762 unique top-level directories, and 30120 unique top-
level file names.

#### Second Pass

This groups the directories and files by their presumed intent, rather than by
their name:

- Review the listings, and collate directories and files that appear to have
  similar purposes under a single category name, sorting them in descending order
  by category count.

- Ignore directories and files that occur only once.

- Ignore files that are obviously for tooling (e.g., dotfiles, `composer.*` and
  `phpunit.*`).

- Ignore files that are obviously code (e.g., `*.php` and `*.js`).

Results: [results/02.txt](./results/02.txt)

For this, the collations into categories were necessarily "by hand," as there
was no automated way to do so. The categories were for the initial expectations:

- Directories:
    - PHP source code files (69% of packages)
    - Test files for that source code (39% of packages)
    - Files intended to be publicly available via a web server (14% of packages)
    - Documentation files (11% of packages)
    - Files intended for execution at the command line (5% of packages)
- Files:
    - a "read me first" file (92% of packages)
    - a licensing or copyright file (60% of packages)

This pass netted some highly-used directories and files not in the original
expectation, all of which appeared more frequently than executable files:

- `config/` directory (9% of packages)
- `CHANGELOG.md` file (9% of packages)
- `CONTRIBUTING.md` file (7% of packages)

The frequency of the occurrence of these elements seems to mean they warrant
inclusion in the analysis.

#### Third Pass

This brings the unexpected directories and files into the groupings:

- Collate the new categories of directories and files into the previous
  groupings, and sort again by descending order.

- Ignore listings with less than 5% usage across all packages, as a lower bound
  to indicate a minimum level of occurrence.

Results: [results/03.txt](./results/03.txt)

The final set of categories was:

- Directories:
    - PHP source code files (69% of packages)
    - Test files for that source code (39% of packages)
    - Files intended to be publicly available via a web server (14% of packages)
    - Documentation files (11% of packages)
    - Configuration files (10% of packages)
    - Files intended for execution at the command line (5% of packages)
- Files:
    - a "read me first" file (92% of packages)
    - a licensing or copyright file (60% of packages)
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

## Conclusion

### Recommendation

Given the above collection and analysis, these names should be used for these
purposes:

```
bin/              # command-line files
config/           # configuration files
docs/             # documentation files
public/           # web files
src/              # source files
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

Of the 63746 packages in the sample, 47106 (73%) of them appear
compliant with the above recommendation.

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
