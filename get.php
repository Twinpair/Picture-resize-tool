<?php
    //Sets up connection
    mysql_connect("sfsuswe.com", "twinpair", "csc2016") or die(mysql_error());
    mysql_select_db("student_twinpair") or die(mysql_error());

    //Request id of the last image uploaded
    $id = addslashes($_REQUEST['id']);
    
    //Query to retreive desired image from database
    $image = mysql_query("SELECT * FROM test WHERE id = $id");
    $image = mysql_fetch_assoc($image);
    $image = $image['image'];
    
    //This file is now of jpeg type
    header("Content-type: image/jpeg");
    
    //Display image
    echo $image;
?>
