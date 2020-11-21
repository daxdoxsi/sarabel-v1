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
    <link rel="stylesheet" href="/css/vendor/fullcalendar.min.css" />
    <link rel="stylesheet" href="/css/vendor/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="/css/vendor/datatables.responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="/css/vendor/select2.min.css" />
    <link rel="stylesheet" href="/css/vendor/select2-bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/css/vendor/glide.core.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-stars.css" />
    <link rel="stylesheet" href="/css/vendor/nouislider.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-tagsinput.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-datepicker3.min.css" />
    <link rel="stylesheet" href="/css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="/css/main.css" />
    <link rel="stylesheet" href="/css/custom.css" />
</head>

<body id="app-container" class="<?php echo ( true ? 'menu-sub-hidden' : 'menu-default');?> show-spinner">

<?php echo $tpl_modules['top_nav']; ?>
<?php echo $tpl_modules['menu']; ?>
<?php echo $tpl_content; ?>
<?php echo $tpl_modules['footer']; ?>

<script src="/js/vendor/jquery-3.3.1.min.js"></script>
<script src="/js/vendor/bootstrap.bundle.min.js"></script>
<script src="/js/vendor/Chart.bundle.min.js"></script>
<script src="/js/vendor/chartjs-plugin-datalabels.js"></script>
<script src="/js/vendor/moment.min.js"></script>
<script src="/js/vendor/fullcalendar.min.js"></script>
<script src="/js/vendor/datatables.min.js"></script>
<script src="/js/vendor/perfect-scrollbar.min.js"></script>
<script src="/js/vendor/progressbar.min.js"></script>
<script src="/js/vendor/jquery.barrating.min.js"></script>
<!--
<script src="/js/vendor/jquery.validate/jquery.validate.min.js"></script>
<script src="/js/vendor/jquery.validate/additional-methods.min.js"></script>
-->
<script src="/js/vendor/select2.full.js"></script>
<script src="/js/vendor/nouislider.min.js"></script>
<script src="/js/vendor/bootstrap-datepicker.js"></script>
<script src="/js/vendor/bootstrap-tagsinput.min.js"></script>
<script src="/js/vendor/Sortable.js"></script>
<script src="/js/vendor/mousetrap.min.js"></script>
<script src="/js/vendor/glide.min.js"></script>
<script src="/js/dore.script.js"></script>
<script src="/js/scripts.js"></script>
<script src="/js/custom.js"></script>
</body>

</html>
