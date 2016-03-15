<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Picture Resize Tool</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/stylish-portfolio.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
    <!-- Header -->
    
    <header id="show" class="header">
        <div class="text-vertical-center">
            <div class="overlay">
                
        <form action="index.html">
            <input class="btn btn-dark btn-lg" type="submit" value="<-- upload another picture">
        </form>
                <?php
                    if(isset($_POST['submit'])){
                        //Connection
                        mysql_connect("sfsuswe.com", "twinpair", "csc2016") or die(mysql_error());
                        mysql_select_db("student_twinpair") or die(mysql_error());

                        //Variables holding the image info
                        $filetmpname = $_FILES["image"]["tmp_name"];
                        $filename = str_replace(" ", "_", $_FILES["image"]["name"]);
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $option = $_POST['size'];
                        $custom_width = $_POST['width'];
                        $custom_height = $_POST['height'];

                        //Check file extension type error
                        if($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "gif" ){
                            header("location:error_ext.html");
                        }// Check for file size error
                        else if ($_FILES["image"]["size"] > 1000000) {
                            header("location:error_size.html");
                        }
                        else if((!ctype_digit($custom_width) && ctype_digit($custom_height)) || (ctype_digit($custom_width) && !ctype_digit($custom_height))){
                            header("location:error_dimensions.html");
                        }
                        else{
                            //Get image contents, name and size
                            $image = addslashes(file_get_contents($filetmpname));
                            $image_name = addslashes($filename);
                            list($width, $height) = getimagesize($filetmpname);
                            $image_resize;

                            //Store original image locally to resize it
                            move_uploaded_file($filetmpname, "uploads/" . $filename);
                            $image_path = "uploads/" . $filename;
                            $resize_name = "resize_" . $filename;

                            if(ctype_digit($custom_width) && ctype_digit($custom_height)){
                                list($image_resize, $newwidth, $newheight) = resize($ext, $width, $height, $image_path, $filename, $custom_width, $custom_height);
                            }
                            else if (strstr($option, 'x')){
                                $size = str_replace("x", "", $option);
                                list($image_resize, $newwidth, $newheight) = resize($ext, $width, $height, $image_path, $filename, "x", $size);
                            } 
                            else {
                                $size = str_replace("/", "", $option);
                                list($image_resize, $newwidth, $newheight) = resize($ext, $width, $height, $image_path, $filename, "/", $size);
                            }

                            //If query does not execute correctly redirect to error page
                            if(!$insert = mysql_query("INSERT INTO test VALUES ('', '$image_name', '$resize_name', '$image', '$image_resize')")){
                                header("location:error_query.html");
                            }   
                            else{
                                //Store images into database and retrieve them to display
                                $lastid = mysql_insert_id();
                                echo "<h1><u>Image Resized Successfully!</u></h1>"
                                    ."<h3>Name: $image_name<br>"
                                    ."<h3>Original: (Width-$width px | Height-$height px)</h3><img src=get.php?id=$lastid />"
                                    ."<h3>Name: $resize_name<br>"
                                    ."<h3>Resize image: (Width-$newwidth px | Height-$newheight px)</h3><img src=resize.php?id=$lastid />";
                            }
                        }
                    }

                    function resize($ext, $width, $height, $image_path, $filename, $type, $size){
                        //Create medium size picture
                        switch ($ext){
                            case "png":
                                $src= imagecreatefrompng($image_path);
                                break;
                            case "gif":
                                $src= imagecreatefromgif($image_path);
                                break;
                            default:
                                $src= imagecreatefromjpeg($image_path);
                        }
                        if (ctype_digit($type)){
                            $newWidth = $type;
                            $newHeight = $size;
                        }
                        else if ($type == "x"){
                            $newWidth = $width * $size;
                            $newHeight = $height * $size;
                        }
                        else{
                            $newWidth = $width / $size;
                            $newHeight = $height/ $size;
                        }
                        $tmp = imagecreatetruecolor($newWidth, $newHeight);
                        imagecopyresampled($tmp, $src, 0,0,0,0, $newWidth, $newHeight, $width, $height);
                        $new_path = "uploads/resize_" . $filename;
                        switch ($ext){
                            case "png":
                                imagepng($tmp, $new_path , 9);
                                break;
                            case "gif":
                                imagegif($tmp, $new_path , 100);
                                break;
                            default:
                                imagejpeg($tmp, $new_path , 100);
                        }
                        list($width, $height) = getimagesize($new_path);
                        imagedestroy($src);
                        imagedestroy($tmp);
                        return array(addslashes(file_get_contents($new_path)),$width, $height );
                    }
                ?>
            </div>
        </div>
    </header>
</body>
</html>
