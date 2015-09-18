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

// Check if the upload is active and the user is uploading
if ($active && isset($_FILES['fileup'])) {

    $errors = array();

    // Check the file size
    if ($_FILES['fileup']['size'] > $config['maxSize']*1024) {
        $errors[] = 'File to large! Max size is '.$config['maxSize'].' KiB!';;
    }

    // Check the file type
    $type = exif_imagetype($_FILES['fileup']['tmp_name']);
    if (!in_array($type, $config['allowedTypes'])) {
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Paint tournament</title>

    <link rel="stylesheet" href="css/style.css" />
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

        <p>Please select the image you wish to upload.</p>

        <input type="file" id="browse" name="fileup" accept="image/png,image/jpeg" />

        <button type="submit">Upload</button>
    </form>

<?php else: ?>

    <p>There is no active contest.</p>

<?php endif; ?>

    </div>

    <img id="preview" />
</div>

</body>
</html>
