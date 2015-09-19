<?php
session_start();

require_once('config.php');

// Check the config
if (!file_exists($config['uploadPath']) || !is_writable($config['uploadPath'])) {
    exit('ERROR: Can not write to upload path! ("'.$config['uploadPath'].'")');
}
elseif (!is_numeric($config['maxSize'])) {
    exit('ERROR: Max size is not set correctly!');
}

$formats = array_keys(array_filter($config['allowedTypes'], function($k) { return $k; }));

// Check if the upload is active and the user is uploading
if ($active && isset($_FILES['fileup'])) {

    $errors = array();

    // Check the file size
    if ($_FILES['fileup']['size'] > $config['maxSize']*1024) {
        $errors[] = 'File to large! Max size is '.$config['maxSize'].' KiB!';;
    }

    // Check the file type
    $type = exif_imagetype($_FILES['fileup']['tmp_name']);
    if (!($type == 1 && in_array('gif', $formats)) && !($type == 2 && in_array('jpeg', $formats)) && !($type == 3 && in_array('png', $formats))) {
        $errors[] = 'File type not allowed!';
    }

    if (empty($errors)) {

        $uploadpath = $config['uploadPath'].pathinfo($_FILES['fileup']['name'], PATHINFO_FILENAME).'_'.time().'.'.pathinfo($_FILES['fileup']['name'], PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES['fileup']['tmp_name'], $uploadpath)) {
            chmod($uploadpath, 0755);
            $_SESSION['success'] = true;
        }
        else {
            $errors[] = 'Something went wrong during upload. Try again!';
        }
    }

    $_SESSION['errors'] = $errors;

    // Redirect to same page to avoid uploading multiple times
    header('Location: .');
    exit();
}
elseif (!empty($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
elseif (!empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (count($formats) == 1) {
    $outFormat = ' is <strong>'.$formats[0].'</strong>';
}
else {
    $outFormat = 's are ';
    for ($i = 0; $i < count($formats)-1; $i++) {
        $outFormat.= '<strong>'.$formats[$i].'</strong>, ';
    }
    $outFormat = substr($outFormat, 0, -2).' and <strong>'.$formats[$i].'</strong>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Paint tournament</title>

    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="Shortcut Icon" href="images/favicon.ico" />
</head>
<body>

<div id="content">

    <div id="textcontainer">

<?php if ($active): ?>

<?php if (isset($success)): ?>
    <p class="success">Upload complete!</p>
<?php endif; ?>

<?php
if (isset($errors)) {
    foreach ($errors as $error) {
        echo '<p class="error">Error: '.$error.'</p>';
    }
}
?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">

        <p>Please select the image you wish to upload. Allowed format<?php echo $outFormat; ?>. The max size is <strong><?php echo $config['maxSize']; ?></strong> KiB.</p>

        <p>File to upload: <input type="text" id="filename" placeholder="No file selected" disabled="disabled" /></p>
        <div class="fileUpload button">
            <span>Select file</span>
            <input type="file" id="fileup" name="fileup" class="upload" accept="<?php echo 'image/'.implode(',image/', $formats); ?>" />
        </div>

        <button type="submit" id="submit">Upload</button>
    </form>

<?php else: ?>

    <p>There is no active tournament.</p>

<?php endif; ?>

    </div>
</div>

<script>
var fileup = document.getElementById("fileup");
var form = document.getElementsByTagName("form")[0];

fileup.onchange = function() {
    document.getElementById("filename").value = this.value;
};

form.onsubmit = function() {
    if (fileup.value == "") {
        document.getElementById("filename").classList.add("error");
        return false;
    }
};
</script>

</body>
</html>
