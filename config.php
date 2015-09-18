<?php

$active = true; // Should the upload be active?

$config['allowedTypes'] = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);
$config['maxSize'] = 500; // In KiB
$config['uploadPath'] = 'upload/'; // Make it writable for the server!
