<?php
$analyze = new Analyze(__DIR__);

if (! isset($argv[1])) {
    echo "Please pass an analysis mode." . PHP_EOL;
    exit();
}

$analyze($argv[1]);

class Analyze
{
    const SRC_DIR = 'source code directory';
    const CONFIG_DIR = 'configuration directory';
    const BIN_DIR = 'executables directory';
    const PUBLIC_DIR = 'web directory';
    const DOCS_DIR = 'documentation directory';
    const TESTS_DIR = 'tests directory';
    const OTHER_DIR = 'other directories';
    const README_FILE = 'read-me-first file';
    const LICENSE_FILE = 'license or copyright file';
    const CONTRIBUTING_FILE = 'contribution guidelines file';
    const CHANGELOG_FILE = 'changes file';
    const OTHER_FILE = 'other files';
    const RES_DIR = 'resources directory';

    protected $baseDir;
    protected $topsDir;
    protected $vendors = [];
    protected $packageCount = 0;

    public function __construct($dir)
    {
        $this->baseDir = $dir;
        $this->topsDir = "{$dir}/vendors/tops";
    }

    public function __invoke($mode)
    {
        $analyze = "analyze{$mode}";
        if (! method_exists($this, $analyze)) {
            echo "No such analysis mode: {$mode}" . PHP_EOL;
            return;
        }

        $this->loadVendors();
        $this->$analyze();
    }

    protected function loadVendors()
    {
        foreach (scandir($this->topsDir) as $vendor) {
            $this->loadVendor($vendor);
        }
    }

    protected function loadVendor($vendor)
    {
        if (substr($vendor, 0, 1) == '.') {
            return;
        }
        $this->loadVendorPackages($vendor);
    }

    protected function loadVendorPackages($vendor)
    {
        $vendorDir = "{$this->topsDir}/{$vendor}";
        foreach (scandir($vendorDir) as $packageFile) {
            $this->loadVendorPackage($vendor, $packageFile);
        }
    }

    protected function loadVendorPackage($vendor, $packageFile)
    {
        if (substr($packageFile, -4) != '.txt') {
            return;
        }

        $lines = file("{$this->topsDir}/{$vendor}/{$packageFile}");
        $package = substr($packageFile, 0, -4);
        $this->loadVendorPackageLines($vendor, $package, $lines);
        $this->packageCount ++;
    }

    protected function loadVendorPackageLines($vendor, $package, $lines)
    {
        foreach ($lines as $line) {
            $this->loadVendorPackageLine($vendor, $package, $line);
        }
    }

    protected function loadVendorPackageLine($vendor, $package, $line)
    {
        $line = trim($line);
        $expl = explode("/", $line);
        if (count($expl) > 2) {
            // convert "foo/bar/baz/" to "foo/"
            $line = array_shift($expl) . "/";
        }
        $this->vendors[$vendor][$package][] = $line;
    }

    protected function analyzeDump()
    {
        echo count($this->vendors) . " vendors, {$this->packageCount} packages." . PHP_EOL;
        var_export($this->vendors);
    }

    protected function analyzeDirNames()
    {
        $dirNames = $this->fetchDirNames();
        echo count($this->vendors) . " vendors, "
            . "{$this->packageCount} packages, "
            . count($dirNames) . " unique directory names"
            . PHP_EOL;

        foreach ($dirNames as $name => $count) {
            echo "$name\t$count" . PHP_EOL;
        }
    }

    protected function analyzeFileNames()
    {
        $fileNames = $this->fetchFileNames();
        echo count($this->vendors) . " vendors, "
            . "{$this->packageCount} packages, "
            . count($fileNames) . " unique file names"
            . PHP_EOL;

        foreach ($fileNames as $name => $count) {
            echo "$name\t\t$count" . PHP_EOL;
        }
    }

    protected function analyzeDirGroups()
    {
        $dirNames = $this->fetchDirNames();
        $dirGroups = $this->groupDirNames($dirNames);

        echo count($this->vendors) . " vendors, "
            . "{$this->packageCount} packages"
            . PHP_EOL;

        foreach ($dirGroups as $group => $names) {
            $sum = array_sum($names);

            if ($group == self::OTHER_DIR) {
                $pct = '';
            } else {
                $pct = round(($sum / $this->packageCount) * 100, 1) . '%';
            }

            echo "{$group}\t\t{$sum}\t$pct" . PHP_EOL;
            foreach ($names as $name => $count) {
                $pct = round(($count / $this->packageCount) *100, 3) . '%';
                echo "\t{$name}\t{$count}\t{$pct}" . PHP_EOL;
            }
        }
    }

    protected function analyzeFileGroups()
    {
        $fileNames = $this->fetchFileNames();
        $fileGroups = $this->groupFileNames($fileNames);

        echo count($this->vendors) . " vendors, "
            . "{$this->packageCount} packages"
            . PHP_EOL;

        foreach ($fileGroups as $group => $names) {
            $sum = array_sum($names);

            if ($group == self::OTHER_FILE) {
                $pct = '';
            } else {
                $pct = round(($sum / $this->packageCount) * 100, 1) . '%';
            }

            echo "{$group}\t\t{$sum}\t{$pct}" . PHP_EOL;
            foreach ($names as $name => $count) {
                $pct = round(($count / $this->packageCount) *100, 3) . '%';
                echo "\t{$name}\t{$count}\t{$pct}" . PHP_EOL;
            }
        }
    }

    protected function fetchDirNames()
    {
        $dirNames = [];
        foreach ($this->vendors as $vendor => $packages) {
            foreach ($packages as $package => $names) {
                foreach ($names as $name) {
                    // ignore non-directories
                    if (substr($name, -1) != '/') {
                        continue;
                    }
                    // count the dir name
                    if (! isset($dirNames[$name])) {
                        $dirNames[$name] = 0;
                    }
                    $dirNames[$name] ++;
                }
            }
        }
        arsort($dirNames);
        return $dirNames;
    }

    protected function groupDirNames($dirNames)
    {
        $groups = [];
        $other = [];

        foreach ($dirNames as $dirName => $count) {
            $group = $this->groupDirName($dirName, $count);
            if (! $group) {
                continue;
            }
            if ($group == self::OTHER_DIR) {
                $other[$dirName] = $count;
            } else {
                $groups[$group][$dirName] = $count;
            }
        }

        // sort ...
        uasort($groups, [$this, 'reverseSortGroups']);

        // ... and make sure "other" are last
        $groups[self::OTHER_DIR] = $other;

        return $groups;
    }

    protected function groupDirName($dirName, $count)
    {
        if ($count == 1) {
            return false;
        }

        if (isset($this->dirGroups[$dirName])) {
            return $this->dirGroups[$dirName];
        }

        $char = substr($dirName, 0, 1);
        if ($char === strtoupper($char)) {
            return false;
        }

        return self::OTHER_DIR;
    }

    protected function fetchFileNames()
    {
        $fileNames = [];
        foreach ($this->vendors as $vendor => $packages) {
            foreach ($packages as $package => $names) {
                foreach ($names as $name) {
                    // ignore directories
                    if (substr($name, -1) == '/') {
                        continue;
                    }
                    // count the file name
                    if (! isset($fileNames[$name])) {
                        $fileNames[$name] = 0;
                    }
                    $fileNames[$name] ++;
                }
            }
        }
        arsort($fileNames);
        return $fileNames;
    }

    protected function groupFileNames($fileNames)
    {
        $groups = [];
        $other = [];

        foreach ($fileNames as $fileName => $count) {
            $group = $this->groupFileName($fileName, $count);
            if (! $group) {
                continue;
            }
            if ($group == self::OTHER_FILE) {
                $other[$fileName] = $count;
            } else {
                $groups[$group][$fileName] = $count;
            }
        }

        // sort ...
        uasort($groups, [$this, 'reverseSortGroups']);

        // ... and make sure "other" are last
        $groups[self::OTHER_FILE] = $other;

        return $groups;

    }

    protected function groupFileName($fileName, $count)
    {
        if ($count == 1) {
            return false;
        }
        foreach ($this->fileGroups as $regex => $group) {
            if (preg_match($regex, $fileName)) {
                return $group;
            }
        }
        return self::OTHER_FILE;
    }

    public function reverseSortGroups($groupA, $groupB)
    {
        $suma = array_sum($groupA);
        $sumb = array_sum($groupB);
        if ($suma == $sumb) {
            return 0;
        }
        return ($suma > $sumb) ? -1 : 1;
    }

    protected $dirGroups = [
        'bin/' => self::BIN_DIR,
        'cli/' => self::BIN_DIR,
        'scripts/' => self::BIN_DIR,
        'console/' => self::BIN_DIR,
        'shell/' => self::BIN_DIR,
        'script/' => self::BIN_DIR,

        'docs/' => self::DOCS_DIR,
        'manual/' => self::DOCS_DIR,
        'documentation/' => self::DOCS_DIR,
        'usage/' => self::DOCS_DIR,
        'doc/' => self::DOCS_DIR,
        'guide/' => self::DOCS_DIR,
        'phpdoc/' => self::DOCS_DIR,

        'src/' => self::SRC_DIR,
        'exception/' => self::SRC_DIR,
        'exceptions/' => self::SRC_DIR,
        'src-files/' => self::SRC_DIR,
        'traits/' => self::SRC_DIR,
        'interfaces/' => self::SRC_DIR,
        'common/' => self::SRC_DIR,
        'sources/' => self::SRC_DIR,
        'php/' => self::SRC_DIR,
        'inc/' => self::SRC_DIR,
        'libraries/' => self::SRC_DIR,
        'autoloads/' => self::SRC_DIR,
        'autoload/' => self::SRC_DIR,
        'source/' => self::SRC_DIR,
        'includes/' => self::SRC_DIR,
        'include/' => self::SRC_DIR,
        'lib/' => self::SRC_DIR,
        'libs/' => self::SRC_DIR,
        'library/' => self::SRC_DIR,
        'code/' => self::SRC_DIR,
        'classes/' => self::SRC_DIR,
        'func/' => self::SRC_DIR,

        'tests/' => self::TESTS_DIR,
        'test/' => self::TESTS_DIR,
        'unit-tests/' => self::TESTS_DIR,
        'phpunit/' => self::TESTS_DIR,
        'testing/' => self::TESTS_DIR,

        'assets/' => self::PUBLIC_DIR,
        'static/' => self::PUBLIC_DIR,
        'html/' => self::PUBLIC_DIR,
        'httpdocs/' => self::PUBLIC_DIR,
        'public/' => self::PUBLIC_DIR,
        'media/' => self::PUBLIC_DIR,
        'docroot/' => self::PUBLIC_DIR,
        'css/' => self::PUBLIC_DIR,
        'fonts/' => self::PUBLIC_DIR,
        'styles/' => self::PUBLIC_DIR,
        'style/' => self::PUBLIC_DIR,
        'js/' => self::PUBLIC_DIR,
        'javascript/' => self::PUBLIC_DIR,
        'images/' => self::PUBLIC_DIR,
        'site/' => self::PUBLIC_DIR,
        'mysite/' => self::PUBLIC_DIR,
        'img/' => self::PUBLIC_DIR,
        'web/' => self::PUBLIC_DIR,
        'pub/' => self::PUBLIC_DIR,
        'webroot/' => self::PUBLIC_DIR,
        'www/' => self::PUBLIC_DIR,
        'htdocs/' => self::PUBLIC_DIR,
        'asset/' => self::PUBLIC_DIR,
        'public_html/' => self::PUBLIC_DIR,
        'publish/' => self::PUBLIC_DIR,
        'pages/' => self::PUBLIC_DIR,

        'config/'=> self::CONFIG_DIR,
        'etc/'=> self::CONFIG_DIR,
        'settings/'=> self::CONFIG_DIR,
        'configuration/'=> self::CONFIG_DIR,
        'configs/'=> self::CONFIG_DIR,
        '_config/'=> self::CONFIG_DIR,
        'conf/'=> self::CONFIG_DIR,

        'Resources/' => self::RES_DIR,
        'resources/' => self::RES_DIR,
        'res/' => self::RES_DIR,
        'resource/' => self::RES_DIR,
        'Resource/' => self::RES_DIR,
        'ressources/' => self::RES_DIR,
        'Ressources/' => self::RES_DIR,

        // extra items noted by Christophe Coevoet from the
        // "all of packagist" list
        'javascripts/' => self::PUBLIC_DIR,
        'icons/' => self::PUBLIC_DIR,
        'imgs/' => self::PUBLIC_DIR,
        'wwwroot/' => self::PUBLIC_DIR,
        'font/' => self::PUBLIC_DIR,

        'src-dev/' => self::SRC_DIR,

        'apidocs/' => self::DOCS_DIR,
        'apidoc/' => self::DOCS_DIR,
        'api-reference/' => self::DOCS_DIR,
        'user_guide/' => self::DOCS_DIR,
        'manuals/' => self::DOCS_DIR,
        'phpdocs/' => self::DOCS_DIR,

        'unittest/' => self::TESTS_DIR,
        'unit_tests/' => self::TESTS_DIR,
        'unit_test/' => self::TESTS_DIR,
        'phpunit-tests/' => self::TESTS_DIR,
    ];

    protected $fileGroups = [

        '/^.*README.*$/i' => self::README_FILE,
        '/^USAGE(\.[a-z]+)?$/i' => self::README_FILE,
        '/^SUMMARY(\.[a-z]+)?$/i' => self::README_FILE,
        '/^DESCRIPTION(\.[a-z]+)?$/i' => self::README_FILE,
        '/^IMPORTANT(\.[a-z]+)?$/i' => self::README_FILE,
        '/^NOTICE(\.[a-z]+)?$/i' => self::README_FILE,
        '/^GETTING(_|-)STARTED(\.[a-z]+)?$/i' => self::README_FILE,

        '/^.*LICENSE.*$/i' => self::LICENSE_FILE,
        '/^.*EULA.*$/i' => self::LICENSE_FILE,
        '/^.*(GPL|BSD).*$/i' => self::LICENSE_FILE,
        '/^([A-Z-]+)?LI(N)?(S|C)(E|A)N(S|C)(E|A)(_[A-Z_]+)?(\.[a-z]+)?$/i' => self::LICENSE_FILE,
        '/^COPY(I)?NG(\.[a-z]+)?$/i' => self::LICENSE_FILE,
        '/^COPYRIGHT(\.[a-z]+)?$/i' => self::LICENSE_FILE,

        '/^CHANGELOG.*$/i' => self::CHANGELOG_FILE,
        '/^CHANGLOG.*$/i' => self::CHANGELOG_FILE,
        '/^CAHNGELOG.*$/i' => self::CHANGELOG_FILE,
        '/^WHATSNEW(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,
        '/^RELEASE((_|-)?NOTES)?(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,
        '/^RELEASES(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,
        '/^CHANGES(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,
        '/^CHANGE(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,
        '/^HISTORY(\.[a-z]+)?$/i' => self::CHANGELOG_FILE,

        '/^DEVELOPMENT(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
        '/^CONTRIBUTING(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
        '/^README\.CONTRIBUTING(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
        '/^DEVELOPMENT_README(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
        '/^CONTRIBUTE(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
        '/^HACKING(\.[a-z]+)?$/i' => self::CONTRIBUTING_FILE,
    ];
}
