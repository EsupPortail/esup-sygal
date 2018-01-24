<?php

$remoteAddr = $_SERVER['REMOTE_ADDR'];
$forwarded = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Meta -->
    <meta charset="utf-8">

    <title>Maintenance - SoDoct</title>
</head>

<body>
<div id="navbar">
    <h1 class="title">SoDoct</h1>
    <p class="info">
        REMOTE_ADDR=<?php echo $remoteAddr; ?>, HTTP_X_FORWARDED_FOR=<?php echo $forwarded; ?>
    </p>
</div>

<div id="contenu">

    <h1>SoDoct
        <small>(SOutenance, Doctorat et Organisation du Circuit des Thèses)</small>
    </h1>
    <p class="lead"><?php echo $maintenanceText ?></p>

</div>
<style>

    body{
        margin: 0px;
        padding:0px;
    }

    #navbar {

        font-size: 14px;
        line-height: 1.42857;
        color: #333;
        background-image: linear-gradient(to bottom, #3C3C3C 0px, #222 100%);
        background-repeat: repeat-x;
        background-color: #222;
        border: 1px #080808 solid;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
    }

    #navbar h1 {
        float: left;
        color: #9D9D9D;
        margin-top: 5px;
        margin-left: 5px;
    }

    #navbar .info {
        color:#555;
        text-align: right;
        margin-right: 5px;
    }

    #contenu {
        margin: 2em;
        padding:1em;
        background-color: #f2dede;
        border-radius: 6px;
        border: 1px #d5c0c0 solid;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
    }

</style>
</body>
</html>
<?php die();