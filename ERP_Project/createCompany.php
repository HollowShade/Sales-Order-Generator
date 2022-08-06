<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Create A Company</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>

    <body>
        <?php require 'Scripts/functions.php';
            //These variables are for the creating part of the application, if not both of its modes
            $name = null;
            $reset = false;
            $noName = false;
            $endMessage = "Reminder: It's easier to create a set of data from a pre-made list of names than from scratch!";
            $footerWarning = "";
        
            //Variables for the editing part of the application
            $link = CompanyLink;
            $isLoaded = false;
            $loadEntry = "AA";
            $file = null;
            $dataStorage[0][0] = null;
            $first = false;
        
            //Check if there's a valid input
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
                //Determine if the page was loaded
                $isLoaded = inputSecurity($_POST["edit"]);
                
                //Check source for a value. If it's not there or the file related to it doesn't exist, set it to the default value
                $link = inputSecurity($_POST["source"]);
                if (!file_exists($link)){
                    $link = CompanyLink;
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
                
                //Assigning a value to the company name
                if (empty($_POST["company"])){ //If the user hasn't provided an input
                    if ($isLoaded){
                        $name = $dataStorage[searchArray($dataStorage, $loadEntry)][1];
                        $first = true;
                    }
                    else{
                        $noName = true;
                    }
                }
                else{
                    $name = inputSecurity($_POST["company"]);
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
                    $loadEntry = "AA"; //This marks the latest entry as the first in a new set of companies
                }
                else {
                    //Incase if we didn't get data from a file, get it from the page.
                    $loadEntry = inputSecurity($_POST["entry"]);
                }
                
                
                //Save the data before preparing $loadEntry
                if ($noName){ //Error Message!
                    $endMessage = "Reminder: You can not create a company unless if you give it a name";
                }
                else{
                    //Check what mode we're in, assuming that we're not deleting a company.
                    if ($isLoaded){//Then we are updating an entry we decided to edit
                        //Prepare dataStorage to edit data
                        $dataStorage[searchArray($dataStorage, $loadEntry)][0] = $loadEntry; //Holds the loadEntry generated from the assignment block above
                        $dataStorage[searchArray($dataStorage, $loadEntry)][1] = $name; //Holds the input for the company name
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
                            if ($first) {
                                //Edit mode introduction and error message
                                $endMessage = "Last saved memory of company " . $loadEntry . " loaded.  Remember to provide a name to save the entry.";
                            }
                            else{
                                $endMessage = "Company name updated";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with updating company " . $loadEntry . "'s name.";
                        }
                    }
                    else { //We are creating a new company,
                        try{//Check if we need to pull a reset when saving data
                            if ($reset || !file_exists(CompanyLink)){
                                $file = fopen($link, "w");
                                
                                //With the file reset or being initially created, we need to reinitialize its header
                                fputcsv($file, CompanyHeader);
                            }
                            else{
                                $file = fopen($link, "a");
                            }
                        
                            //save the line
                            fputcsv($file, array($loadEntry, $name));
                        
                            //Close the file
                            fclose($file);
                        
                            //Let the user know that the deed is done depending on the command provided
                            if ($reset){
                                $endMessage = "Company Data file reset, now there's only one company entry remaining.";
                            }
                            else{
                                $endMessage = "A Company was added to the Company Data file.";
                            }
                        }
                        catch (IOException $error){
                            $endMessage = "There was an issue with creating a new company";
                        }
                    }
                }  
            }
        
            //Recalibrate loadEntry so it has a unique letter ID.
                if (!$isLoaded){
                    if(file_exists($link)){
                        try{
                            //Open the file
                            $file = fopen($link, "r");
                            
                            //Store the values in a temporary array, since we're in entry mode, it can be dataStorage
                            $track = 0;
                            while (!feof($file)){
                                $read = fgetcsv($file, 500, ",");
                                if (!empty($read)){ //Use the 3D array dataStorage[x][y] as a 2D array by keeping x at a constant 0, then have it hold all of the company IDs 
                                    $dataStorage[0][$track] = $read[0];
                                    $track++;
                                }
                            }
                            
                            //Close the file
                            fclose($file);
                            
                            //Use the 3D array gone 2D in IDGenerator to generate a unique ID
                            $loadEntry = IDGenerator($dataStorage[0]);
                        }
                        catch (IOException $error){ //In case we don't have a file to refer to when adding a new entry
                            $footerWarning .= "<br>There may be a repeated ID if you save the file again";
                        }
                    }
                    else{
                        //Return $loadEntry to its default value
                        $loadEntry = "AA";
                    }
                }
            
            //The introduction depends on whether the page was loaded or not
            if ($isLoaded){
                //We need loadEntry to use this title
                echo "<h2>Edit " . $name . "</h2>";
                echo "<p>If you need to change the name of this company, you can do so below.<br>";
                echo "Alternatively, you can also delete the entry or enter data entry mode.</p>";
            }
            else{
                echo "<h2>Create a Company</h2>";
                echo "<p>Enter a company name and we'll add it to our list of companies<br>";
                echo "You can also choose to restart that list or enter edit mode</p>";
            }
        ?>
        
        <!-- Input Form -->
        <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
            <label for="company">Company name:</label>
            <input type="text" id="company" name="company" value="<?php echo $name; ?>">
            <!-- I had to save the data in the comment block above to make the data below function correctly -->
            <input type="text" id="entry" name="entry" value=<?php echo $loadEntry?> hidden>
            <!-- Other hidden values -->
            <input type="text" name="source" value=<?php echo $link; ?> hidden>
            <input type="text" name="edit" hidden value="<?php echo strval($isLoaded); ?>">
            <br>
            
            <?php
            //Give the user the choice to reset the file if they're in data entry mode
            if ($isLoaded){
                //If we're in edit mode, keep $reset set to false
                echo "<input type=text name=reset value=false hidden>";
            }
            else{
                //This is where the option presents itself
                echo "<label for=reset>Overwrite the current company records file? </label>";
                echo "<select id=reset name=reset>";
                    echo "<option value=false>No</option>";
                    echo "<option value=true>Yes</option>";
                echo "</select><br>";
            }
            ?>
            
            <!-- This section is where the submit buttons live -->
            <button type="submit">Save Company</button>
            <?php
            //Give the user the choice to delete the entry if they're in data editing mode
            if ($isLoaded){
                echo "<button type=submit formaction=fileLoaded.php id=delete name=delete value=true>Delete Data Entry</button>";
                /*The original simpleSave stored $loadEntry and $link here, but while $loadEntry can migrate over to file loaded for use of the same name, $link is known as from, so it will hide right here.
                  fileLoaded.php also requires dataFile, so we'll just sent company its way.
                */
                echo "<input type=text name=dataFile value=company hidden>";
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
            <input type="text" name="dataFile" value="company" hidden>
            <input type="text" name="from" value="<?php echo $link; ?>" hidden>
            <input type="text" name="delete" value="false" hidden>
            <br>
            <br>
            <?php
            //The rest of the forms buttons depend on the mode the page is in.
            if ($isLoaded){ //Edit mode
                echo "<button type=submit formaction=fileLoaded.php>Return to the Load Results</button><br><br>";
                echo "<button type=submit formaction=loadFile.php>Return to the Company File Select Menu</button><br><br>";
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
            echo $footerWarning;
        ?>
        
    </body>
    
</html>