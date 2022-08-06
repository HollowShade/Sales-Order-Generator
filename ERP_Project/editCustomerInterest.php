<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Change Customer Interests</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <?php require 'Scripts/functions.php';
            //These are either values that we save or messages we print at the end
            $interest = 0;
            $min = 0;
            $max = 120;
            $endMessage = "If you're here, you probably got here by a direct link.<br>";
            $footerWarning = "";
        
            //Variables for the editing part of the application
            $link = CustomerInterestLink;
            $isLoaded = false;
            $entry = "CI-000001";
            $file = null;
            $dataStorage[0][0] = null;
            $lastPage = null;
        
            //Check if there's a valid input
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
                //Determine if the page was loaded
                $isLoaded = inputSecurity($_POST["edit"]);
                
                if ($isLoaded == "true"){
                    //Update values that we get from either this page or the fileLoaded page
                    $link = inputSecurity($_POST["source"]);//Where is the load file located?
                    $lastPage = inputSecurity($_POST["priorLink"]);//What's the URL of the last page?
                    
                    //Retrieve the ID from the last page
                    $entry = inputSecurity($_POST["entry"]);
                    
                    //Use these variables to load data from the file
                    if (file_exists($link)){
                        try {
                            $dataStorage = loadFile($link);
                        }
                        catch (IOException $error){
                            $endMessage = "If you're here, there was an issue with loading the file.";
                            $link = CustomerInterestLink;
                            $isLoaded = false;
                        }
                    }
                    else { //The file doesn't exist, launch an error and set isLoad to false
                        $endMessage = "If you're here, there was an issue with loading the file.";
                        $link = CustomerInterestLink;
                        $isLoaded = false;
                    }
                    
                    /*Normally, data assignments would occur outside of the if statement that checks if we're in edit mode.
                     Since editCustomerInterest.php is only capible of editing data and not creating it, we could save the system some time if we kept the data assignments off a path the system wouldn't access.
                     Now, let's assign values to $interest, $min, and $max, starting with $interest*/
                    if (empty($_POST["interest"])){ //If the user hasn't provided an input
                        //Use $lastPage to determine if we need to load data or not
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                            //Load Data
                            $interest = $dataStorage[searchArray($dataStorage, $entry)][3];
                        }
                        else{
                            //Tell the user that a default value was used because they left a value field blank.
                            $footerWarning .= "<br>Customer Interest was set to 0. The customer will not be seen purchasing this item in any future sales order.";
                        }
                    }
                    else{
                        $interest = intval(inputSecurity($_POST["interest"]));
                    }
                    
                    //Assigning a value to the minimum number of purchases
                    if (empty($_POST["minimum"])){ //If the user hasn't provided an input
                        //Use $lastPage to determine if we need to load data or not
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                            //Load Data
                            $min = $dataStorage[searchArray($dataStorage, $entry)][4];
                        }
                        else{
                            //Tell the user that a default value was used because they left a value field blank.
                            $footerWarning .= "<br>Customer minimum amount of purchases, if they make purchases, was set to a default value of 0.";
                        }
                    }
                    else{
                        $min = intval(inputSecurity($_POST["minimum"]));
                    }
                                      
                    //Assigning a value to the maximum number of purchases
                    if (empty($_POST["maximum"])){ //If the user hasn't provided an input
                        //Use $lastPage to determine if we need to load data or not
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                            //Load Data
                            $max = $dataStorage[searchArray($dataStorage, $entry)][5];
                        }
                        else{
                            //Tell the user that a default value was used because they left a value field blank.
                            $footerWarning .= "<br>Customer maximum amount of purchases, if they make purchases, was set to a default value of 120.";
                        }
                    }
                    else{
                        $max = intval(inputSecurity($_POST["maximum"]));
                    }
                                      
                    //Check to make sure $max is more thatn $min
                    if ($max < $min){
                        $footerWarning .= "<br><b>ERROR:</b> The provided maximum amount of purchases was less than the minimum amount of purchases. We've reloaded the old maximum and minimum values to combat this issue.";
                        $max = $dataStorage[searchArray($dataStorage, $entry)][5];
                        $min = $dataStorage[searchArray($dataStorage, $entry)][4];
                        if ($max < $min){
                            $footerWarning .= "...or we would've if SOMEONE didn't make the source file's maximum less than the source file's minimum. <br>Ugh, can't believe I have to reset maximum and minimum to their default value. Thanks a lot!";
                            $min = 0;
                            $max = 120;
                        }
                    }
                                      
                    //If we had a create mode, we'd have a block of code that gave values to the IDs, but we don't, so we're just going to save changes
                    $dataStorage[searchArray($dataStorage, $entry)][3] = $interest; //Holds the customer product interest
                    $dataStorage[searchArray($dataStorage, $entry)][4] = $min; //Holds the Customer minimum amount of purchases, if they make purchases
                    $dataStorage[searchArray($dataStorage, $entry)][5] = $max; //Holds the Customer maximum amount of purchases, if they make purchases
                    try{
                        //Open the file
                        $file = fopen($link, "w");
                        
                        //Save changes to the data list
                        foreach($dataStorage as $dataRow){
                            fputcsv($file, $dataRow);
                        }
                        
                        //Close the file
                        fclose($file);
                        
                        //Let the user know that the deed is done
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])) {
                            //Edit mode introduction and error message
                            $endMessage = "Entry " . $entry . " loaded.";
                        }
                        else{
                            $endMessage = "Customer Interest Entry updated";
                        }
                    }
                    catch (IOException $error){
                        $endMessage = "There was an issue with updating entry " . $entry . ".";
                    }
                }
                else{
                    $isLoaded = false; //Ensuring it remains false
                }
            }
        
            //Check the value of $isLoaded to determine how to interact with the user
            if ($isLoaded){
                //Introduce the user to the page
                echo "<h2>Edit customer product interest entry " . $entry . "</h2>";
                echo "<p>If you need to change the customer product interest entry's attributes, like interest level, purchase minimum, and purchase maximum, you can do so by entering values into the fields below.";
                echo "<br>You can also disable the customer's interest by setting interest to 0.";
                echo "<br>When you're done, click submit to save your changes, or leave the page to stop making changes.";
                echo "<br>We'll be ready when you provide no value for an input.</p>";
                
                //Input form, only visible in edit mode
                echo "<form method=post action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                    echo "<br><label for=interest>Customer Interest Level on a 0-10 scale (with 10 being the highest):</label>";
                    echo "<input type=number name=interest min=0 max=10 value=" . $interest . "><br>";
                    echo "<label for=minimum>Minimum number of products purchased if customer purchases products:</label>";
                    echo "<input type=number name=minimum min=0 value=" . $min . "><br>";
                    echo "<label for=maximum>Maximum number of products purchased if customer purchases products:</label>";
                    echo "<input type=number name=maximum min=0 value=" . $max . "><br>";
                    //Hidden Values
                    echo "<input type=text name=source hidden value=" . $link . ">";
                    echo "<input type=text name=edit hidden value=" .$isLoaded . ">";
                    echo "<input type=text name=priorLink hidden value=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                    //Submit Button, this is where we keep our IDs
                    echo "<button type=submit name=entry value=" . $entry . ">Save Customer Product Interest Entry</button>";
                echo "</form><br>";
            }
            else{
                echo "<h2>ERROR: No Data Loaded</h2>";
                echo $endMessage;
                echo "<br>While this page can edit customer product interest data entries, it can't create those entries.<br>";
            }
        ?>
        
        <!-- This form, the links form, is the only part of the program that's constant -->
        <p>If you want to travel to another page, you can do so with the links below</p>
        <form method="post">
            <button type="submit" formaction="index.php">Return to Main Menu</button>
            <br><br>
            <?php
                //The following buttons should only appear if the page was loaded with the POST method
                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                    echo "<button type=submit formaction=fileLoaded.php>Return to the Load Results</button>";
                    echo "<br><br>";
                    echo "<button type=submit formaction=loadFile.php>Return to the Company Product Price File Select Menu</button>";
                    echo "<br><br>";
                }
            ?>
            <!-- Hidden values -->
            <input type="text" name="dataFile" value="customerInterest" hidden>
            <input type="text" name="from" value="<?php echo $link; ?>" hidden>
            <input type="text" name="delete" value="false" hidden>
        </form>
        <br>
        
        <!-- If $footerWarning has any value, display it now -->
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            echo $endMessage;
        }
        echo $footerWarning; ?>
        
    </body>
    
</html>    