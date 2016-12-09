<?php
$json = json_decode(file_get_contents("./vendors/list.json"), true);
$vendors = [];
foreach ($json['packageNames'] as $vendor_package) {
    list($vendor, $package) = explode('/', $vendor_package);
    $vendors[$vendor][$package] = true;
}

$counts = [];

foreach ($vendors as $vendor => $packages) {
    $count = count($packages);
    $counts[$count][] = $vendor;
}
ksort($counts);

foreach ($counts as $count => $vendors) {
    echo count($vendors) . "\tvendors have\t{$count}\tpackages." . PHP_EOL;
}

$min = 4;
$package_total = 0;
$vendor_count = 0;
for ($i = 600; $i >= $min; $i --) {
    if (isset($counts[$i])) {
        foreach ($counts[$i] as $vendor) {
            $vendor_count ++;
            echo "{$vendor}\thas\t{$i}\tpackages" . PHP_EOL;
            $package_total += $i;
        }
    }
}

echo "$package_total packages total from $vendor_count vendors with $min+ packages." . PHP_EOL;
