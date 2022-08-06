<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Create/Edit Sales Order Data</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <?php require 'Scripts/functions.php';
            //Load the variables that we need
            $companyLink = CompanyLink;
            $customerLink = CustomerLink;
            $productLink = ProductLink;
            $salesLink = null;
            $save = null;
            $menuMessage = "<br>";
            $endMessage = "<br>";
        
            //There are times when this page can take a long time, so give it as long as it needs to load with the following line of code
            set_time_limit(0);
        
            //Check if we're using the post method
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                //Find the value of salesLink by checking if we have an input from source
                if (!empty($_POST["source"]) && file_exists(inputSecurity($_POST["source"]))){
                    $salesLink = inputSecurity($_POST["source"]);
                }
                else{
                    $salesLink = SalesOrderLink;
                }
                
                /* If the $salesLink leads to the local sales order file, Check if $companyLink, $customerLink, and $productLink lead to files,
                   If $salesLink isn't the link to the local sales order file, we only need to check that file.*/
                if (($salesLink == SalesOrderLink && !file_exists($companyLink)) || ($salesLink == SalesOrderLink && !file_exists($customerLink)) || ($salesLink == SalesOrderLink && !file_exists($productLink))){
                    echo "<h2><b>ERROR:</b> Prerequisite files not found</h2>";
                    echo "<p>It seems that you have forgot to create the following files before attempting to create a sales order:</p>";
                
                    //Tell them the missing files
                    if (!file_exists($companyLink)){
                        echo $companyLink . "<br>";
                    }
                    if (!file_exists($customerLink)){
                        echo $customerLink . "<br>";
                    }
                    if (!file_exists($productLink)){
                        echo $productLink . "<br>";
                    }
                
                    //Tell the user to return to the main menu and create the missing files (Set Menu introduction)
                    $menuMessage .= "Please go back to the main menu and create the files before attempting to do anything related to sales orders.<br>";
                }
                else{
                    //Determine the value of $save
                    $save = inputSecurity($_POST["saved"]);
                    
                    //Create file references, but don't assign their values.
                    $companyReference = null;
                    $customerReference = null;
                    $productReference = null;
                    $salesReference = null;
                    
                    //Check if salesLink exists and if it's not the local file before determining which references to use and initialize
                    if (file_exists($salesLink) && $salesLink != SalesOrderLink){ //All we'll need from here on in is salesReference
                        $salesReference = loadFile($salesLink);
                        
                        //This is a temporary storage for customerReference
                        $tempArray = null;
                        
                        //But we could save code if we used sales reference to initialize the other references, starting with $companyReference
                        foreach($salesReference as $sales){
                            if ($companyReference == null){
                                $companyReference = array(array($sales[1], $sales[2]));
                            }
                            else {
                                $match = searchArray($companyReference, $sales[1]);
                                if ($match == count($companyReference)){
                                    $companyReference[$match] = array($sales[1], $sales[2]);
                                }
                            }
                            
                            //Next will be $productReference
                            if ($productReference == null){
                                $productReference = array(array($sales[7], $sales[8]));
                            }
                            else {
                                $match = searchArray($productReference, $sales[7]);
                                if ($match == count($productReference)){
                                    $productReference[$match] = array($sales[7], $sales[8]);
                                }
                            }
                            
                            //Finally, tempArray, the presorted customerReference
                            if ($tempArray == null){
                                $tempArray = array(array($sales[5], $sales[6]));
                            }
                            else{
                                $match = searchArray($tempArray, $sales[5]);
                                if ($match == count($tempArray)){
                                    $tempArray[$match] = array($sales[5], $sales[6]);
                                }
                            }
                        }
                        
                        //Give customerReference the values stored in tempArray, then destroy the array.
                        for ($track = 0; $track < count($tempArray); $track++){
                            $customerReference[$track] = array($tempArray[$track][0], "Customer Name", "Customer Address", $tempArray[$track][1]);
                        }
                        $tempArray = null;
                        
                        //Now we need to sort our references, then they'll be ready for use.
                        $companyReference = sort3DArray($companyReference, 0);
                        $customerReference = sort3DArray($customerReference, 0);
                        $productReference = sort3DArray($productReference, 0);
                    }
                    else { //The other three files give the local system a lot of options to choose from, use them 
                        $companyReference = loadFile($companyLink);
                        $customerReference = loadFile($customerLink);
                        $productReference = loadFile($productLink);
                        
                        //Now, lets check if we actually have a salesReference
                        if (file_exists($salesLink)){
                            //We have a salesReference, let's load it
                            $salesReference = loadFile($salesLink);
                        }
                        else {
                            //No salesReference, that's okay, we'll just store the sales order headers into the reference.
                            $salesReference = array(SalesOrderHeader);
                        }
                    }
                    
                    //Check the second entries stored in each reference, if there's one that's empty, set save to "referenceless" and don't check the other references
                    if (count($companyReference) == 1 && $save != "referenceless"){
                        $save = "referenceless";
                    }
                    
                    if (count($customerReference) == 1 && $save != "referenceless"){
                        $save = "referenceless";
                    }
                    
                    if (count($productReference) == 1 && $save != "referenceless"){
                        $save = "referenceless";
                    }
                
                    //If save is set to select, we need to determine where to edit or create data before doing so
                    if ($save == "select"){
                        //With a mode established, introduce the user to the page
                        echo "<h2>Select Sales Order Entry</h2>";
                        echo "<p>Select identifiers from the menus provided below and we'll open up the sales order file's directory on those identifiers</p>";
                        echo "<p>If the identifier combination doesn't connect to a data entry, we'll create a new one.</p>";
                    
                        //Create a variable for tracking the number that's one higher than the number of months and days in the system
                        $time = 1;
                    
                        //Create an input form for the user to specify which sales order entry the user wishes to create or modify
                        echo "<form method=post action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                            //Display company Options
                            echo "<label for=company>Select a Company:</label>";
                            echo "<select id=company name=company>";
                                //Use a loop to populate the select menu
                                foreach($companyReference as $companies){
                                    //Check the values in company reference to make sure they're not headers before listing them as an option
                                    if ($companies[0] != "Company ID"){
                                        //We need to know if there's a value in company name so we don't end up posting an unprovided name
                                        if ($companies[1] != "Unprovided" && $companies[1] != null){
                                            echo "<option value=" . $companies[0] . ">" . $companies[1] . "</option>";
                                        }
                                        else { //Bad name? Display the ID instead
                                            echo "<option value=" . $companies[0] . ">" . $companies[0] . "</option>";
                                        }
                                    }
                                }
                            echo "</select>";
                            //Display customer options
                            echo "<br><label for=customer>Select a customer:</label>";
                            echo "<select id=customer name=customer>";
                                //Use a loop to populate the select menu
                                foreach ($customerReference as $customers){
                                    //Check the values in customer reference to make sure they're not headers before listing them as an option
                                    if ($customers[0] != "Customer ID"){
                                        echo "<option value=" . $customers[0] . ">" . $customers[0] . "</option>";
                                    }
                                }
                            echo "</select>";
                            //Display product options
                            echo "<br><label for=product>Select a product:</label>";
                            echo "<select id=product name=product>";
                                //Use a loop to populate the select menu
                                foreach ($productReference as $products){
                                    //Check the values in customer reference to make sure they're not headers before listing them as an option
                                    if ($products[0] != "Product ID"){
                                        //We need to know if there's a value in product name so we don't end up posting an unprovided name
                                        if ($products[1] != "Unprovided" && $products[1] != null){
                                            echo "<option value=" . $products[0] . ">" . $products[1] . "</option>";
                                        }
                                        else { //Bad name? Display the ID instead
                                            echo "<option value=" . $products[0] . ">" . $products[0] . "</option>";
                                        }
                                    }
                                }
                            echo "</select>";
                            //Display month options
                            echo "<br><label for=month>Select a round/month:</label>";
                            echo "<select id=month name=month>";
                                //Check the value of salesReference. If it's populated, use it in a loop to populate the select menu
                                if ($salesReference != null){
                                    //Find the highest moth value in salesReference
                                    $maxMonth = 0;
                                    foreach ($salesReference as $check){
                                        if ($maxMonth < $check[3]){
                                            $maxMonth = $check[3];
                                        }
                                    }
                                
                                    //Use a loop to popluate the select menu
                                    for ($track = 1; $track <= $maxMonth; $track++){
                                        echo "<option value=" . $track . ">" . $track . "</option>";
                                        
                                        //Increase time to it's value doesn't repeat track
                                        $time++;
                                    }
                                }
                    
                                //Regardless, have an option that uses $time as a value and reset $time to 1
                                echo "<option value=" . $time . ">New Month</option>";
                                $time = 1;
                            echo "</select>";
                            //Display day options
                            echo "<br><label for=day>Select a step/day in the round/month:</label>";
                            echo "<select id=day name=day>";
                                //Check the value of salesReference. If it's populated, use it in a loop to populate the select menu
                                if ($salesReference != null){
                                    //Find the highest moth value in salesReference
                                    $maxDay = 0;
                                    foreach ($salesReference as $check){
                                        if ($maxDay < $check[4]){
                                            $maxDay = $check[4];
                                        }
                                    }
                                
                                    //Use a loop to popluate the select menu
                                    for ($track = 1; $track <= $maxDay; $track++){
                                        echo "<option value=" . $track . ">" . $track . "</option>";
                                        
                                        //Increase time to it's value doesn't repeat track
                                        $time++;
                                    }
                                }
                    
                                //Regardless, have an option that uses $time as a value
                                echo "<option value=" . $time . ">New Day</option>";
                            echo "</select>";
                            //A submit button and a value containing the link to this page for future ID purposes
                            echo "<br><button type=submit name=saved value=false>Open Sales Order Entry</button>";
                            echo "<input type=text name=priorLink hidden value=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                            echo "<input type=text name=source hidden value=" . $salesLink . ">";
                        echo "</form><br>";
                    
                        //Set Menu Introduction
                        $menuMessage .= "If you would like to return to the main menu, you can do so with one of the links below.<br>";
                    }
                    elseif ($save == "false" || $save == "true"){
                        //And now the sales order identifier becomes relevant
                        $identity = null;
                    
                        //These variables are the values that the user can modify
                        $price = 0;
                        $sales = 0;
                        $profit = 0;
                        $cost = 0;
                    
                        //And these variables are for saving purposes
                        $lastPage = null;
                        $slot = 0;
                    
                        //Get the value of lastPage, we'll use to determine how to load the IDs
                        $lastPage = inputSecurity($_POST["priorLink"]);
                    
                        //Assign values to the IDs based on the population of entry
                        if (!empty($_POST["entry"])){
                            $identity = inputSecurity($_POST["entry"]);
                            
                            //Set the page's end message
                            $endMessage .= "<br>Sales Order " . $identity . " loaded.";
                        }
                        else {
                            //Finding the sales order ID will require the other identifiers, which should be provided if the entry wasn't
                            $companyID = inputSecurity($_POST["company"]);
                            $customerID = inputSecurity($_POST["customer"]);
                            $productID = inputSecurity($_POST["product"]);
                            $month = intval(inputSecurity($_POST["month"]));
                            $day = intval(inputSecurity($_POST["day"]));
                            
                            //Run a loop to find the sales order ID.
                            foreach ($salesReference as $salesOrders){
                                if ($salesOrders[1] == $companyID && $salesOrders[3] == $month && $salesOrders[4] == $day && $salesOrders[5] == $customerID && $salesOrders[7] == $productID){
                                    $identity = $sales[0];
                                    break;
                                }
                            }
                            
                            //If we didn't get an identifier from the loop, generate a new one
                            if ($identity == null){
                                $identity = numericIDGenerator(1, "salesOrder", $salesReference);
                            }
                            
                            //Set the page's end message
                            $endMessage .= "<br>Sales Order " . $identity . " created.";
                        }
                        
                        //Declare name values before initializing them
                        $companyName = null;
                        $productName = null;
                        $customerArea = null;
                    
                        //Find the values of price, sales, profit, cost, and the other identifiers in the sales order entry with the identifier we found
                        if (searchArray($salesReference, $identity) != count($salesReference)){
                            $companyID = $salesReference[searchArray($salesReference, $identity)][1];
                            $companyName = $salesReference[searchArray($salesReference, $identity)][2];
                            $customerID = $salesReference[searchArray($salesReference, $identity)][5];
                            $customerArea = $salesReference[searchArray($salesReference, $identity)][6];
                            $productID = $salesReference[searchArray($salesReference, $identity)][7];
                            $productName = $salesReference[searchArray($salesReference, $identity)][8];
                            $month = $salesReference[searchArray($salesReference, $identity)][3];
                            $day = $salesReference[searchArray($salesReference, $identity)][4];
                            $price = $salesReference[searchArray($salesReference, $identity)][9];
                            $sales = $salesReference[searchArray($salesReference, $identity)][10];
                            $profit = $salesReference[searchArray($salesReference, $identity)][11];
                            $cost = $salesReference[searchArray($salesReference, $identity)][12];
                        }
                        
                        //Some parts of data creation/modification require the page to truely save data
                        if ($save == "true"){
                            //Reset $endMessage so we don't get a create or load message
                            $endMessage = "<br>";
                            
                            //Get the input for $price, $sales, $profit, and $cost, starting with price
                            $price = floatval(inputSecurity($_POST["prices"]));
                            
                            //If price was set to 0, notify the user that we're using the default price value of 0.
                            if ($price == 0){
                                $endMessage .= "<br>Notice: Product price is at a value of $0.00. If you don't want this entry, you can delete it. Otherwise, increase the product price or feel awkward for offering it for free.";
                            }
                            
                            //Sales is important because if no items were sold, the existence of the entry would be questionable, so check if it has a value before replacing it.
                            if (!empty ($_POST["sales"])){
                                $sales = intval(inputSecurity($_POST["sales"]));
                            }
                            else{
                                //Notify the user that they attempted to use 0.
                                $endMessage .= "<br>Error: An attempt to set Products sold to 0 was detected. If a sale reports no products sold, the revenue isn't from sales and the cost isn't from sales, it's from something else entirely.<br>Products sold was returned to its last known value. If you don't want any products sold, you're better off deleting the entry, because we don't want to use this file as an accountant's debit/credit sheet.";
                            }
                            
                            //Load the value in $profit
                            $profit = floatval(inputSecurity($_POST["profit"]));
                            
                            //If profit was set to 0, notify the user that we're using the default price value of 0.
                            if ($profit == 0){
                                $endMessage .= "<br>Notice: The profit from sales is at a value of $0.00. If you don't want this entry, you cn delete it. Otherwise, increase the profits from sales or enjoy having a sale that leaves a deficit on company " . $companyID . "!";
                            }
                            
                            //Load the value in $cost
                            $cost = floatval(inputSecurity($_POST["cost"]));
                            
                            //If cost was set to 0, notify the user that we're using the default price value of 0.
                            if ($cost == 0){
                                $endMessage .= "<br>Notice: The cost from loss of inventory is at a value of $0.00. If you don't want this entry, you can delete it. Otherwise, increase the costs of inventory loss or enjoy being unrealistic!";
                            }
                        }
                        
                        //Company name and product name might have bad values like null and unprovided. Set them to their IDs if they have bad values.
                        if ($companyName == null || $companyName == "Unprovided"){
                            $companyName = $companyID;
                        }
                        
                        if ($productName == null || $productName == "Unprovided"){
                            $productName = $productID;
                        }
                    
                        //Introduce the user to the page
                        echo "<h2>Modify Sales Order Data Entry " . $identity . "</h2>";
                        echo "<p>Use the input fields below to change how much " . $productName . " " . $companyName . " sold to customer " . $customerID . " on step/day " . $day . " of round/month " . $month . " of the simulation.</p>";
                        echo "<p>You can also change the profits and costs (in inventory) of this transaction, along with the price per product (we won't update the product file though).</p>";
                        echo "<p>While we'd recommend filling all areas, you don't have to do it (we're prepared to cover your shortcomings).</p>";
                        echo "<p>When you're done, you can hit the save button to save the entry, or any other link to leave the entry as it was. Unless it's the delete button, which deletes the entry you're working on.</p>";
                        echo "<p>Ultimately, what you enter or don't enter is on you.</p>";
                        
                        //Of course, we haven't saved the form yet, so set company and product name to unprovided if their names match their ID
                        if ($companyName == $companyID){
                            $companyName = "Unprovided";
                        }
                        
                        if ($productName == $productID){
                            $productName = "Unprovided";
                        }
                    
                        //Input form
                        echo "<form method=post action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                            echo "<br><label for=prices>Price of each product (Excluding Sales):</label>";
                            echo "<input type=text name=prices value=" . $price . ">";
                            echo "<br><label for=sales>Number of Products sold:</label>";
                            echo "<input type=number name=sales value=" . $sales . ">";
                            echo "<br><label for=profit>Money Earned From Sales:</label>";
                            echo "<input type=text name=profit value=" . $profit . ">";
                            echo "<br><label for=cost>Money lost from loss of inventory:</label>";
                            echo "<input type=text name=cost value=" . $cost . ">";
                        
                            //Hidden inputs
                            echo "<input type=text name=entry hidden value=" . $identity . ">";
                            echo "<input type=test name=source hidden value=" . $salesLink . ">";
                            echo "<input type=text name=priorLink hidden value=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                    
                            //Submit buttons, one to save and one to delete
                            echo "<br><button type=submit name=saved value=true>Save Sales Order Entry</button>";
                            echo "<button type=submit formaction=fileLoaded.php id=delete name=delete value=true>Delete Sales Order Entry</button>";
                    
                            //Hidden values for traveling to fileLoaded
                            echo "<input type=text name=dataFile hidden value=salesOrder>";
                            echo "<input type=text name=from hidden value=" . $salesLink . ">";
                        echo "</form>";
                    
                        //Set menu introduction
                        $menuMessage .= "<br><br>If you would like to return to the main menu, the load results, the file select page, or even back to entry select mode, you can do so with one of the links below.<br>";
                        
                        //Okay, time to save an entry, regardless of completion level. Create an entry for the sales order table
                        $dataStorage = array($identity, $companyID, $companyName, $month, $day, $customerID, $customerArea, $productID, $productName, $price, $sales, $profit, $cost);
                            
                        //Use the searchArray function to determine what row to replace, or if we need to create a new row.
                        if (searchArray($salesReference, $identity) == count($salesReference)){
                            //We'll need to create a new data row, but it's going to take some data management to do so, so here's a bunch of code that will help manage data, starting with the variables
                            $beforeArray = array();
                            $afterArray = array();
                            $ahead = 1;
            
                            //And now, here's a big loop for sorting sales orders
                            for ($track = 0; $track < count($salesReference); $track++){
                                //Check the foreign identifiers for null, if they're all null, an entry was replaced, and we need to end this loop.
                                if ($dataStorage[1] != null || $dataStorage[5] != null || $dataStorage[7] != null){
                                    $beforeArray = $salesReference[$track];
                                    //If you're at the end of the track, add the data storage array to the end of the track
                                    if (($ahead) == count($salesReference)){
                                        $salesReference[$ahead] = $dataStorage;
                                    }
                                    else {
                                        //Give the after array a new array and proceed with the program as usual
                                        $afterArray = $salesReference[$ahead];

                                        //Determine file placement. Step one: compare the companies of data storage and after (check value 1, company ID)
                                        if ($dataStorage[1] != $afterArray[1]){
                                            //Mismatched IDs, is data storage at the end of a company list?
                                            if($dataStorage[1] == $beforeArray[1]){
                                                //It is at the end of a list! Add data storage to the salesReference and turn after into data storage
                                                $salesReference[$ahead] = $dataStorage;
                                                $dataStorage = $afterArray;
                                            }
                                            else{
                                                //It's not, either data storage's company appears later, or it's a new company.
                                                $salesReference[$ahead] = $afterArray;
                                            }
                                        }
                                        else{
                                            //They have the same ID, Now for step two: compare the rounds/months of data storage and after (check value 3, round/month)
                                            if ($dataStorage[3] < $afterArray[3]){
                                                //Data storage comes before after! Add data storage to the salesReference and turn after into data storage
                                                $salesReference[$ahead] = $dataStorage;
                                                $dataStorage = $afterArray;
                                            }
                                            elseif ($dataStorage[3] > $afterArray[3]){
                                                //After comes before data storage. Add after to the salesReference
                                                $salesReference[$ahead] = $afterArray;
                                            }
                                            else {
                                                //They occur in the same month/round, Now for step three: compare the steps/days of data storage and after (check value 4, step)
                                                if ($dataStorage[4] < $afterArray[4]){
                                                    //Data storage comes before after! Add data storage to the salesReference and turn after into data storage
                                                    $salesReference[$ahead] = $dataStorage;
                                                    $dataStorage = $afterArray;
                                                }
                                                elseif ($dataStorage[4] > $afterArray[4]){
                                                    //After comes before data storage. Add after to the salesReference
                                                    $salesReference[$ahead] = $afterArray;
                                                }
                                                else {
                                                    //They occur on the same day/step, Now for step four: compare the customers of data storage and after (check value 5, customerID)
                                                    if ($dataStorage[5] < $afterArray[5]){
                                                        //Data storage comes before after! Add data storage to the salesReference and turn after into data storage
                                                        $salesReference[$ahead] = $dataStorage;
                                                        $dataStorage = $afterArray;
                                                    }
                                                    elseif ($dataStorage[5] > $afterArray[5]){
                                                        //After comes before data storage. Add after to the salesReference
                                                        $salesReference[$ahead] = $afterArray;
                                                    }
                                                    else {
                                                        //They have the same ID, Now for the final step: compare the products of data storage and after (check value 7, productID)
                                                        if ($dataStorage[7] < $afterArray[7]){
                                                            //Data storage comes before after! Add data storage to the salesReference and turn after into data storage
                                                            $salesReference[$ahead] = $dataStorage;
                                                            $dataStorage = $afterArray;
                                                        }
                                                        elseif ($dataStorage[7] > $afterArray[7]){
                                                            //After comes before data storage. Add after to the salesReference
                                                            $salesReference[$ahead] = $afterArray;
                                                        }
                                                        else {
                                                            /*If data storage and after share the same IDs, add data storage to the salesReference, but don't turn after into data storage. 
                                                            Instead, mark data storage for deletion. This replaces after with data storage without altering future files, which we'll assume is the intent of the system
                                                            DataStorage should have the ID of after before we add it to salesReference*/
                                                            $dataStorage[0] = $afterArray[0];
                                                            $salesReference[$ahead] = $dataStorage;
                                                            $dataStorage = array($identity, null, $companyName, $month, $day, null, $customerArea, null, $productName, $price, $sales, $profit, $cost);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        //Increase ahead
                                        $ahead++;
                                    }
                                }
                                else {
                                    //End the loop, we replaced a value.
                                    break;
                                }
                            }
                        }
                        else {
                            //A row is getting replaced
                            $salesReference[searchArray($salesReference, $identity)] = $dataStorage;
                        }
                            
                        //Try to save the file
                        try{
                            //Open the file
                            $file = fopen($salesLink, "w");
                                
                            //Save changes to the data list
                            foreach($salesReference as $sells){
                                fputcsv($file, $sells);
                            }
                                
                            //Close the file
                            fclose($file);
                                
                            //If $save was set to "true", notify the user of a successful save
                            if ($save == "true"){
                                $endMessage .= "<br>Sales Order " . $identity . " saved successfully.";
                            }
                        }
                        catch (IOException $error){
                            //Notify the user of a failed save
                            $endMessage .= "<br>An error has occurred, Sales Order " . $identity . " was not saved.";
                        }   
                    }
                    elseif ($save == "referenceless"){
                        echo "<h2><b>ERROR:</b> Empty Reference Files Detected</h2>";
                        echo "<p>It seems that the following files have empty references:</p>";

                        //Tell them the missing files
                        if (count($companyReference) == 1){
                            echo $companyLink . "<br>";
                        }
                        if (count($customerReference) == 1){
                            echo $customerLink . "<br>";
                        }
                        if (count($productReference) == 1){
                            echo $productLink . "<br>";
                        }

                        //Tell the user to return to the main menu and create the missing files (Set Menu introduction)
                        $menuMessage .= "If you're seeing this error message with data you've uploaded from the load page, you'll need to reupload the file you were working on.<br>Otherwise, you'll just need to add another entry one of the files listed above.<br>Either way, you'll have to go back to the main menu to get te job done.<br>";
                    }
                }
            }
            else {
                echo "<h2><b>ERROR:</b> Bad Gateway</h2>";
                echo "It seems that you didn't travel to this page using a link from the program.<br>";
                
                //Tell the user to return to the main menu and create the missing files (Set Menu introduction)
                $menuMessage .= "Please go back to the main menu and try accessing this page with a link from the program.<br>";
            }
        
            //Display the menu message before displaying the links form
            echo $menuMessage;
        ?>
        
        <!-- Links form -->
        <form method="POST">
            <br>
            <button type="submit" formaction="index.php">Return to the Main Menu</button>
            <!-- The following are hidden values that are always present for the sake of upload bloat management -->
            <input type="text" name="from" hidden value="<?php echo $salesLink; ?>">
            <input type="text" name="dataFile" hidden value="salesOrder">
            <br>
            <!-- Every other button only appears if we're editing data -->
            <?php 
                if ($save == "false" || $save == "true"){
                    //Return to entry selection
                    echo "<br><button type=submit formaction=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " name=saved value=select>Select another entry by ID</button><br>";
                    //Return to load results
                    echo "<br><button type=submit formaction=fileLoaded.php>Return to the load results</button><br>";
                    //Return to file select
                    echo "<br><button type=submit formaction=loadFile.php>Return to the file select menu</button><br>";
                
                    //Hidden values
                    echo "<input type=text name=source hidden value=" . $salesLink . ">";
                    echo "<input type=text name=delete hidden value=false>";
                }
            ?>
        </form>
    </body>
    
    <?php 
        //Display the end message, if possible
        echo $endMessage;
    ?>
</html>