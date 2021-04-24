<?php
namespace Server\Upload;

error_reporting(E_ALL | E_STRICT);
require('CustomUploadHandler.php');

$upload_handler = new CustomUploadHandler();
