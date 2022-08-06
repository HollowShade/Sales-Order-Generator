<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Create A Customer</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>

    <body>
        <?php require 'Scripts/functions.php';
            //These variables are for the creating part of the application, if not both of its modes
            $name = null;
            $address = null;
            $area = null;
            $reset = false;
            $noShow = true;
            $endMessage = "Reminder: It's easier to create a set of data from a pre-made list of names than from scratch!";
            $footerWarning = "";
        
            //Variables for the editing part of the application
            $link = CustomerLink;
            $isLoaded = false;
            $loadEntry = "CC-000001";
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
                if(!file_exists($link)){
                    $link = CustomerLink;
                }
                
                if ($isLoaded == "true"){
                    //Update values that we get from either this page or the postLoad customer page
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
                
                //Assigning a value to the customer name
                if (empty($_POST["customer"])){ //If the user hasn't provided an input
                    if ($isLoaded){
                        $name = $dataStorage[searchArray($dataStorage, $loadEntry)][1];
                        
                        //If we're in edit mode and the last page is the current page, warn the user that we reloaded the old name
                        if ($lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){
                            $footerWarning .= "<br>The customer didn't have a name when being saved. We're using its old name instead of using giberish.";
                        }
                    }
                    else{
                        //Entry mode: Tell the user that we're using giberish
                        $name = giberish(rand());
                        $footerWarning .= "<br>Unless if you want the name of the last customer to remain a mess of bunched up characters, you should edit the name of the last customer later.";
                    }
                }
                else{
                    $name = inputSecurity($_POST["customer"]);
                    $noShow = false;
                }
                
                //Assigning a value to the customer address
                if (empty($_POST["home"])){ //If the user hasn't provided an input
                    if ($isLoaded){
                        //Edit mode: Set the value of $address to the value on file
                        $address = $dataStorage[searchArray($dataStorage, $loadEntry)][2];
                        
                        //If we're in edit mode and the last page is the current page, warn the user that we reloaded the old address over using giberish
                        if ($lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){
                            $footerWarning .= "<br>The customer didn't have an address when being saved. We're using its old name instead of using giberish.";
                        }
                    }
                    else{
                        //Entry mode: Tell the user that we're using giberish
                        $address = giberish(rand());
                        $footerWarning .= "<br>Unless if you want the address of the last customer to remain a mess of bunched up characters, you should edit the address of the last customer later.";
                    }
                }
                else{
                    $address = inputSecurity($_POST["home"]);
                    $noShow = false;
                }
                
                //Assigning a value to the customer area
                if (empty($_POST["area"]) || $_POST["area"] == "New Area"){//If an area wasn't selected in the area select dropdown menu
                    if (empty($_POST["newArea"])){//If a new area wasn't provided
                        if ($isLoaded){
                            //Edit Mode: Set the value of $area to the value on file
                            $area = $dataStorage[searchArray($dataStorage, $loadEntry)][3];
                            
                            //If we're in edit mode and the last page is the current page, warn the user that we reloaded the old area
                            if ($lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){
                                $footerWarning .= "<br>The customer didn't have an area specified when a new area was being saved. We're using its old area instead a default area.";
                            }
                        }
                        else{
                            //Entry Mode: Tell the user that we've set the area to central
                            $area = presetAreas(4);
                            $footerWarning .= "<br>Area was set to the default value of central";
                        }
                    }
                    else{
                        $area = inputSecurity($_POST["newArea"]);
                        $noShow = false;
                    }
                }
                else{
                    $area = inputSecurity($_POST["area"]);
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
                
                //Give $loadEntry a value.  Well have to replace it in entry mode, so a save will occur afterward
                if ($reset){
                    $loadEntry = "CC-000001"; //This marks the latest entry as the first in a new set of customers
                }
                else {
                    //Incase if we didn't get data from a file, get it from the page.
                    $loadEntry = inputSecurity($_POST["entry"]);
                }
                
                //Save the data before preparing $loadEntry
                if ($noShow && $lastPage == htmlspecialchars($_SERVER["PHP_SELF"])){ //Error Message!
                    $endMessage = "Reminder: You can not save a customer, whether its new or an existing customer, unless you provide a value.";
                    
                    //Reset all inputs we'd display later
                    if(!$isLoaded){
                        $name = null;
                        $address = null;
                        $area = null;
                    }
                }
                else{
                    //Check what mode we're in, assuming that we're not deleting a customer.
                    if ($isLoaded){//Then we are updating an entry we decided to edit
                        //Prepare dataStorage to edit data
                        $dataStorage[searchArray($dataStorage, $loadEntry)][0] = $loadEntry; //Holds the loadEntry generated from the assignment block above
                        $dataStorage[searchArray($dataStorage, $loadEntry)][1] = $name; //Holds the input for the customer name
                        $dataStorage[searchArray($dataStorage, $loadEntry)][2] = $address; //Holds the input for the customer address
                        $dataStorage[searchArray($dataStorage, $loadEntry)][3] = $area; //Holds the input for the customer area
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
                                $endMessage = "Last saved memory of customer " . $loadEntry . " loaded";
                            }
                            else{
                                $endMessage = "Customer " . $loadEntry . " updated";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with updating customer " . $loadEntry . ".";
                        }
                    }
                    else { //We are creating a new customer
                        try{//Check if we need to pull a reset when saving data
                            if ($reset || !file_exists(CustomerLink)){
                                $file = fopen($link, "w");
                                
                                //With the file reset, we need to reinitialize its header
                                fputcsv($file, CustomerHeader);
                            }
                            else{
                                $file = fopen($link, "a");
                            }
                        
                            //save the line
                            fputcsv($file, array($loadEntry, $name, $address, $area));
                        
                            //Close the file
                            fclose($file);
                        
                            //Let the user know that the deed is done depending on the command provided
                            if ($reset){
                                $endMessage = "Customer Data file reset, now there's only one customer entry remaining.";
                            }
                            else{
                                $endMessage = "A Customer was added to the Customer Data file.";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with creating a new customer";
                        }
                    }
                }   
            }
        
            //Recalibrate loadEntry so it has a unique ID.
            if (!$isLoaded){
                if(file_exists($link)){
                    try{
                        //Load the contents of customers.csv and add them to dataStorage
                        $dataStorage = loadFile($link);
                        
                        //Use $dataStorage in numericIDGenerator to generate a unique ID
                        $loadEntry = numericIDGenerator(1, "customer", $dataStorage);
                    }
                    catch (IOException $error){ //In case we don't have a file to refer to when adding a new entry
                        $footerWarning .= "<br>There may be a repeated ID if you save the file again";
                    }
                }
                else{
                    //Return $loadEntry to its default value
                    $loadEntry = "CC-000001";
                }
            }
        
            //The introduction depends on whether the page was loaded or not
            if ($isLoaded){
                echo "<h2>Edit " . $name . "</h2>";
                echo "<p>If you need to change the name, address, or area of this customer, you can do so below.<br>";
                echo "Alternatively, you can also delete the entry or enter data entry mode.</p>";
            }
            else{
                echo "<h2>Create a Customer</h2>";
                echo "<p>Enter a customer name, address, and area and we'll add it to our list of customers<br>";
                echo "It is in your best interests to fill all fields, except for new area unless you want to make a new area<br>";
                echo "You can also choose to restart that list or enter edit mode</p>";
            }
        ?>
        
        <!-- Input Form -->
        <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
            <label for="customer">Customer name:</label>
            <input type="text" id="customer" name="customer" value="<?php echo $name; ?>">
            <br>
            <label for="home">Customer Address:</label>
            <input type="text" id="home" name="home" value="<?php echo $address; ?>">
            <br>
            <!-- Area is a field that requires two inputs to fill, one for a pre-existing area, and another for a new one if the user wants to create one, we start with the pre-existing data list -->
            <label for="area">Customer Area:</label>
            <select id=area name=area>
                <?php
                    //The contents of this list will be held in the following array.
                    $areaList = array();
                
                    //It's initialized with the values in the preset areas function
                    for ($track = 0; $track < 17; $track++){
                        $areaList[$track] = presetAreas($track);
                    }
                
                    //Since we have $dataStorage lying around after using it to find an ID, we can use it to determine if there are any areas we need to add to $areaList
                    for ($track = 1; $track < count($dataStorage); $track++){
                        //Variables to help check for repeat areas
                        $noRepeat = true;
                        $areaTrack = 0;
                        
                        //We have to check if the areas in $dataStorage match the areas in $areaList, if dataStorage presents a unique area, add it to the list
                        while ($noRepeat && $areaTrack < count($areaList)){
                            if($dataStorage[$track][3] == $areaList[$areaTrack]){
                                $noRepeat = false;
                            }
                            else{
                                $areaTrack++;
                            }
                        }
                        
                        //If $noRepeat is still true, then $areaTrack is equal to the number of elements stored in $areaList, and this number is the element number of the next empty array!
                        if($noRepeat){
                            $areaList[$areaTrack] = $dataStorage[$track][3];
                        }
                    }
                
                    //With all values in $arrayList initialized, use a foreach loop to create the options for the select list
                    foreach ($areaList as $options){
                        echo "<option value=\"" . $options . "\">" . $options . "</option>";
                    }
                
                    //There is one more option, but since it's not attatched to any value it can exist outside of PHP
                ?>
                <option>New Area</option>
            </select>
            <br>
            <!-- The following prompt asks the user to name a new area-->
            <label for="newArea">Name of New Area (if using the New Area Option from the options list above</label>
            <input type="text" name="newArea">
            <!-- I had to save the data in the comment block above to make the data below function correctly -->
            <input type="text" id="entry" name="entry" value=<?php echo $loadEntry?> hidden>
            <!-- Other hidden values -->
            <input type="text" name="source" value=<?php echo $link; ?> hidden>
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
                echo "<label for=reset>Overwrite the current customer records file? </label>";
                echo "<select id=reset name=reset>";
                    echo "<option value=false>No</option>";
                    echo "<option value=true>Yes</option>";
                echo "</select><br>";
            }
            ?>
            
            <!-- This section is where the submit buttons live -->
            <button type="submit">Save Customer</button>
            <?php
            //Give the user the choice to delete the entry if they're in data editing mode
            if ($isLoaded){
                echo "<button type=submit formaction=fileLoaded.php id=delete name=delete value=true>Delete Data Entry</button>";
                /*The original simpleSave stored $loadEntry and $link here, but while $loadEntry can migrate over to file loaded for use of the same name, $link is known as from, so it will hide right here.
                  fileLoaded.php also requires dataFile, so we'll just sent customer its way.
                */
                echo "<input type=text name=dataFile value=customer hidden>";
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
            <input type="text" name="dataFile" value="customer" hidden>
            <input type="text" name="from" value="<?php echo $link; ?>" hidden>
            <input type="text" name="delete" value="false" hidden>
            <input type="text" name="priorLink" hidden value="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <br>
            <br>
            <?php
            //The rest of the forms buttons depend on the mode the page is in.
            if ($isLoaded){ //Edit mode
                echo "<button type=submit formaction=fileLoaded.php>Return to the Load Results</button><br><br>";
                echo "<button type=submit formaction=loadFile.php>Return to the Customer File Select Menu</button><br><br>";
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