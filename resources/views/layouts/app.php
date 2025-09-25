<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />

    <!-- adsense -->
    <?php View::yield('metaProperties') ?>

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">

    <!-- Poppins Font Family -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1">

    <?php View::yield('css') ?>
    
    <?php View::yield('structuredData') ?>

    <?php if(env('APP_ENV') == 'production') { ?>
        
        <!-- meta tags for external resources -->

    <?php } ?>
</head>
<body>


    <!-- Top-Navbar -->
    <?php include '../resources/views/components/header.php'; ?>
    
    <?php View::yield('content') ?>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <?php include '../resources/views/components/footer.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>

    <?php View::yield('js') ?>
    
</body>
</html>