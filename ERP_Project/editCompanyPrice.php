<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Change Company Prices</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <?php require 'Scripts/functions.php';
            //These are either values that we save or messages we print at the end
            $price = 0;
            $stock = 0;
            $active = "Inactive";
            $endMessage = "If you're here, you probably got here by a direct link.<br>";
            $footerWarning = "";
        
            //Variables for entry identification
            $link = CompanyPriceLink;
            $isLoaded = false;
            $entry = "CP-000001";
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
                            $link = CompanyPriceLink;
                            $isLoaded = false;
                        }
                    }
                    else { //The file doesn't exist, launch an error and set isLoad to false
                        $endMessage = "If you're here, there was an issue with loading the file.";
                        $link = CompanyPriceLink;
                        $isLoaded = false;
                    }
                    
                    /*Normally, data assignments would occur outside of the if statement that checks if we're in edit mode.
                     Since editCompanyPrice.php is only capible of editing data and not creating it, we could save the system some time if we kept the data assignments off a path the system wouldn't access.
                     Now, let's assign values to $price, $stock, and $active, starting with $price*/
                    if (empty($_POST["value"])){ //If the user hasn't provided an input
                        //Use $lastPage to determine if we need to load data or not
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                            //Load Data
                            $price = $dataStorage[searchArray($dataStorage, $entry)][3];
                        }
                        else{
                            //Tell the user that a default value was used because they left a value field blank.
                            $footerWarning .= "<br>The product's price was set to a default value of 0 due to a missing price tag";
                        }
                    }
                    else{
                        $price = floatval(inputSecurity($_POST["value"]));
                    }
                    
                    //Assigning a value to the company's current stock of the product
                    if (empty($_POST["stock"])){ //If the user hasn't provided an input
                        //Use $lastPage to determine if we need to load data or not
                        if ($lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                            //Load Data
                            $stock = $dataStorage[searchArray($dataStorage, $entry)][4];
                        }
                        else{
                            //Tell the user that a default value was used because they left a value field blank.
                            $footerWarning .= "<br>In case you haven't noticed, this company's product stock is empty.";
                        }
                    }
                    else{
                        $stock = intval(inputSecurity($_POST["stock"]));
                    }
                    
                    //Assigning a value to the flag of active or inactive
                    if (empty($_POST["activity"])){ //If the user hasn't provided an input
                        //$Active is a field with its value set by a select prompt in a form, the only way for it to not have a value is either the user toying with the code, or the page first being loaded
                        $active = $dataStorage[searchArray($dataStorage, $entry)][5];
                    }
                    else{
                        $active = inputSecurity($_POST["activity"]);
                    }
                    
                    //If we had a create mode, we'd have a block of code that gave values to the IDs, but we don't, so we're just going to save changes
                    $dataStorage[searchArray($dataStorage, $entry)][3] = $price; //Holds the company's price of the product
                    $dataStorage[searchArray($dataStorage, $entry)][4] = $stock; //Holds the company's current stock of the product
                    $dataStorage[searchArray($dataStorage, $entry)][5] = $active; //Holds the current status of the product in the company's wares
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
                            $endMessage = "Company Price Entry updated";
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
                echo "<h2>Edit company product price entry " . $entry . "</h2>";
                echo "<p>If you need to change the company product price entry's attributes, like price, stock, or status, you can do so by entering values into the fields below.";
                echo "<br>You can also disable this entry by making it an inactive product";
                echo "<br>When you're done, click submit to save your changes, or leave the page to stop making changes.";
                echo "<br>We'll be ready when you provide no value for an input.</p>";
                
                //Input form, only visible in edit mode
                echo "<form method=post action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                    echo "<br><label for=value>Copmany " . $dataStorage[searchArray($dataStorage, $entry)][1] . "'s price of product " . $dataStorage[searchArray($dataStorage, $entry)][2] . ":</label>";
                    echo "<input type=text name=value value=" . $price . "><br>";
                    echo "<label for=stock>Their current stock of the product:</label>";
                    echo "<input type=number name=stock min=0 value=" . $stock . "><br>";
                    //$Active is different from the other inputs as it's a select input
                    echo "<label for=activity>Company's status of the product:</label>";
                    echo "<select id=activity name=activity>";
                        //The value inside of $active determines what activity will display as options
                        switch ($active){
                            case "Active":
                                echo "<option value=" . $active . ">" . $active . "</option>";
                                echo "<option value=Inactive>Inactive</option>";
                                break;
                            case "Inactive":
                                echo "<option value=" . $active . ">" . $active . "</option>";
                                echo "<option value=Active>Active</option>"; 
                                break;
                            default:
                                echo "<option value=" . $active . ">" . $active . "</option>";
                                echo "<option value=Active>Active</option>";
                                echo "<option value=Inactive>Inactive</option>";
                                break;
                        }
                    echo "</select><br>";
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
                echo "<br>While this page can edit company product price data entries, it can't create those entries.<br>";
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
            <input type="text" name="dataFile" value="companyPrice" hidden>
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