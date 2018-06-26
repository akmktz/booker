<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <?php if ($content['title']): ?>
        <title><?= $content['title'] ?></title>
    <?php endif; ?>
</head>
<body>
<?php if ($content['h1']): ?>
    <h1><?= $content['h1'] ?></h1>
<?php endif; ?>
<?php if ($content['view']): ?>
    <h1><?= $content['view'] ?></h1>
<?php endif; ?>
</body>
</html>




