<?php require 'Scripts/functions.php';
    //Get some variables out of the way
    $source = null;
    $link = null;
        
    //Check the method of the source form
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
        //If there's a value in dataFile, save it to $source
        if (!empty($_POST["dataFile"])){
            $source = inputSecurity($_POST["dataFile"]);
        }
        
        //If there's a value in from, save it to $link
        if (!empty($_POST["from"])){
            $link = inputSecurity($_POST["from"]);
        }
        
        //If there's a value in download, download a file if there is
        if(!empty($_POST["download"])){
            //Before asking if the user wants to download a file, make sure it exists
            if (file_exists($link)){
                //Headers for the download pop up, PHP.net says that we need them
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($link).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($link));

                //Read the file
                $value = readfile($link);

                //Send the download message
                exit;
            }
        }
                
        //If there's a value in source and link, use the values to remove foreign files from the uploads directory
        if($source != null && $link != null){
            deleteForeignFiles($source, $link);
        }
    
        //What the rest of the file looks like depends on if source has a value or not. If it does, use it to find a value for link, if not, send an error message and tell the user to go back to the main menu
        switch ($source){
            case "salesOrder":
                $link = SalesOrderLink;
                break;
            case "company":
                $link = CompanyLink;
                break;
            case "customer":
                $link = CustomerLink;
                break;
            case "product":
                $link = ProductLink;
                break;
            case "companyPrice":
                $link = CompanyPriceLink;
                break;
            case "customerInterest":
                $link = CustomerInterestLink;
                break;
            //I'd have a default value here, but there'd be nothing following it.
        }
    }
?>

<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Load File</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <!-- Introduce the User to the page -->
        <h2>Load Data File</h2>
        <?php
        //If $link was able to get a value from $source, generate the forms
        if ($link != null){
            //Form 1's existence depends on the existence of the file $link refers to, there'll be a form if, and only if, it exists.
            if (file_exists($link)){ //File exists, generate load form
                echo "Click the button below to load the local " . $source . " file.<br>";
                echo "<form method=post action=fileLoaded.php>";
                    echo "<button type=submit name=dataFile value=" . $source .  ">Load Local File</button>";
                    echo "<input type=text name=from hidden value=" . $link . ">";
                echo "</form><br>";
            }
            else{ //File doesn't exist, produce error
                echo "<p>We are unable to locate the local " . $source . " file.</p>";
            }
            
            //Form 2: Another File upload, but this time we don't save the upload to the local system
            echo "Alternatively...<br>You can load one of your " . $source . " files to the system and we can show you its contents.  We'll even allow you to edit it in house.<br>";
            echo "<form method=post action=fileLoaded.php enctype=multipart/form-data>";
                echo "<input type=file name=upload accept=.csv required><br>";
                echo "<button type=submit name=dataFile value=" . $source . ">Load File</button>";
            echo "</form><br>";
            
            //Form 3's existence also depends on the existance of link, its a form that allows the user to download the system's local data
            if (file_exists($link)){
                echo "If you want to download the local data and don't want to wait the load time, you can click the button below.<br>Don't worry, we understand, a sales order file can take a long time to load when backed up with detailed company, customer, and product files.<br>";
                echo "<form method=post action=loadFile.php>";
                    echo "<button type=submit name=download value=true>Download Local " . $source . " data</button>";
                    //Hidden values to ensure we can reload the page
                    echo "<input type=text name=dataFile hidden value=" . $source . ">";
                    echo "<input type=text name=from hidden value=" . $link . ">";
                echo "</form><br>";
            }
            
            //Form 4 is just a glorified main menu button
            echo "If you did not want to load a file, you can click the button below to return to the main menu.<br>";
            echo "<form method=get action=index.php>";
                echo "<button type=submit name=return>Okay, take me back to the Main Menu</button>";
            echo "</form>";
        }
        else { //No value?
            echo "<p>We are unable to determine where the source file is, or what it is at that matter.  This is probably because you got here from a place other than the main menu.<br>Sorry if you don't come here from the main menu, I've got nothing for you.  You're welcome to go back to the main menu with the button below though.</p>";
            echo "<form method=get action=index.php>";
                echo "<button type=submit name=return>Okay, take me back to the Main Menu</button>";
            echo "</form>";
        }
        ?>
    </body>
    
</html>