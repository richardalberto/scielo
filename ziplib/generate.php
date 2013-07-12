<?php
include("ziplib.php");

$zipfile = new Ziplib;
$zipfile->zl_add_file("This is a test file","path/to/file","g9");
//You can stream the ZIP file or write it in a file on your server
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"testfile.zip\"");
echo $zipfile->zl_pack("zip file comments");
?>