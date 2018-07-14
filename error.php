<html>
<head>
    <title>Error</title>
</head>
<body>
    <h1>Error <?php echo $caughtException->getCode(); ?></h1>
    <p><?php echo $caughtException->getMessage(); ?></p>
    <p><?php echo $caughtException->getFile(); ?>:<?php echo $caughtException->getLine(); ?></p>
    <pre>
        <?php echo $caughtException->getTraceAsString(); ?>
    </pre>
</body>
</html>