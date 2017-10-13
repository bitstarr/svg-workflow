<?php
    define( 'WP_DEBUG', true );
    define( 'TEMPLATEPATH', dirname(__FILE__) );

    require 'inc/icons.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>SVG Icons</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel='stylesheet' href='dist/css/main.css' type='text/css' media='all' />
</head>
<body>

    <main class="main">
        <div class="contact">
            <div class="logo"><?php svg_icon( 'logo', array( 'title' => 'Company Name' ) ) ?></div>
            <h1 class="title">SVG Icons Demo</h1>
            <p><?php svg_icon( 'phone', array( 'title' => 'Phone' ) ) ?> 0123 456 78 90</p>
            <p><?php svg_icon( 'mail', array( 'title' => 'E-Mail', 'class' => 'icon--blue' ) ) ?> foo@bar.foo</p>
        </div>
    </main>

    <script type='text/javascript' src='./dist/js/svgxuse.min.js'></script>

</body>
</html>