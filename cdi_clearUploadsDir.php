<?php 

// $rootDir = stripos($_ENV['environment'], 'dev')  ? '../' : '/';
$rootDir = stripos($_ENV['environment'], 'dev')  ? './' : '/';
$rootDir = './';
$ds = DIRECTORY_SEPARATOR;

$d = dir($rootDir . "uploads");
echo "<br/>Handle: " . $d->handle . "\n";
echo "<br/>Path: " . $d->path . "\n";
echo "<br/>Delete Files in Directory";
$path = $d->path;
while (false !== ($entry = $d->read())) {
    echo "<br/>" . $path .  $ds . $entry ."\n";
    
    $isFile = is_file($path . $ds . $entry);
    
    var_dump($isFile);
    
    is_file($path . $ds . $entry) && !empty($_GET['delete']) ? unlink($path . $ds . $entry) : null;
}
$d->close();

$d = dir($rootDir . "uploads");
echo "<br/>Handle: " . $d->handle . "\n";
echo "<br/>Path: " . $d->path . "\n";
echo "<br/>Dir list after deletions.";
while (false !== ($entry = $d->read())) {
    echo "<br/>" . $entry."\n";
}
$d->close();



