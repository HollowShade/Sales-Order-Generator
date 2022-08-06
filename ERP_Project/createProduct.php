<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Create A Product</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <?php require 'Scripts/functions.php';
            //These variables are for the creating part of the application, if not both of its modes
            $name = null;
            $price = 0;
            $reset = false;
            $noShow = true;
            $endMessage = "Reminder: It's easier to create a set of data from a pre-made list of names than from scratch!";
            $footerWarning = "";
        
            //Variables for the editing part of the application
            $link = ProductLink;
            $isLoaded = false;
            $loadEntry = "PP-000001";
            $file = null;
            $dataStorage[0][0] = null;
            $lastPage = null;
        
            //Check if there's a valid input
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
                //Determine if the page was loaded
                $isLoaded = inputSecurity($_POST["edit"]);
                
                //Get the address of the last page
                $lastPage = inputSecurity($_POST["priorLink"]);
                
                //Check source for a value. If it's not there or the file related to it doesn't exist, set it to the default value
                $link = inputSecurity($_POST["source"]);
                if (!file_exists($link)){
                    $link = ProductLink;
                }
                
                if ($isLoaded == "true"){
                    //Update values that we get from either this page or the postLoad company page
                    $loadEntry = inputSecurity($_POST["entry"]);//Which row does our data come from?
                    
                    //Load data from the file
                    try {
                        $dataStorage = loadFile($link);
                    }
                    catch (IOException $error){
                        $footerWarning = "<br><b>LOAD FAILED: Going from data editing mode to data entry</b>";
                        $isLoaded = false;
                    }
                }
                else{
                    $isLoaded = false; //Ensuring it remains false
                }
                
                //Assigning a value to the product name
                if (empty($_POST["product"])){ //If the user hasn't provided an input
                    if ($isLoaded){
                        $name = $dataStorage[searchArray($dataStorage, $loadEntry)][1];
                        
                        //If we're in edit mode and the last page is the current page, warn the user that we reloaded the old name
                        if ($lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){
                            $footerWarning .= "<br>The product didn't have a name when being saved. We're using its old name instead of using giberish.";
                        }
                    }
                    else{
                        //Entry mode: Tell the user that we're using giberish
                        $name = giberish(rand());
                        $footerWarning .= "<br>Unless if you want the name of the last product to remain a mess of bunched up characters, you should edit the name of the last product later.";
                    }
                }
                else{
                    $name = inputSecurity($_POST["product"]);
                    $noShow = false;
                }
                
                //Assigning a value to the product price
                if (empty($_POST["cost"])){ //If the user hasn't provided an input
                    if ($isLoaded && $lastPage != htmlspecialchars($_SERVER["PHP_SELF"])){
                        //Set the value of $price to the value on file
                        $price = $dataStorage[searchArray($dataStorage, $loadEntry)][2];
                    }
                    else{
                        //Tell the user that we're using a default value
                        $footerWarning .= "<br>The default value of 0 was used in the product price";
                    }
                }
                else{
                    $price = floatval(inputSecurity($_POST["cost"]));
                    $noShow = false;
                }
                
                //Give $reset a value before we use it to determine the value of loadEntry
                if (!empty($_POST["reset"])){
                    $reset = inputSecurity($_POST["reset"]);
                    if ($reset == "true"){
                        $reset = true;
                    }
                    else{
                        $reset = false;
                    }
                }
                
                //Give $loadEntry a value.  We'll have to replace it in entry mode, so a save will occur afterward
                if ($reset){
                    $loadEntry = "PP-000001"; //This marks the latest entry as the first in a new set of products
                }
                else {
                    //Incase if we didn't get data from a file, get it from the page.
                    $loadEntry = inputSecurity($_POST["entry"]);
                }
                
                //Save the data before preparing $loadEntry
                if ($noShow && $lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){ //Error Message!
                    $endMessage = "Reminder: You can not save a product, whether its new or an existing product, unless you give it a value.";
                    
                    //Reset all inputs we'd display later
                    if($isLoaded){
                        //We're in edit mode, let's use it our preexisting data to find the last value of price.
                        $price = $dataStorage[searchArray($dataStorage, $loadEntry)][2];
                    }
                    else {
                        //We're in entry mode, nothing for name to reference.
                        $name = null;
                    }
                }
                else{
                    //Check what mode we're in, assuming that we're not deleting a product.
                    if ($isLoaded){//Then we are updating an entry we decided to edit
                        //Prepare dataStorage to edit data
                        $dataStorage[searchArray($dataStorage, $loadEntry)][0] = $loadEntry; //Holds the loadEntry generated from the assignment block above
                        $dataStorage[searchArray($dataStorage, $loadEntry)][1] = $name; //Holds the input for the product name
                        $dataStorage[searchArray($dataStorage, $loadEntry)][2] = $price; //Holds the input for the product price
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
                                $endMessage = "Last saved memory of product " . $loadEntry . " loaded";
                            }
                            else{
                                $endMessage = "Product " . $loadEntry . " updated";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with updating product " . $loadEntry . ".";
                        }
                    }
                    else { //We are creating a new product
                        try{//Check if we need to pull a reset when saving data
                            if ($reset || !file_exists(ProductLink)){
                                $file = fopen($link, "w");
                                
                                //With the file reset, we need to reinitialize its header
                                fputcsv($file, ProductHeader);
                            }
                            else{
                                $file = fopen($link, "a");
                            }
                        
                            //save the line
                            fputcsv($file, array($loadEntry, $name, $price));
                        
                            //Close the file
                            fclose($file);
                        
                            //Let the user know that the deed is done depending on the command provided
                            if ($reset){
                                $endMessage = "Product Data file reset, now there's only one product entry remaining.";
                            }
                            else{
                                $endMessage = "A Product was added to the Product Data file.";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with creating a new product";
                        }
                    }
                }   
            }
        
            //Recalibrate loadEntry so it has a unique ID.
            if (!$isLoaded){
                if(file_exists($link)){
                    try{
                        //Load the contents of products.csv and add them to dataStorage
                        $dataStorage = loadFile($link);
                        
                        //Use $dataStorage in numericIDGenerator to generate a unique ID
                        $loadEntry = numericIDGenerator(1, "product", $dataStorage);
                    }
                    catch (IOException $error){ //In case we don't have a file to refer to when adding a new entry
                        $footerWarning .= "<br>There may be a repeated ID if you save the file again";
                    }
                }
                else{
                    //Return $loadEntry to its default value
                    $loadEntry = "PP-000001";
                }
            }
        
            //The introduction depends on whether the page was loaded or not
            if ($isLoaded){
                //We need loadEntry to use this title
                echo "<h2>Edit " . $name . "</h2>";
                echo "<p>If you need to change the name or price of this product, you can do so below.<br>";
                echo "Alternatively, you can also delete the entry or enter data entry mode.</p>";
            }
            else{
                echo "<h2>Create a Product</h2>";
                echo "<p>Enter a product name and price and we'll add it to our list of products<br>";
                echo "Product price may be optional, but all products have a price realisticly<br>";
                echo "You can also choose to restart that list or enter edit mode</p>";
            }
        ?>
        
        <!-- Input Form -->
        <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
            <label for="product">Product name:</label>
            <input type="text" id="product" name="product" value="<?php echo $name; ?>">
            <br>
            <label for="cost">Product Price:</label>
            <input type="text" id="cost" name="cost" value="<?php echo $price; ?>">
            <!-- I had to save the data in the comment block above to make the data below function correctly -->
            <input type="text" id="entry" name="entry" value=<?php echo $loadEntry?> hidden>
            <!-- Other hidden values -->
            <input type="text" name="source" value="<?php echo $link; ?>" hidden>
            <input type="text" name="edit" hidden value="<?php echo strval($isLoaded); ?>">
            <input type="text" name=priorLink hidden value="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <br>
            
            <?php
            //Give the user the choice to reset the file if they're in data entry mode
            if ($isLoaded){
                //If we're in edit mode, keep $reset set to false
                echo "<input type=text name=reset value=false hidden>";
            }
            else{
                //This is where the option presents itself
                echo "<label for=reset>Overwrite the current product records file? </label>";
                echo "<select id=reset name=reset>";
                    echo "<option value=false>No</option>";
                    echo "<option value=true>Yes</option>";
                echo "</select><br>";
            }
            ?>
            
            <!-- This section is where the submit buttons live -->
            <button type="submit">Save Product</button>
            <?php
            //Give the user the choice to delete the entry if they're in data editing mode
            if ($isLoaded){
                echo "<button type=submit formaction=fileLoaded.php id=delete name=delete value=true>Delete Data Entry</button>";
                /*The original simpleSave stored $loadEntry and $link here, but while $loadEntry can migrate over to file loaded for use of the same name, $link is known as from, so it will hide right here.
                  fileLoaded.php also requires dataFile, so we'll just sent product its way.
                */
                echo "<input type=text name=dataFile value=product hidden>";
                echo "<input type=text name=from value=" . $link . " hidden>";
            }
            ?>
        </form>
        <br>
        
        <!-- Create a form for links, along with an introduction -->
        <p>The links for leaving this page are shown below.</p>
        <form method="POST">
            <button type="submit" formaction="index.php">Return to Main Menu</button>
            <!-- Since both modes can travel to the view data page, dataFile and from/link make good global hidden input. -->
            <input type="text" name="dataFile" value="product" hidden>
            <input type="text" name="from" value="<?php echo $link; ?>" hidden>
            <input type="text" name="delete" value="false" hidden>
            <input type="text" name=priorLink hidden value="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <br>
            <br>
            <?php
            //The rest of the forms buttons depend on the mode the page is in.
            if ($isLoaded){ //Edit mode
                echo "<button type=submit formaction=fileLoaded.php>Return to the Load Results</button><br><br>";
                echo "<button type=submit formaction=loadFile.php>Return to the Product File Select Menu</button><br><br>";
                echo "<button type=submit formaction=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " name=edit value=false>Switch to Data Entry mode</button><br><br>";
                
                //Hidden inputs
                echo "<input type=text name=reset value=false hidden>";
                echo "<input type=text name=entry value=" . $loadEntry . " hidden>";
                echo "<input type=text name=source value=" . $link . " hidden>";
            }
            else { //Entry mode
                echo "<button type=submit formaction=fileLoaded.php>Switch to Edit mode</button><br><br>";
            }
            ?>
        </form>
        
        <?php
            //This is the part where we display our string variables
            echo $endMessage;
        
            //Don't display the footer warning if the data is a no show
            if (!$noShow){
                echo $footerWarning;
            }
        ?>
    </body>
    
</html>