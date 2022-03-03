<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        $to = 'jhouvardas@gmail.com';
        $subject = 'the subject';
        $message = 'hello';
        $headers = 'From: jhouvardas@jhouv.eu' . "\r\n" .
                'Reply-To: jhouvardas@jhouv.eu' . "\r\n" .
                'X-Mailer: PHP/' . phpversion(7.3);

        mail($to, $subject, $message, $headers);
        ?> 
    </body>
</html>
