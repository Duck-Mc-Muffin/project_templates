<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Hello, World!</title>
    </head>
    <body>
        <h1>Hello, World!</h1>
        <p>
        <?php
            include_once "../src/bootstrap.php";
            $my_controller = new App\Controller\MainController();
        ?>
        </p>
        See also: <a href="info.php">phpinfo()</a>
    </body>
</html>
