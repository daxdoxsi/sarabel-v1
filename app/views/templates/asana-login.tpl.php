<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo page_title($page_title);?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="stylesheet" href="/font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />

    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-float-label.min.css" />
    <link rel="stylesheet" href="/css/main.css" />
</head>

<body class="background show-spinner no-footer">
<div class="fixed-background"></div>

<?php echo $content; ?>

<script src="/js/vendor/jquery-3.3.1.min.js"></script>
<script src="/js/vendor/bootstrap.bundle.min.js"></script>
<script src="/js/dore.script.js"></script>
<script src="/js/scripts.js"></script>
<script src="/js/custom.js"></script>
</body>

</html>
