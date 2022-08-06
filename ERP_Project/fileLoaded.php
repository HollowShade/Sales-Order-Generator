<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Load Results</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>

    <body>
        <?php require 'Scripts/functions.php';
            //Before introducing the user, we're going to need some variables
            $fileType = null;
            $fileAddress = null;
            $editLink = null;
            $expectedHeaders = null;
        
            //Variables for data deletion
            $deleteEntry = false;
            $dataEntry = null;
        
            //There are times when this page can take a long time, so give it as long as it needs to load with the following line of code
            set_time_limit(0);
        
            //Check if there's a valid input
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
                //Initialize $fileType if there's a source in the prior file
                if (!empty($_POST["dataFile"])){
                    $fileType = inputSecurity($_POST["dataFile"]);
                }
                
                //Initialize $fileAddress if there's a source in the prior file
                if (!empty($_POST["from"])){
                    $fileAddress = inputSecurity($_POST["from"]);
                } //Check if there was an upload
                else {
                    $fileAddress = uploadFile();
                    
                    //Check if fileAddress points to a file
                    if (file_exists($fileAddress)){
                        //This is the part that depends on the contents of the file
                        try {
                            //Start by determining what to do by checking the value of $fileType
                            switch($fileType){
                                case "salesOrder":
                                    /* This file type is strict, it will only display entries if the user provides twelve of thirteen entries for a sales order file. It will still function as intended if all thirteen entries are provided
                                       It's possible to succeed with nine or ten, but the system won't display names with a nine or ten column file
                                       Also, no generation as we can't make assumptions on what's going on in a user's example
                                       The only exception to this rule is sales order IDs, which will help the user know which sales order is which.
                                       Here's an array to track accepted entries*/
                                    $check = array(null, null, null, null, null, null, null, null, null);
                                    
                                    //And these arrays track other identifiers like company, customer, and product
                                    $companyIDs = array(null);
                                    $customerIDs = array(null);
                                    $productIDs = array(null);
                                    
                                    //Then there are these variables, the first two track the highest month and day value, while the last two track the number of legal entries saved and the number of identifiers generated
                                    $highMonth = 1;
                                    $highDay = 1;
                                    $legalEntries = 1;
                                    $identifiers = 1;
                                    
                                    //Incase if we never touch transit, here's an empty 3D array to reference
                                    $transit = array(SalesOrderHeader);
                                    
                                    //This is the only path that opens the file located in file address
                                    $screening = fopen($fileAddress, "r");
                            
                                    //Skip the file's header, we're using our own
                                    $trash = fgetcsv($screening);
                                    
                                    //Here's a loop to find legal entries
                                    while (!feof($screening)){
                                        //Set array values so the iteration runs smoothly
                                        $check = fgetcsv($screening);
                                        
                                        //If check is empty, we know how the script goes and won't waste count's time.
                                        if ($check != null){
                                            //Initialize the next transit entry to prevent array reading errors
                                            $transit[$legalEntries] = array(null, null, null, null, null, null, null, null, null, null, null, null, null);
                                            
                                            if (count($check) == 12 || count($check) == 13){
                                                //If the element count in check is twelve, increase it to thirteen to give space to the sales order ID we'll eventually generate
                                                if (count($check) == 12){
                                                    $check[12] = floatval($check[11]); //Initialize cost of goods sold
                                                    $check[11] = floatval($check[10]); //Initialize revenue
                                                    $check[10] = intval($check[9]); //Initialize Products sold
                                                    $check[9] = floatval($check[8]); //Initialize product price
                                                    $check[8] = $check[7]; //Initialize Product Name
                                                    $check[7] = $check[6]; //Initialize Product ID
                                                    $check[6] = $check[5]; //Initialize Customer Area
                                                    $check[5] = $check[4]; //Initialize Customer ID
                                                    $check[4] = intval($check[3]); //Initialize Day/Step
                                                    $check[3] = intval($check[2]); //Initialize Month/Round
                                                    $check[2] = $check[1]; //Initialize Company Name
                                                    $check[1] = $check[0]; //Initialize Company ID
                                                    $check[0] = null; //Empty the identifier element
                                                }
                                                
                                                //Check if there's a value in the IDs necessary to create a sales order entry via upload (Check values 1, 3, 4, 5, and 7 for null).
                                                if ($check[1] != null && $check[3] != null && $check[4] != null && $check[5] != null && $check[7] != null ){
                                                    //Save the necessary IDs sans months/rounds and days/steps to arrays if they're new (1, 5, 7). Start with the company identifiers
                                                    if ($companyIDs == array(null)){
                                                        //First one's free
                                                        $companyIDs[0] = $check[1];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($companyIDs as $identity){
                                                            if ($check[1] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to companyIDs if there are no matches
                                                        if ($noMatch){
                                                            $companyIDs[count($companyIDs)] = $check[1];
                                                        }
                                                    }
                                                    
                                                    //Now check the customer identifiers
                                                    if ($customerIDs == array(null)){
                                                        //First one's free
                                                        $customerIDs[0] = $check[5];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($customerIDs as $identity){
                                                            if ($check[5] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to customerIDs if there are no matches
                                                        if ($noMatch){
                                                            $customerIDs[count($customerIDs)] = $check[5];
                                                        }
                                                    }
                                                    
                                                    //Now check the product identifer
                                                    if ($productIDs == array(null)){
                                                        //First one's free
                                                        $productIDs[0] = $check[7];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($productIDs as $identity){
                                                            if ($check[7] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to companyIDs if there are no matches
                                                        if ($noMatch){
                                                            $productIDs[count($productIDs)] = $check[7];
                                                        }
                                                    }
                                                    
                                                    //The day/step and month/round will be compared to our current highest day and month. If the new day and month are higher, they'll replace the old ones
                                                    if ($check[3] > $highMonth){
                                                        $highMonth = $check[3];
                                                    }
                                                    
                                                    if ($check[4] > $highDay){
                                                        $highDay = $check[4];
                                                    }
                                                    
                                                    //Check the remaining values sans Sales Order ID (We'll address this later) for null, then act according if you find null (2, 6, 8, 9, 10, 11, 12). Start with company name
                                                    if ($check[2] == null){
                                                        $check[2] = "Unprovided";
                                                    }

                                                    //Now check customer area
                                                    if ($check[6] == null){
                                                        $check[6] = "Unprovided";
                                                    }

                                                    //Now check product name
                                                    if ($check[8] == null){
                                                        $check[8] = "Unprovided";
                                                    }

                                                    //Now check product price to make sure it's not empty or less than 0
                                                    if (floatval($check[9]) == null || floatval($check[9]) < 0){
                                                        //This is a default value
                                                        $check[9] = 0.00;
                                                    }
                                                    else{
                                                        $check[9] = floatval($check[8]);
                                                    }

                                                    //Now check products sold to make sure it's not empty or less than 0
                                                    if (intval($check[10]) == null || intval($check[10]) < 0){
                                                        //This is a default value
                                                        $check[10] = 0;
                                                    }
                                                    else{
                                                        $check[10] = intval($check[10]);
                                                    }

                                                    //Now check revenue to make sure it's not empty or less than 0
                                                    if (floatval($check[11]) == null || floatval($check[11]) < 0){
                                                        //This is a default value
                                                        $check[11] = 0.00;
                                                    }
                                                    else{
                                                        $check[11] = floatval($check[11]);
                                                    }

                                                    //Now check cost
                                                    if (floatval($check[12]) == null || floatval($check[12]) < 0){
                                                        //This is a default value
                                                        $check[12] = 0.00;
                                                    }
                                                    else{
                                                        $check[12] = floatval($check[12]);
                                                    }

                                                    //Add check to the current transit entry
                                                    $transit[$legalEntries] = check;
                                                }
                                            }
                                            elseif (count($check) == 9 || count($check) == 10){
                                                //If the element count in check is nine, increase it to ten to give space to the sales order ID we'll eventually generate
                                                if (count($check) == 9){
                                                    $check[9] = floatval($check[8]); //Initialize cost of goods sold
                                                    $check[8] = floatval($check[7]); //Initialize revenue
                                                    $check[7] = intval($check[6]); //Initialize Products sold
                                                    $check[6] = floatval($check[5]); //Initialize product price
                                                    $check[5] = $check[4]; //Initialize Product ID
                                                    $check[4] = $check[3]; //Initialize Customer ID
                                                    $check[3] = intval($check[2]); //Initialize Day/Step
                                                    $check[2] = intval($check[1]); //Initialize Month/Round
                                                    $check[1] = $check[0]; //Initialize Company ID
                                                    $check[0] = null; //Empty the identifier element
                                                }
                                                
                                                //Check if there's a value in the IDs necessary to create a sales order entry via upload (Check values 1, 2, 3, 4, and 5 for null).
                                                if ($check[1] != null && $check[2] != null && $check[3] != null && $check[4] != null && $check[5] != null){
                                                    //Save the necessary IDs sans months/rounds and days/steps to arrays if they're new (1, 4, 5). Start with the company identifiers
                                                    if ($companyIDs == array(null)){
                                                        //First one's free
                                                        $companyIDs[0] = $check[1];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($companyIDs as $identity){
                                                            if ($check[1] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to companyIDs if there are no matches
                                                        if ($noMatch){
                                                            $companyIDs[count($companyIDs)] = $check[1];
                                                        }
                                                    }
                                                    
                                                    //Now check the customer identifiers
                                                    if ($customerIDs == array(null)){
                                                        //First one's free
                                                        $customerIDs[0] = $check[4];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($customerIDs as $identity){
                                                            if ($check[4] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to customerIDs if there are no matches
                                                        if ($noMatch){
                                                            $customerIDs[count($customerIDs)] = $check[4];
                                                        }
                                                    }
                                                    
                                                    //Now check the product identifer
                                                    if ($productIDs == array(null)){
                                                        //First one's free
                                                        $productIDs[0] = $check[5];
                                                    }
                                                    else{
                                                        //After that, we need to start making comparisons
                                                        $noMatch = true;
                                                        foreach($productIDs as $identity){
                                                            if ($check[5] == $identity){
                                                                $noMatch = false;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        //Only add a new ID to companyIDs if there are no matches
                                                        if ($noMatch){
                                                            $productIDs[count($productIDs)] = $check[5];
                                                        }
                                                    }
                                                    
                                                    //The day/step and month/round will be compared to our current highest day and month. If the new day and month are higher, they'll replace the old ones
                                                    if ($check[2] > $highMonth){
                                                        $highMonth = $check[2];
                                                    }
                                                    
                                                    if ($check[3] > $highDay){
                                                        $highDay = $check[3];
                                                    }
                                                    
                                                    //Check the remaining values for null, then act according if you find null (6, 7, 8, 9). Start with product price
                                                    if (floatval($check[6]) == null || floatval($check[6]) < 0){
                                                        //This is a default value
                                                        $check[6] = 0.00;
                                                    }
                                                    else{
                                                        $check[6] = floatval($check[6]);
                                                    }

                                                    //Now check products sold
                                                    if (intval($check[7]) == null || intval($check[7]) < 0){
                                                        //This is a default value
                                                        $check[7] = 0;
                                                    }
                                                    else{
                                                        $check[7] = intval($check[7]);
                                                    }

                                                    //Now check revenue
                                                    if (floatval($check[8]) == null || floatval($check[8]) < 0){
                                                        //This is a default value
                                                        $check[8] = 0.00;
                                                    }
                                                    else{
                                                        $check[8] = floatval($check[8]);
                                                    }

                                                    //Now check cost
                                                    if (floatval($check[9]) == null || floatval($check[9]) < 0){
                                                        //This is a default value
                                                        $check[9] = 0.00;
                                                    }
                                                    else{
                                                        $check[9] = floatval($check[9]);
                                                    }

                                                    //Add check to the current transit entry
                                                    $transit[$legalEntries] = array($check[0], $check[1], "Unprovided", $check[2], $check[3], $check[4], "Unprovided", $check[5], "Unprovided", $check[6], $check[7], $check[8], $check[9]);
                                                }
                                            }
                                            
                                            //If no products were sold, it wouldn't make since for an entry to have a sales or cost value, or let alone exist. Therefore, it should be removed.
                                            if ($transit[$legalEntries][10] != null){
                                                //Increase legal entries to show another saved entry if there's a non-default value
                                                $legalEntries++;
                                            }
                                            else {
                                                //We have a garbage entry, throw it out
                                                $trash = array_pop($transit);
                                            }
                                        }
                                    
                                        //Regardless of finding a legal entry or not, reset $check.
                                        $check = array(null, null, null, null, null, null, null, null, null);
                                    }
                                    
                                    //Close the file and reopen it for writing
                                    fclose($screening);
                                    $screening = fopen($fileAddress, "w");
                                    
                                    //We need to save the header before we save anything else
                                    fputcsv($screening, $transit[0]);
                                    
                                    //Sort the company, customer, and product arrays
                                    $companyIDs = sortArray($companyIDs);
                                    $customerIDs = sortArray($customerIDs);
                                    $productIDs = sortArray($productIDs);
                                    
                                    //Replace the file's contents with the contents of $transit in the order of companies, then months/rounds, then days/steps, then customers, then products
                                    foreach ($companyIDs as $companies){
                                        for ($monthTrack = 1; $monthTrack <= $highMonth; $monthTrack++){
                                            for ($dayTrack = 1; $dayTrack <= $highDay; $dayTrack++){
                                                foreach ($customerIDs as $customers){
                                                    foreach ($productIDs as $products){
                                                        
                                                        //Check each entry in transit to see if it matches the current ID combination
                                                        foreach ($transit as $entry){
                                                            if ($entry[1] == $companies && $entry[3] == $monthTrack && $entry[4] == $dayTrack && $entry[5] == $customers && $entry[7] == $products){
                                                                //Check if the entry has its sales order identifier set to null
                                                                if ($entry[0] == null){
                                                                    //No identifier? Generate an ID with the transit array as a reference
                                                                    $entry[0] = numericIDGenerator($identifiers, "salesOrder", $transit);
                                                                    
                                                                    //Increase the number in identifiers
                                                                    $identifiers++;
                                                                }
                                                                
                                                                //Once we know the entry has an ID, save it to the file
                                                                fputcsv($screening, $entry);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    //Close the file one more time
                                    fclose($screening);
                                    break;
                                case "company":
                                    /* This file type is so lenient, it functions just like uploading a file in the main menu.
                                       Just run the uploadCompanyFile function, and hope for the best*/
                                    uploadCompanyFile($fileAddress, $fileAddress);
                                    break;
                                case "customer":
                                    /* This file type is so lenient, it functions just like uploading a file in the main menu.
                                       Just run the uploadCustomerFile function, and hope for the best*/
                                    uploadCustomerFile($fileAddress, $fileAddress);
                                    break;
                                case "product":
                                    /* This file type is so lenient, it functions just like uploading a file in the main menu.
                                       Just run the uploadProductFile function, and hope for the best*/
                                    uploadProductFile($fileAddress, $fileAddress);
                                    break;
                                case "companyPrice":
                                    /* This file type demands a five-column input, anything else will be ignored
                                       There's an exception for three-column inputs, they go to the scrapyard
                                       They'll only be pulled if the system determines a need for data generation
                                       You can also provide a company product price identifier as the first element of the entry, 
                                       This makes four- and six-column inputs possible
                                       Retrieve the necessary values from the uploadCompanyPriceFile function*/
                                    $results = uploadCompanyPriceFile($fileAddress, false);

                                    //Then use those values in the generate company price data function
                                    $run = generateCompanyPriceData($fileAddress, $results[0], $results[1], $results[2], $results[3]);
                                    break;
                                case "customerInterest":
                                    /* This file type demands a five-column input, anything else will be ignored
                                       There's an exception for three-column inputs, they go to the scrapyard
                                       They'll only be pulled if the system determines a need for data generation
                                       You can also provide a customer product interest identifier as the first element of the entry, 
                                       This makes four- and six-column inputs possible
                                       Retrieve the necessary values from the uploadCustomerInterestFile function*/
                                    $results = uploadCustomerInterestFile($fileAddress);
                                    
                                    //Then use those values in the generate customer interest data function
                                    $run = generateCustomerInterestData($fileAddress, $results[0], $results[1], $results[2], $results[3]);
                                    break;
                            }
                        }
                        catch (IOException $error){
                            echo "<h3>We couldn't read the uploaded file</h3>";
                        }
                    }
                }
                
                //Use the value in $fileType to initialize $editLink
                switch($fileType){
                    case "salesOrder":
                        $editLink = "createSalesOrder.php";
                        $expectedHeaders = SalesOrderHeader;
                        $expectedLink = SalesOrderLink;
                        break;
                    case "company":
                        $editLink = "createCompany.php";
                        $expectedHeaders = CompanyHeader;
                        $expectedLink = CompanyLink;
                        break;
                    case "customer":
                        $editLink = "createCustomer.php";
                        $expectedHeaders = CustomerHeader;
                        $expectedLink = CustomerLink;
                        break;
                    case "product":
                        $editLink = "createProduct.php";
                        $expectedHeaders = ProductHeader;
                        $expectedLink = ProductLink;
                        break;
                    case "companyPrice":
                        $editLink = "editCompanyPrice.php";
                        $expectedHeaders = CompanyPriceHeader;
                        $expectedLink = CompanyPriceLink;
                        break;
                    case "customerInterest":
                        $editLink = "editCustomerInterest.php";
                        $expectedHeaders = CustomerInterestHeader;
                        $expectedLink = CustomerInterestLink;
                        break;
                    default: //No value? You're getting an alternate page then
                        $editLink = null;
                        break;
                }
                
                //Check if we need to delete an entry
                if (!empty($_POST["delete"])){
                    $deleteEntry = inputSecurity($_POST["delete"]);
                    //Get the ID of the entry we need delete, but if it doesn't exist, turn of $deleteEntry as a safety measure
                    if ($deleteEntry == "true" && !empty($_POST["entry"])){ 
                        //If we have an ID, we're deleting an entry.
                        $deleteEntry = true;
                        $dataEntry = inputSecurity($_POST["entry"]);
                    }
                    else { //No data entry to delete? Turn delete off, we're not deleting anything
                        $deleteEntry = false;
                    }
                }
            }
        
            //After initializing the files, check the value of $editLink. If it has a value, we know how to display the data.
            if ($editLink != null && file_exists($expectedLink)){
                //Declare a Legal boolean variable
                $legalFile = true;
                
                //Introduce the user to the page
                echo "<h2>" . $fileType . " File Load Results</h2>";
                
                //The rest of the file depends on file contents.
                try {
                    //Open the file
                    $file = fopen($fileAddress, "r");
                    
                    //Get the headers from the file
                    $read = fgetcsv($file, 500, ",");
                    
                    //Check columnCheck against the size of read to see if the file is legal.
                    if (count($read) != count($expectedHeaders)){ //The file is not legal
                        echo "<p>The data below contains the contents of the loaded file. Unfortunately, it doesn't have the same number of columns as a " . $fileType . " file, so we can't treat it like one.<br>Still, you're welcome to look at it, just not edit it.</p>";
                        $legalFile = false;
                    }
                    else{ //The file is legal
                        echo "<p>The data below contains the contents of the loaded file. If the file you provided contains the headers below, it's a proper " . $fileType . " file and you'll have no consequences for editing it.</p>";
                        
                        //Tell the user about the expected headers
                        foreach($expectedHeaders as $data){
                            echo $data . ", ";
                        }
                        
                        //Finish the introduction
                        echo "<br><p>If not, then you're not working with a " . $fileType . " file and attempting to edit it will damage the data inside the file.</p>";
                    }
                    
                    //If the file is from an upload, tell the user that we might've swapped some identifiers around
                    if ($fileAddress != $expectedLink){
                        echo "<p>In case if some of your data isn't where it's supposed to be, like identifiers or things related to identifiers, we try to keep identifiers unique and data sorted. Unfortunately, we're not always sorting by the main identifier, so data can get jumbled during our sort if you're data isn't already sorted, so please keep your data sorted alphabeticly, from the lowest value, to the highest value before uploading.</p>";
                    }
                    
                    echo "<br>";
                    
                    //Declare variables relevant to the table, while row will always exist, column and newData are exclusive to delete mode.
                    $row = 0;
                    
                    if ($deleteEntry){
                        $newData = array(array()); //Contains the data after deletion
                        $column = 0;
                    }
                    
                    //Declare a table and the first row
                    echo "<table><tr>";
                    
                            //Give the first row its headers
                            foreach($read as $data){
                                echo "<th>" . $data . "</th>";
                                
                                //If we're deleting data, save the data to $newData
                                if ($deleteEntry){
                                    $newData[$row][$column] = $data;
                                    $column++;
                                }
                            }
                    
                        //Close the First row
                        echo "</tr>";
                    
                        //Generate the rest of the rows with a loop.
                        while (!feof($file)){
                            //Get the next row of data
                            $read = fgetcsv($file, 500, ",");
                            
                            //If it contains data, display it
                            if ($read != null){
                                //Create a new row
                                echo "<tr>";
                                
                                //Check if we're in deletion mode and if we're working with a matching ID
                                if ($deleteEntry && $read[0] == $dataEntry){
                                    //We deleted this, mark the row as deleted
                                    echo "<td colspan=" . $column . " style=text-align:center><b>ENTRY DELETED</b></td>";
                                }
                                else{
                                    //Increase the value in row
                                    $row++;
                                    
                                    //If we're still in delete mode, reset the value of column to 0
                                    if ($deleteEntry){
                                        $column = 0;
                                    }
                                    
                                    //Add contents to the row
                                    foreach($read as $data){
                                        echo "<td style=text-align:center>" . $data ."</td>";
                                        
                                        //If we're deleting data, save the data to $newData
                                        if ($deleteEntry){
                                            $newData[$row][$column] = $data;
                                            $column++;
                                        }
                                    }
                                
                                    //Check if the file is a legal file to see if the table needs edit buttons
                                    if ($legalFile){
                                        //Add an edit button to the table and set its value to the ID of the current entry
                                        echo "<td><button type=submit name=entry form=toEdit value=" . $read[0] . ">Edit</button></td>";
                                    }
                                }
                                
                                //End the new row
                                echo "</tr>";
                            }
                        }
                    
                    //If there weren't any rows added during the loop, let the user know that the reference file was empty
                    if ($row == 0){
                        echo "<tr><td colspan=" . count($expectedHeaders) . " style=text-align:center>There are no entries on file.</td></tr>";
                    }
                    
                    //Close the table, then the file
                    echo "</table><br>";
                    fclose($file);
                    
                    //If we're in delete entry mode, we save the data we collected here
                    if ($deleteEntry){
                        saveFile($newData, $fileAddress);
                        
                        //Check the type of file we're displaying, then tell the system to update related files if its a customer, product, or company
                        if ($fileType == "company" && $fileAddress == CompanyLink || $fileType == "customer" && CustomerLink || $fileType == "product" && ProductLink){
                            updateFiles($fileType);
                        }
                    }
                    
                    //Create a hidden form that travels to an edit function
                    echo "<form method=post id=toEdit action=" . $editLink . ">";
                        echo "<input type=text name=source hidden value=" . $fileAddress . ">";
                        echo "<input type=text name=edit value=true hidden>";
                        echo "<input type=text name=priorLink hidden value=" . htmlspecialchars($_SERVER["PHP_SELF"]) . ">";
                        echo "<input type=text name=saved value=false hidden>";
                    echo "</form>";
                }
                catch (IOExeption $error){ //In case of file errors
                    echo "<p>While we would like to display the load results here, we couldn't get them. You may want to use the links below to leave the page and try to load the file again</p>";
                }
                
                //The following is a form with links to other pages
                echo "<p>In case if you want to backtrack to another page, here are some links to help you do so</p>";
                echo "<form method=post>";
                    echo "<button type=submit formaction=loadFile.php>Return to the file select page</button><br><br>";
                    echo "<button type=submit formaction=index.php>Return to the main menu</button><br><br>";
                    echo "<button type=submit name=download value=true formaction=loadFile.php>Download " . $fileType . " data</button>";
                    echo "<input type=text name=from value=" . $fileAddress . " hidden>";
                    echo "<input type=text name=dataFile value=" . $fileType . " hidden>";
                echo "</form>";
            }
        else{
            echo "<h2>Load Failed</h2>";
                echo "<p>This would be the file load results page, but there was no file to load from.</p>";
                echo "<p>The best way to restore a load direction is to reload the file from the main menu all the way back to this page.  You can go back to the main menu with the button below.</p>";
                echo "<form method=post action=index.php>";
                    echo "<button type=submit name=return>Okay, take me back to the Main Menu</button>";
                    echo "<input type=text name=from hidden value=" . $fileAddress . ">";
                echo "</form>";
        }
        ?>
    </body>
    
</html>