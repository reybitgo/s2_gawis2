<!DOCTYPE html>
<!--
* CoreUI PRO Bootstrap Admin Template
* @version <?= $config['version'] ?>
* @link https://coreui.io/product/bootstrap-dashboard-template/
* Copyright (c) 2025 creativeLabs Łukasz Holeczek
* License (https://coreui.io/pro/license/)
-->
<html lang="en">
  <head>
    <!-- Removed by WebCopy -->
    <!--<base href="./">-->
    <!-- Removed by WebCopy -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
    />
    <meta name="description" content="<?= $pageDescription ?>" />
    <meta name="author" content="<?= $config['author'] ?>" />
    <meta
      name="keyword"
      content="<?= $config['keywords'] ?>"
    />
    <title><?= $pageTitle ?></title>

    <!-- Favicon links -->
    <link
      rel="apple-touch-icon"
      sizes="57x57"
      href="<?= $baseDir ?>assets/favicon/apple-icon-57x57.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="60x60"
      href="<?= $baseDir ?>assets/favicon/apple-icon-60x60.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="72x72"
      href="<?= $baseDir ?>assets/favicon/apple-icon-72x72.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="76x76"
      href="<?= $baseDir ?>assets/favicon/apple-icon-76x76.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="114x114"
      href="<?= $baseDir ?>assets/favicon/apple-icon-114x114.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="120x120"
      href="<?= $baseDir ?>assets/favicon/apple-icon-120x120.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="144x144"
      href="<?= $baseDir ?>assets/favicon/apple-icon-144x144.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="152x152"
      href="<?= $baseDir ?>assets/favicon/apple-icon-152x152.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="<?= $baseDir ?>assets/favicon/apple-icon-180x180.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="192x192"
      href="<?= $baseDir ?>assets/favicon/android-icon-192x192.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="<?= $baseDir ?>assets/favicon/favicon-32x32.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="96x96"
      href="<?= $baseDir ?>assets/favicon/favicon-96x96.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="<?= $baseDir ?>assets/favicon/favicon-16x16.png"
    />
    <link rel="manifest" href="<?= $baseDir ?>assets/favicon/manifest.json" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta
      name="msapplication-TileImage"
      content="<?= $baseDir ?>assets/favicon/ms-icon-144x144.png"
    />
    <meta name="theme-color" content="#ffffff" />

    <!-- CoreUI CSS -->
    <link href="<?= $baseDir ?>vendors/simplebar/css/simplebar.css" rel="stylesheet" />
    <link
      rel="stylesheet"
      href="<?= $baseDir ?>vendors/%40coreui/chartjs/css/coreui-chartjs.css"
    />
    <link href="<?= $baseDir ?>css/style.css" rel="stylesheet" />
    <link href="<?= $baseDir ?>vendors/%40coreui/icons/css/free.min.css" rel="stylesheet" />
    <link href="<?= $baseDir ?>vendors/%40coreui/icons/css/flag.min.css" rel="stylesheet" />
    <link href="<?= $baseDir ?>vendors/%40coreui/icons/css/brand.min.css" rel="stylesheet" />

    <!-- Additional CSS files -->
    <?php if (!empty($additionalCSS)): ?>
      <?php foreach ($additionalCSS as $css): ?>
        <link href="<?= $baseDir ?><?= $css ?>" rel="stylesheet" />
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Google Tag Manager -->
    <script>
      (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
          "gtm.start": new Date().getTime(),
          event: "gtm.js",
        });
        var f = d.getElementsByTagName(s)[0],
          j = d.createElement(s),
          dl = l != "dataLayer" ? "&l=" + l : "";
        j.async = true;
        j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
        f.parentNode.insertBefore(j, f);
      })(window, document, "script", "dataLayer", "GTM-KX4JH47");
    </script>
    <!-- End Google Tag Manager -->
  </head>
  <body>
    <!-- Google Tag Manager (noscript)-->
    <noscript>
      <iframe
        src="https://www.googletagmanager.com/ns.html?id=GTM-KX4JH47"
        height="0"
        width="0"
        style="display: none; visibility: hidden"
      ></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript)-->