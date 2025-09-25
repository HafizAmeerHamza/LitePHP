<?php View::extend('layouts/app'); ?>

<?php View::section('metaProperties'); ?>

    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">

    <link rel="canonical" href="<?= route('home') ?>" />

    <!-- Open Graph Protocol for Social Media -->
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" /> <!--website, article -->
    <meta property="og:title" content="<?= $title ?>" />
    <meta property="og:description" content="<?= $description ?>" />
    <meta property="og:url" content="<?= route('home') ?>" />
    <meta property="og:site_name" content="<?= env('APP_NAME') ?>" />
    <meta property="og:image" content="<?= asset('images/share/preview/logo.png') ?>" />
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="<?=$title ?>">
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />

    <!-- Twitter Card (Summary with Large Image) -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?=$title ?>" />
    <meta name="twitter:description" content="<?= $description ?>" />
    <meta name="twitter:image" content="<?= asset('images/share/preview/logo.png') ?>" />

<?php View::endSection() ?>


<?php View::section('content') ?>
    <section class="wrapper medium mt-3">
        Welcome to ABC
    </section>

<?php View::endSection() ?>


<?php View::section('structuredData'); ?>
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "WebPage",
          "name": "<?= $title ?>",
          "description": "<?= $description ?>",
          "url": "<?= route('home') ?>",
          "image": {
            "@type": "ImageObject",
            "url": "<?= asset('images/share/preview/logo.png') ?>",
            "width": 1200,
            "height": 630
          },
          "inLanguage": "en-US",
          "isPartOf": {
            "@type": "WebSite",
            "name": "ABC",
            "url": "<?= route('home') ?>"
          }
        }
    </script>
<?php View::endSection() ?>
