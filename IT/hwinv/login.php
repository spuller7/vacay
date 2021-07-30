<?php
    session_start();
?>

<html>
    <head>
        <title>PC Inventory Tool</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <form action="functions.php" method="post">
            <input name="username" placeholder="username">
            <input type="password" name="password" placeholder="password">
            <input type="submit" value="Submit">
            <input type="hidden" name="function" value="authenticate">
        </form>       
    </body>
    <script>
    </script>
</html>