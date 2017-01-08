<?php
$vendors = scandir(__DIR__ . '/vendors/tops');
foreach ($vendors as $vendor) {

    if (substr($vendor, 0, 1) == '.') {
        continue;
    }

    $packageFiles = scandir(__DIR__ . "/vendors/tops/$vendor");
    foreach ($packageFiles as $packageFile) {

        if (substr($packageFile, -4) != '.txt') {
            continue;
        }

        $lines = file(__DIR__ . "/vendors/tops/{$vendor}/{$packageFile}");
        if (! $lines) {
            continue;
        }

        if (
            checkBin($lines)
            && checkConfig($lines)
            && checkDocs($lines)
            && checkPublic($lines)
            && checkSrc($lines)
            && checkResources($lines)
            && checkTests($lines)
            && checkChangelog($lines)
            && checkContributing($lines)
            && checkLicense($lines)
            && checkReadme($lines)
        ) {
            $package = substr($packageFile, 0, -4);
            echo "{$vendor}/{$package}" . PHP_EOL;
        }
    }
}

function checkDir($lines, $pass, array $fail)
{
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line == $pass) {
            continue;
        }
        if (in_array($line, $fail)) {
            return false;
        }
    }

    return true;
}

function checkFile($lines, $pass, array $fail)
{
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match("/^{$pass}(\.[a-z]+)?$/", $line)) {
            continue;
        }
        foreach ($fail as $regex) {
            if (preg_match($regex, $line)) {
                return false;
            }
        }
    }

    return true;
}

function checkChangelog($lines)
{
    return checkFile($lines, 'CHANGELOG', [
        '/^.*CHANGLOG.*$/i',
        '/^.*CAHNGELOG.*$/i',
        '/^WHATSNEW(\.[a-z]+)?$/i',
        '/^RELEASE((_|-)?NOTES)?(\.[a-z]+)?$/i',
        '/^RELEASES(\.[a-z]+)?$/i',
        '/^CHANGES(\.[a-z]+)?$/i',
        '/^CHANGE(\.[a-z]+)?$/i',
        '/^HISTORY(\.[a-z]+)?$/i',
    ]);
}

function checkContributing($lines)
{
    return checkFile($lines, 'CONTRIBUTING', [
        '/^DEVELOPMENT(\.[a-z]+)?$/i',
        '/^README\.CONTRIBUTING(\.[a-z]+)?$/i',
        '/^DEVELOPMENT_README(\.[a-z]+)?$/i',
        '/^CONTRIBUTE(\.[a-z]+)?$/i',
        '/^HACKING(\.[a-z]+)?$/i',
    ]);
}

function checkLicense($lines)
{
    return checkFile($lines, 'LICENSE', [
        '/^.*EULA.*$/i',
        '/^.*(GPL|BSD).*$/i',
        '/^([A-Z-]+)?LI(N)?(S|C)(E|A)N(S|C)(E|A)(_[A-Z_]+)?(\.[a-z]+)?$/i',
        '/^COPY(I)?NG(\.[a-z]+)?$/i',
        '/^COPYRIGHT(\.[a-z]+)?$/i',
    ]);
}

function checkReadme($lines)
{
    return checkFile($lines, 'README', [
        '/^USAGE(\.[a-z]+)?$/i',
        '/^SUMMARY(\.[a-z]+)?$/i',
        '/^DESCRIPTION(\.[a-z]+)?$/i',
        '/^IMPORTANT(\.[a-z]+)?$/i',
        '/^NOTICE(\.[a-z]+)?$/i',
        '/^GETTING(_|-)STARTED(\.[a-z]+)?$/i',
    ]);
}

function checkBin($lines)
{
    return checkDir($lines, 'bin/', [
        'cli/',
        'scripts/',
        'console/',
        'shell/',
        'script/',
    ]);
}

function checkConfig($lines)
{
    return checkDir($lines, 'config/', [
        'etc/',
        'settings/',
        'configuration/',
        'configs/',
        '_config/',
        'conf/',
    ]);
}

function checkDocs($lines)
{
    return checkDir($lines, 'docs/', [
        'manual/',
        'documentation/',
        'usage/',
        'doc/',
        'guide/',
        'phpdoc/',
        // extra items from @stof
        'apidocs/',
        'apidoc/',
        'api-reference/',
        'user_guide/',
        'manuals/',
        'phpdocs/',
    ]);
}

function checkPublic($lines)
{
    return checkDir($lines, 'public/', [
        'assets/',
        'static/',
        'html/',
        'httpdocs/',
        'media/',
        'docroot/',
        'css/',
        'fonts/',
        'styles/',
        'style/',
        'js/',
        'javascript/',
        'images/',
        'site/',
        'mysite/',
        'img/',
        'web/',
        'pub/',
        'webroot/',
        'www/',
        'htdocs/',
        'asset/',
        'public_html/',
        'publish/',
        'pages/',
        // extra items from @stof
        'javascripts/',
        'icons/',
        'imgs/',
        'wwwroot/',
        'font/',
    ]);
}

function checkSrc($lines)
{
    return checkDir($lines, 'src/', [
        'exception/',
        'exceptions/',
        'src-files/',
        'traits/',
        'interfaces/',
        'common/',
        'sources/',
        'php/',
        'inc/',
        'libraries/',
        'autoloads/',
        'autoload/',
        'source/',
        'includes/',
        'include/',
        'lib/',
        'libs/',
        'library/',
        'code/',
        'classes/',
        'func/',
        // extra items from @stof
        'src-dev/',
    ]);
}

function checkTests($lines)
{
    return checkDir($lines, 'tests/', [
        'test/',
        'unit-tests/',
        'phpunit/',
        'testing/',
        // extra items from @stof
        'unittest/',
        'unit_tests/',
        'unit_test/',
        'phpunit-tests/',
    ]);
}

function checkResources($lines)
{
    return checkDir($lines, 'resources/', [
        'Resources/',
        'res/',
        'resource/',
        'Resource/',
        'ressources/',
        'Ressources/',
    ]);
}
