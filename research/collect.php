<?php
$collect = new Collect(__DIR__);
$collect(4);

class Collect
{
    protected $baseDir;
    protected $jsonDir;
    protected $topsDir;
    protected $vendors = [];

    public function __construct($dir)
    {
        $this->baseDir = "{$dir}/vendors";
        $this->jsonDir = "{$dir}/vendors/json";
        $this->topsDir = "{$dir}/vendors/tops";
    }

    public function __invoke($min)
    {
        $this->collectVendors();
        $this->collectPackages($min);
    }

    protected function collectVendors()
    {
        $text = file_get_contents("https://packagist.org/packages/list.json");
        file_put_contents("{$this->baseDir}/list.json", $text);

        $json = json_decode($text, true);
        foreach ($json['packageNames'] as $vendor_package) {
            list($vendor, $package) = explode('/', $vendor_package);
            $this->vendors[$vendor][] = $package;
        }
    }

    protected function collectPackages($min)
    {
        foreach ($this->vendors as $vendor => $packages) {
            if (count($packages) < $min) {
                continue;
            }
            foreach ($packages as $package) {
                $this->collectPackage($vendor, $package);
            }
        }
    }

    protected function collectPackage($vendor, $package)
    {
        $json = $this->collectPackageJson($vendor, $package);
        if ($json) {
            $this->collectPackageTops($vendor, $package, $json);
        }
    }

    protected function collectPackageJson($vendor, $package)
    {
        $dir = "{$this->jsonDir}/{$vendor}";
        if (! is_dir($dir)) {
            mkdir($dir);
        }

        $package .= '.json';
        $file = "{$dir}/{$package}";
        if (file_exists($file) && trim(file_get_contents($file))) {
            return;
        }

        if (file_exists($file) && ! trim(file_get_contents($file))) {
            unlink($file);
        }

        echo "{$vendor}/{$package} ... ";
        $text = file_get_contents("https://packagist.org/p/{$vendor}/{$package}");
        if (! $text) {
            echo "FAIL" . PHP_EOL;
            return;
        }

        file_put_contents($file, $text);
        echo "ok" . PHP_EOL;
        return json_decode($text, true);
    }

    protected function collectPackageTops($vendor, $package, $json)
    {
        $dir = "{$this->topsDir}/{$vendor}";
        if (! is_dir($dir)) {
            mkdir($dir);
        }

        $package .= '.txt';
        $file = "{$dir}/{$package}";
        if (file_exists($file) && trim(file_get_contents($file))) {
            return;
        }

        if (file_exists($file) && ! trim(file_get_contents($file))) {
            unlink($file);
        }

        echo "{$vendor}/{$package} ... ";

        $json = array_shift($json); // "packages"
        if (! $json) {
            echo "no 'packages' in json" . PHP_EOL;
            return;
        }

        $json = array_shift($json); // "vendor/package"
        if (! $json) {
            echo "no 'vendor/package' in json" . PHP_EOL;
            return;
        }

        $json = array_shift($json); // first branch
        if (! $json) {
            echo "no branches in json" . PHP_EOL;
            return;
        }

        if (isset($json['abandoned']) && $json['abandoned']) {
            echo "abandoned in json" . PHP_EOL;
            return;
        }

        $url = $json['source']['url'];
        $host = parse_url($url, PHP_URL_HOST);
        if ($host !== 'github.com') {
            echo "not github in json ({$host})" . PHP_EOL;
            return;
        }
        $html = file_get_contents($url);

        $doc = new DOMDocument();
        @$doc->loadHtml($html);
        $doc->normalizeDocument();
        $xpath = new DOMXpath($doc);

        $tops = $this->collectPackageTopsGithub($xpath);
        if (! $tops) {
            echo "no tops found" . PHP_EOL;
            return;
        }

        file_put_contents($file, implode("\n", $tops));
        echo "ok" . PHP_EOL;
    }

    // directories get a slash appended, files do not
    protected function collectPackageTopsGithub(DOMXpath $xpath)
    {
        $tops = [];
        $trs = $xpath->query("//*/table/tbody/tr[@class='js-navigation-item']");
        foreach ($trs as $tr) {
            $td = $tr->getElementsByTagName('td');
            $type = $td[0]->getElementsByTagName('svg')[0]->getAttribute('class');
            $slash = $type == 'octicon octicon-file-directory' ? '/' : '';
            $name = trim($td[1]->textContent);
            $tops[] = $name . $slash;
        }
        return $tops;
    }
}
