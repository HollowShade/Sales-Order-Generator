<?php
//The purpose of this page is to hold PHP functions.  Include it, but don't travel to it.

//Do you mind if I have some constants here as well? I want to see if they can hold arrays, I've also thrown in some constants carrying addresses used throughout the program
define ("SalesOrderHeader", array("Sales Order ID", "Company ID", "Company Name", "Round", "Step", "Customer ID", "Customer Area", "Product ID", "Product Name", "Product Price", "Products Sold", "Revenue", "Cost"));
define ("SalesOrderLink", "Data/salesOrders.csv");
define ("CompanyHeader", array("Company ID", "Company Name"));
define ("CompanyLink", "Data/companies.csv");
define ("CustomerHeader", array("Customer ID", "Customer Name", "Customer Address", "Customer Area"));
define ("CustomerLink", "Data/customers.csv");
define ("ProductHeader", array("Product ID", "Product Name", "Product Cost"));
define ("ProductLink", "Data/products.csv");
define ("CompanyPriceHeader", array("Company Product Price ID", "CompanyID", "ProductID", "Product Price", "Product Stock", "Product Status"));
define ("CompanyPriceLink", "Data/companyPrices.csv");
define ("CustomerInterestHeader", array("Customer Product Interest ID", "CustomerID", "ProductID", "Customer Interest Level", "Customer Product Purchase Minimum", "Customer Product Purchase Maximum"));
define ("CustomerInterestLink", "Data/customerInterests.csv");

//The input security function to make sure that inputs are legal
//Validation function
  function inputSecurity($input) {
       $input = trim($input);
       $input = stripslashes($input);
       $input = htmlspecialchars($input);
       return $input;
   }

//The loadFile function should only be used in the try portion of a try-catch block, but it helps with any file that needs to load data. Give it a file address and it will give you an array of its contents.
function loadFile($link){
    //Initialize fileArray so in case if the try block below doesn't work, we can return a defined array
    $fileArray[0][0] = null;
    
    try{
        //Open the file
        $file = fopen($link, "r");
    
        //Have a counter variable so we can jump to new elements/entries/rows of our new array
        $row = 0;
        
        //Read from the file
        while (!feof($file)){
            $fileArray[$row] = fgetcsv($file);
            
            //Check the array to see if we got an empty value
            if ($fileArray[$row] == null){
                //Remove the empty entry
                $trash = array_pop($fileArray);
            }
            else{
                //Increase row so we can enter a new data entry
                $row++;
            }
        }
        
        //Close the file
        fclose($file);
    }
    catch (IOException $error){
        throw $error;
    }
    
    //return the file contents, they should now be in the fileArray
    return $fileArray;
}

//The uploadFile function takes a file and runs the upload process on it, a process which I based off the example code in W3Schools Complete Upload File PHP Script example
function uploadFile (){
    $directory = "Uploads/";
    $targetFile = $directory . basename($_FILES["upload"]["name"]);
    $fileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
    
    //Check if we are working with a CSV file, an excel file, or something else
    if($fileType != "csv" && $fileType != "xlsx"){
        return null;
    }
    
    //Attempt to upload the file
    if ($_FILES["upload"]["error"]){
        return null;
    }
    if (move_uploaded_file($_FILES["upload"]["tmp_name"], $targetFile)){
        return $targetFile;
    }
    else {
        return null;
    }
}

//The saveFile function should only be used in the try portion of a try-catch block, but it helps with any file that needs to save data in a simple manner. Just give it an array and a destination, and you'll have a saved file.
function saveFile($save, $saveAs){
    try{
        //Open the file
        $file = fopen($saveAs, "w");
    
        //Use a foreach loop to send the contents of the $save array to the destination in $saveAs
        foreach($save as $data){
            fputcsv($file, $data);
        }
        
        //Close the file
        fclose($file);
    }
    catch (IOException $error){
        throw $error;
    }
}

/**
 The deleteForeignFiles function takes a file type and a file address. 
 Each file type is associated with a constant link tied to a local system file
 If the address provided doesn't match the link in the constant connected to the file type, it's deleted
 This function is used by the main menu and the load file page to prevent overpopulation in the upload file
 */
function deleteForeignFiles ($fileType, $fileAddress){
    //Use a switch statement with fileType to determine what link we need to file
    switch ($fileType){
        case "salesOrder":
            $targetAddress = SalesOrderLink;
            break;
        case "company":
            $targetAddress = CompanyLink;
            break;
        case "customer":
            $targetAddress = CustomerLink;
            break;
        case "product":
            $targetAddress = ProductLink;
            break;
        case "companyPrice":
            $targetAddress = CompanyPriceLink;
            break;
        case "customerInterest":
            $targetAddress = CustomerInterestLink;
            break;
        default:
            $targetAddress = null;
    }
    
    //Check fileAddress against targetAddress, if they don't match and targetAddress isn't null: Delete the file fileAddress points to
    if ($fileAddress != $targetAddress && $targetAddress != null){
        unlink($fileAddress);
    }
}

//The updateFiles function takes a file type (like company price or customer) and updates the files that use that file type as a foreign key
function updateFiles ($fileType){
    //Here are some variables that will appear throughout the function:
    $check = array(array());
    $checkFor = array();
    $newData = array(array());
    $field = 0;
    
    //We'll need to check the fileType to determine which files to update
    switch ($fileType){
        case "company":
            //We need to make sure the company file exists first
            if (file_exists(CompanyLink)){
                //Properly initialize $checkFor by loading company into $check
                $check = loadFile(CompanyLink);

                //If the number of columns doesn't match the file's specified number of columns (2), do not update any files
                if ($check != null && count($check[0]) == 2){
                    //Then use a loop to read the array and load the IDs into $checkFor
                    for ($track = 1; $track < count($check); $track++){
                        $checkFor[$field] = $check[$track][0];
                        $field++;
                    }

                    //Set $field to 1 for the future loops
                    $field = 1;

                    //Make sure the company prices file exists
                    if (file_exists(CompanyPriceLink)){
                        //Load the company price file
                        $check = loadFile(CompanyPriceLink);

                        //If the number of columns doesn't match the file's specified number of columns (6), do not update the file
                        if ($check != null && count($check[0]) == 6){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][1] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }
                            
                            //Save the new data to the company price file
                            saveFile($newData, CompanyPriceLink);

                            //Reset $field to 1 and $newData to an empty array
                            $field = 1;
                            $newData = array(array());
                        }
                    }

                    //Make sure the sales orders file exists
                    if (file_exists(SalesOrderLink)){
                        //Load the sales order file
                        $check = loadFile(SalesOrderLink);

                        //If the number of columns doesn't match the file's specified number of columns (13), do not update the file
                        if ($check != null && count($check[0]) == 13){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][1] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the sales order file
                            saveFile($newData, SalesOrderLink);
                        }
                    }
                }
            }
            break;
        case "customer":
            //We need to make sure the customer file exists first
            if (file_exists(CustomerLink)){
                //Properly initialize $checkFor by loading customer into $check
                $check = loadFile(CustomerLink);

                //If the number of columns doesn't match the file's specified number of columns (4), do not update any files
                if ($check != null && count($check[0]) == 4){
                    //Then use a loop to read the array and load the IDs into $checkFor
                    for ($track = 1; $track < count($check); $track++){
                        $checkFor[$field] = $check[$track][0];
                        $field++;
                    }

                    //Set $field to 1 for the future loops
                    $field = 1;

                    //Make sure the customer interests file exists
                    if (file_exists(CustomerInterestLink)){
                        //Load the customer interest file
                        $check = loadFile(CustomerInterestLink);

                        //If the number of columns doesn't match the file's specified number of columns (6), do not update the file
                        if ($check != null && count($check[0]) == 6){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][1] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the customer interest file
                            saveFile($newData, CustomerInterestLink);

                            //Reset $field to 1 and $newData to an empty array
                            $field = 1;
                            $newData = array(array());
                        }
                    }

                    //Make sure the sales orders file exists
                    if (file_exists(SalesOrderLink)){
                        //Load the sales order file
                        $check = loadFile(SalesOrderLink);

                        //If the number of columns doesn't match the file's specified number of columns (13), do not update the file
                        if ($check != null && count($check[0]) == 13){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][5] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the sales order file
                            saveFile($newData, SalesOrderLink);
                        }
                    }
                }
            }
            break;
        case "product":
            //We need to make sure the products file exists first
            if (file_exists(ProductLink)){
                //Properly initialize $checkFor by loading product into $check
                $check = loadFile(ProductLink);

                //If the number of columns doesn't match the file's specified number of columns (3), do not update any files
                if ($check != null && count($check[0]) == 3){
                    //Then use a loop to read the array and load the IDs into $checkFor
                    for ($track = 1; $track < count($check); $track++){
                        $checkFor[$field] = $check[$track][0];
                        $field++;
                    }

                    //Set $field to 1 for the future loops
                    $field = 1;

                    //Make sure the company prices file exists
                    if (file_exists(CompanyPriceLink)){
                        //Load the company price file
                        $check = loadFile(CompanyPriceLink);

                        //If the number of columns doesn't match the file's specified number of columns (6), do not update the file
                        if ($check != null && count($check[0]) == 6){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][2] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the company price file
                            saveFile($newData, CompanyPriceLink);

                            //Reset $field to 1 and $newData to an empty array
                            $field = 1;
                            $newData = array(array());
                        }
                    }

                    //Make sure the customer interests file exists
                    if (file_exists(CustomerInterestLink)){
                        //Load the customer interest file
                        $check = loadFile(CustomerInterestLink);

                        //If the number of columns doesn't match the file's specified number of columns (6), do not update the file
                        if ($check != null && count($check[0]) == 6){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][2] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the customer interest file
                            saveFile($newData, CustomerInterestLink);

                            //Reset $field to 1 and $newData to an empty array
                            $field = 1;
                            $newData = array(array());
                        }
                    }

                    //Make sure the sales orders file exists
                    if (file_exists(SalesOrderLink)){
                        //Load the sales order file
                        $check = loadFile(SalesOrderLink);

                        //If the number of columns doesn't match the file's specified number of columns (13), do not update the file
                        if ($check != null && count($check[0]) == 13){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][7] == $ID){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the sales order file
                            saveFile($newData, SalesOrderLink);
                        }
                    }
                }
            }
            break;
        case "companyPrice":
            //We need to make sure the company product price file exists first
            if (file_exists(CompanyPriceLink)){
                //Properly initialize $checkFor by loading company price into $check
                $check = loadFile(CompanyPriceLink);

                //If the number of columns doesn't match the file's specified number of columns (6), do not update any files
                if ($check != null && count($check[0]) == 6){
                    //Then use a loop to read the array and load the IDs into $checkFor
                    for ($track = 1; $track < count($check); $track++){
                        $checkFor[$field] = array($check[$track][1], $check[$track][2]);
                        $field++;
                    }

                    //Set $field to 1 for the future loops
                    $field = 1;

                    //Make sure the sales orders file exists
                    if (file_exists(SalesOrderLink)){
                        //Load the sales order file
                        $check = loadFile(SalesOrderLink);

                        //If the number of columns doesn't match the file's specified number of columns (13), do not update the file
                        if ($check != null && count($check[0]) == 13){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][1] == $ID[0] && $check[$track][7] == $ID[1]){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the sales order file
                            saveFile($newData, SalesOrderLink);
                        }
                    }
                }
            }
            break;
        case "customerInterest":
            //We need to make sure the customer product interest file exists first
            if (file_exists(CustomerInterestLink)){
                //Properly initialize $checkFor by loading customer interest into $check
                $check = loadFile(CustomerInterestLink);

                //If the number of columns doesn't match the file's specified number of columns (6), do not update any files
                if ($check != null && count($check[0]) == 6){
                    //Then use a loop to read the array and load the IDs into $checkFor
                    for ($track = 1; $track < count($check); $track++){
                        $checkFor[$field] = array($check[$track][1], $check[$track][2]);
                        $field++;
                    }

                    //Set $field to 1 for the future loops
                    $field = 1;

                    //Make sure the sales orders file exists
                    if (file_exists(SalesOrderLink)){
                        //Load the sales order file
                        $check = loadFile(SalesOrderLink);

                        //If the number of columns doesn't match the file's specified number of columns (13), do not update the file
                        if ($check != null && count($check[0]) == 13){
                            //Store the first entry (headers) into $newData
                            $newData[0] = $check[0];

                            //Check every entry in $check against the IDs in $checkFor
                            for ($track = 1; $track < count($check); $track++){
                                //Reset $exists
                                $exists = false;

                                //Check the ID in track's entry against the IDs in $checkFor
                                foreach ($checkFor as $ID){
                                    //If there's a match, set $exists to true and break the loop.
                                    if ($check[$track][5] == $ID[0] && $check[$track][7] == $ID[1]){
                                        $exists = true;
                                        break;
                                    }
                                }

                                //If $exists was set to true, add the current entry to $newData and increase field
                                if ($exists){
                                    $newData[$field] = $check[$track];
                                    $field++;
                                }
                            }

                            //Save the new data to the sales order file
                            saveFile($newData, SalesOrderLink);
                        }
                    }
                }
            }
            break;
        default: //The file type isn't a foreign key anywhere else
            echo "<br>Why are you requesting an update if there's nothing to update?";
    }
    
    //If we ran an update, let the user know
    if ($field > 0){
        echo "<br>Files relative to the " . $fileType . " file have been updated so they don't show non-existing IDs.<br>";
    }
    else {
        echo "<br>The local " . $fileType . " file is completely empty, we're not updating any data until you restore the file's headers.<br>";      
    }
}

//IDGenerator generates IDs for companies, but if it goes over 52 recorded companies, it will call giberish for help
function IDGenerator($reference){
   $ID = "AA"; //Starting ID
            
    if ($reference != null){
        //Use a loop to check if an ID matches any in the file.
        for($track = 0; $track < count($reference); $track++){
            if ($ID == $reference[$track]){
                //If there's a match, change the value of $ID with this switch
                switch ($ID){
                    case "AA":
                        $ID = "BB";
                        break;
                    case "BB":
                        $ID = "CC";
                        break;
                    case "CC":
                        $ID = "DD";
                        break;
                    case "DD":
                        $ID = "EE";
                        break;
                    case "EE":
                        $ID = "FF";
                        break;
                    case "FF":
                        $ID = "GG";
                        break;
                    case "GG":
                        $ID = "HH";
                        break;
                    case "HH":
                        $ID = "II";
                        break;
                    case "II":
                        $ID = "JJ";
                        break;
                    case "JJ":
                        $ID = "KK";
                        break;
                    case "KK":
                        $ID = "LL";
                        break;
                    case "LL":
                        $ID = "MM";
                        break;
                    case "MM":
                        $ID = "NN";
                        break;
                    case "NN":
                        $ID = "OO";
                        break;
                    case "OO":
                        $ID = "PP";
                        break;
                    case "PP":
                        $ID = "QQ";
                        break;
                    case "QQ":
                        $ID = "RR";
                        break;
                    case "RR":
                        $ID = "SS";
                        break;
                    case "SS":
                        $ID = "TT";
                        break;
                    case "TT":
                        $ID = "UU";
                        break;
                    case "UU":
                        $ID = "VV";
                        break;
                    case "VV":
                        $ID = "WW";
                        break;
                    case "WW":
                        $ID = "XX";
                        break;
                    case "XX":
                        $ID = "YY";
                        break;
                    case "YY":
                        $ID = "ZZ";
                        break;
                    case "ZZ":
                        $ID = "aa";
                        break;
                    case "aa":
                        $ID = "bb";
                        break;
                    case "bb":
                        $ID = "cc";
                        break;
                    case "cc":
                        $ID = "dd";
                        break;
                    case "dd":
                        $ID = "ee";
                        break;
                    case "ee":
                        $ID = "ff";
                        break;
                    case "ff":
                        $ID = "gg";
                        break;
                    case "gg":
                        $ID = "hh";
                        break;
                    case "hh":
                        $ID = "ii";
                        break;
                    case "ii":
                        $ID = "jj";
                        break;
                    case "jj":
                        $ID = "kk";
                        break;
                    case "kk":
                        $ID = "ll";
                        break;
                    case "ll":
                        $ID = "mm";
                        break;
                    case "mm":
                        $ID = "nn";
                        break;
                    case "nn":
                        $ID = "oo";
                        break;
                    case "oo":
                        $ID = "pp";
                        break;
                    case "pp":
                        $ID = "qq";
                        break;
                    case "qq":
                        $ID = "rr";
                        break;
                    case "rr":
                        $ID = "ss";
                        break;
                    case "ss":
                        $ID = "tt";
                        break;
                    case "tt":
                        $ID = "uu";
                        break;
                    case "uu":
                        $ID = "vv";
                        break;
                    case "vv":
                        $ID = "ww";
                        break;
                    case "ww":
                        $ID = "xx";
                        break;
                    case "xx":
                        $ID = "yy";
                        break;
                    case "yy":
                        $ID = "zz";
                        break;
                    default:
                        //The function can only repeat the Alphabet so many times before it gets repetative, I'm running the giberish function in a way that it only generates two characters.
                        $ID = giberish(rand(1, 2808));
                }
                        
                //Then reset $track, it should increment after the loop's contents are ran
                $track = 0;
            }
        }
    }
                
    return $ID;
}

//The Giberish function generates random text.  Not only does it give IDGenerator more options than it should, but it also for unreliable random name generation.
function giberish($wut) {
    //Variables
    $rerun = $wut / 53;
    $characterGenerator = $wut % 53;
    
    //The result variable requires two steps to create.
    //Step one, use characterGenerator to determine what character should start the result string.
    switch ($characterGenerator){
        case 0:
            $result = " ";
            break;
        case 1:
            $result = "A";
            break;
        case 2:
            $result = "B";
            break;
        case 3:
            $result = "C";
            break;
        case 4:
            $result = "D";
            break;
        case 5:
            $result = "E";
            break;
        case 6:
            $result = "F";
            break;
        case 7:
            $result = "G";
            break;
        case 8:
            $result = "H";
            break;
        case 9:
            $result = "I";
            break;
        case 10:
            $result = "J";
            break;
        case 11:
            $result = "K";
            break;
        case 12:
            $result = "L";
            break;
        case 13:
            $result = "M";
            break;
        case 14:
            $result = "N";
            break;
        case 15:
            $result = "O";
            break;
        case 16:
            $result = "P";
            break;
        case 17:
            $result = "Q";
            break;
        case 18:
            $result = "R";
            break;
        case 19:
            $result = "S";
            break;
        case 20:
            $result = "T";
            break;
        case 21:
            $result = "U";
            break;
        case 22:
            $result = "V";
            break;
        case 23:
            $result = "W";
            break;
        case 24:
            $result = "X";
            break;
        case 25:
            $result = "Y";
            break;
        case 26:
            $result = "Z";
            break;
        case 27:
            $result = "a";
            break;
        case 28:
            $result = "b";
            break;
        case 29:
            $result = "c";
            break;
        case 30:
            $result = "d";
            break;
        case 31:
            $result = "e";
            break;
        case 32:
            $result = "f";
            break;
        case 33:
            $result = "g";
            break;
        case 34:
            $result = "h";
            break;
        case 35:
            $result = "i";
            break;
        case 36:
            $result = "j";
            break;
        case 37:
            $result = "k";
            break;
        case 38:
            $result = "l";
            break;
        case 39:
            $result = "m";
            break;
        case 40:
            $result = "n";
            break;
        case 41:
            $result = "o";
            break;
        case 42:
            $result = "p";
            break;
        case 43:
            $result = "q";
            break;
        case 44:
            $result = "r";
            break;
        case 45:
            $result = "s";
            break;
        case 46:
            $result = "t";
            break;
        case 47:
            $result = "u";
            break;
        case 48:
            $result = "v";
            break;
        case 49:
            $result = "w";
            break;
        case 50:
            $result = "x";
            break;
        case 51:
            $result = "y";
            break;
        case 52:
            $result = "z";
            break;
        default:
            $result = "";
    }
                
    //Step two: Call the function again and add it's results to the current result.
    if ($rerun >= 1) {
        $result .= giberish($rerun); //Dear god, this is going to go to hell fast!
    }
                
    //Return the Result
    return $result;
}

//This searchArray function takes a 3D array and a string representing what to search for. Once supplied, it returns the array number the values were in, but if it failed, it returns the number of arrays it searched through
function searchArray($search, $target) {
    //Container represents an array number, which identifies an array holding a company
    for ($container = 0; $container < count($search); $container++){
        //Search the specified element for the value we are looking for
        if ($search[$container][0] == $target){
            return $container; //If there's an array with the ID we're looking for, return it's number.
        }
    }
                
    //This might cause an error, but when you confident one of your IDs exists, you do stuff like this
    return $container;
}

/*The numericIDGenerator is a function for customer and product data. 
  It takes an array representing data contents, a number representing what we're trying to save, and a string containing the file we're generating for, and returns an ID.*/
function numericIDGenerator($start, $file, $reference){
    //Check if the reference array is empty
    if ($reference == null){
        //Empty array? There nothing to check, so the first ID is free
        return numericIDCreator($start, $file);
    }
    else{
        //We're running the loop, here's a variable for tracking if we've ran through it before
        $looped = false;
        $ID = "The world turned upside down"; //It would feel wrong to not have $ID initialized, even if we're past the point where we know the following loop will run at least once
    
        //Run a for loop to check the new ID against the IDs contained in the reference array
        for ($track = 0; $track < count($reference); $track++){
            //Get a value from the ID creator
            $ID = numericIDCreator($start, $file);
        
            //Compare the value of $ID to the values in $reference (if the customer and product data tables are similar, the ID is stored in slot 1).
            if ($ID == $reference[$track][0]){
                //Reset the counter
                $track = -1;
            
                //Check the value of $start and looped to see what we'll do next
                if ($start >= 999999){
                    if ($looped){//The only way looped is true is if we go through the IDs before 999999, if that's the case, tell the user of the situation.
                        echo "The number of " . $file . " IDs is exceeding 1,000,000, are you sure you're not generating too much data?";
                        $start++; 
                    }
                    else { //We've hit the max? Check the provided array and see if that's true by going back to 0
                        $start = 0;
                        $looped = true; //Just to say we're checking the data again
                    }
                }
                else { //Just increase start
                    $start++;
                }
            }
        }
    
        //Return the ID
        return $ID;
    }
}

//The numericIDCreator is an add on to numericIDGenerator, handling the value creation side of generating while it does the data checking
function numericIDCreator($value, $source){
    //Prepare a prefix based on the value in source
    switch ($source){
        case "customer":
            $ID = "CC-";
            break;
        case "product":
            $ID = "PP-";
            break;
        case "companyPrice":
            $ID = "CP-";
            break;
        case "customerInterest":
            $ID = "CI-";
            break;
        case "salesOrder":
            $ID = "SO-";
            break;
        default:
            $ID = "";
            break;
    }
    
    //Check the number in value to determine what to return
    if ($value < 100000){
        if ($value < 10000){
            if ($value < 1000){
                if ($value < 100){ 
                    if ($value < 10){
                        if ($value < 1){ //No digits were provided for the ID
                            return $ID . "000000";
                        }
                        else { //One digit is provided for an ID
                            return $ID . "00000" . $value;}
                    }
                    else { //Two digits are provided for an ID
                        return $ID . "0000" . $value;}
                }
                else { //Three digits are provided for an ID
                    return $ID . "000" . $value;}
            }
            else { //Four digits are provided for an ID
                return $ID . "00" . $value;}
        }
        else { //Five digits are provided for an ID
            return $ID . "0" . $value;}
    }
    else { //Six digits are provided for an ID
        return $ID . $value;}
}

//The randomDecimal function is for data sets that use price. It takes a minimum value, a maximum value, rounds them into whole numbers, finds a random number between them, and returns the result divided by 100.
function randomDecimal($mini, $maxi){
    //Variables 
    $min = $mini * 100;
    $max = $maxi * 100;
                
    //Rounding numbers eliminates any chance of floats reaching rand, which doesn't take floats
    $min = round($min);
    $max = round($max);
                
    //Return a random price
    return (rand($min, $max)) / 100.0;
}

/*The presetAreas function is for customer and location data sets.  It takes a number and uses it to determine which one of nine preset values it should return. 
  If the number isn't between 0 and 16, presetAreas will call giberish to make up an area.*/
function presetAreas($choice){
    switch ($choice){
        case 0:
            return "North";
            break;
        case 1:
            return "South";
            break;
        case 2:
            return "East";
            break;
        case 3:
            return "West";
            break;
        case 4:
            return "Central";
            break;
        case 5:
            return "Northeast";
            break;
        case 6:
            return "Northwest";
            break;
        case 7:
            return "Southeast";
            break;
        case 8:
            return "Southwest";
            break;
        case 9:
            return "North Northeast";
            break;
        case 10:
            return "North Northwest";
            break;
        case 11:
            return "South Southeast";
            break;
        case 12:
            return "South Southwest";
            break;
        case 13:
            return "East Northeast";
            break;
        case 14:
            return "East Southeast";
            break;
        case 15:
            return "West Northwest";
            break;
        case 16:
            return "West Southwest";
            break;
        default:
            return giberish(rand());
    }
}

/* The uploadCompanyFile function takes a link, extracts its contents, sorts them into the contents of a company file (if it can), and saves them to another link (it can match the first link).
    Results depend on the sucess of the upload and the contents recieved.*/
function uploadCompanyFile($fileAddress, $target){
    //This array will hold the array that we will return in the future
    $transit = array();
    
    //This array keeps identifiers unique
    $IDStorage = array(null);
    
    //We'll need to track how much data we'll run through
    $entries = 1;
    
    //The rest of the function depends on reading and writing to files
    try{
        //Open the uploaded file
        $reader = fopen($fileAddress, "r");
                
        //Read the first entry to see what we are working with
        $transit[0] = fgetcsv($reader);
                
        //Check the contents of the first entry to see how we'll handle the upload, if we'll handle it at all
        if (count($transit[0]) == 1){ //If we were only provided with names, assuming we recieved names
            //The header of the file (the first data entry) needs to be modified for proper company data file formatting
            $transit[0] = CompanyHeader;
                    
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                        
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{
                    //Since the identifier is the first value, everything needs to move up for the to-be-generated ID
                    $transit[$entries][1] = $transit[$entries][0];
                    
                    //If we're working with one column, we should expect to have to set the value of the IDs
                    $IDStorage[$entries] = IDGenerator($IDStorage);
                    $transit[$entries][0] = $IDStorage[$entries];
                            
                    //Transit might have more values than just company name and ID, remove any extra values
                    while (count($transit[$entries]) > 2){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        elseif (count($transit[0]) == 2){ //The user's providing the entire file.
            //The header of the file (the first data entry) needs to be modified for proper company data file formatting
            $transit[0] = CompanyHeader;
            
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                        
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null, null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{ //Check all values of the entry and make sure it only has two columns
                    //Check the value in ID. If there's a unique value, add it to the IDStorage. If not, generate one in reference to what's in IDStorage
                    if($transit[$entries][0] == null || searchArray($transit, $transit[$entries][0]) != $entries){
                        $IDStorage[$entries] = IDGenerator($IDStorage);
                    }
                    else{
                        $IDStorage[$entries] = $transit[$entries][0];
                    }
                    $transit[$entries][0] = $IDStorage[$entries];
                    
                    //Check the value in name, use giberish to create one if its empty
                    if($transit[$entries][1] == null){
                        $transit[$entries][1] = giberish(rand());
                    }
                            
                    //Transit might have more values than just company name and ID, remove any extra values
                    while (count($transit[$entries]) > 2){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        else{
            fclose($reader);
            return "Fail";
        }
                
        //Close the file
        fclose($reader);
                
        //If the code above gave us more than one entry, save the data and inform the user of a successful upload
        if ($entries > 1){
            saveFile($transit, $target);
            return "Pass";
        }
        else{
            return "Empty";
        }
    }
    catch (IOException $error){
        throw $error;
    }
}

/* The uploadCustomerFile function takes a link, extracts its contents, sorts them into the contents of a customer file (if it can), and saves them to another link (it can match the first link).
    Results depend on the success of the upload and the contents recieved.*/
function uploadCustomerFile($fileAddress, $target){
    //Declare an array that will hold the uploaded file's data while it's being edited into the system's customer data file.
    $transit = array();
            
    //We'll need a track of how much data we'll run through
    $entries = 1;
            
    //The rest of the function depends on reading and writing to files
    try{
        //Open the uploaded file
        $reader = fopen($fileAddress, "r");
                
        //Read the first entry to see what we are working with
        $transit[0] = fgetcsv($reader);
            
        //Check the contents of the first entry to see how we'll handle the upload, if we'll handle it at all
        if (count($transit[0]) == 3){ //If we were only provided with names, addresses, and areas, assuming that's what we recieved
            //The header of the file (the first data entry) needs to be modified for proper customer data file formatting
            $transit[0] = CustomerHeader;
                    
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                        
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null, null, null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{ //Check all values of the entry and make sure it only has four columns
                    //Since the identifier is the first value, everything needs to move up for the to-be-generated ID
                    $transit[$entries][3] = $transit[$entries][2]; //Initialize area
                    $transit[$entries][2] = $transit[$entries][1]; //Initialize address
                    $transit[$entries][1] = $transit[$entries][0]; //Initialize name
                            
                    //Since the ID was not provided, we'll generate one
                    $transit[$entries][0] = numericIDGenerator($entries, "customer", $transit);        
                    
                    //Check if there's a value in name, use giberish if a name doesn't exist
                    if ($transit[$entries][1] == null){
                        $transit[$entries][1] = giberish(rand());
                    }
                            
                    //Check if there's a value in address, use giberish if the address is missing
                    if ($transit[$entries][2] == null){
                        $transit[$entries][2] = giberish(rand());
                    }
                            
                    //Check if there's a value in area, set it to central with presetAreas if the area is missing
                    if ($transit[$entries][3] == null){
                        $transit[$entries][3] = presetAreas(4);
                    }
                            
                    //Transit might have more values than just customer name, address, area, and ID, remove any extra values
                    while (count($transit[$entries]) > 4){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        elseif (count($transit[0]) == 4){ //The user's providing the entire file.
            //The header of the file (the first data entry) needs to be modified for proper customer data file formatting
            $transit[0] = CustomerHeader;
            
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null, null, null, null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{ //Check all values of the entry and make sure it only has three columns
                    //Check the value in ID, generate one with numericIDGenerator if its missing or a repeated ID
                    if($transit[$entries][0] == null || searchArray($transit, $transit[$entries][0]) != $entries){
                        $transit[$entries][0] = numericIDGenerator($entries, "customer", $transit);
                    }
                    
                    //Check the value in name, use giberish to create one if its empty
                    if($transit[$entries][1] == null){
                        $transit[$entries][1] = giberish(rand());
                    }
                                  
                    //Check if there's a value in address, use giberish if the address is missing
                    if ($transit[$entries][2] == null){
                        $transit[$entries][2] = giberish(rand());
                    }
                            
                    //Check if there's a value in area, set it to central with presetAreas if the area is missing
                    if ($transit[$entries][3] == null){
                        $transit[$entries][3] = presetAreas(4);
                    }
                            
                    //Transit might have more values than just customer name, address, area, and ID, remove any extra values
                    while (count($transit[$entries]) > 4){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        else{ //The file is incompatible with the customer file format, close it and tell the user about the upload failure
            fclose($reader);
            return "Fail";
        }
                
        //Close the file
        fclose($reader);
                
        //If the code above gave us more than one entry, save the data and tell the user of the success
        if ($entries > 1){
            saveFile($transit, $target);
            return "Pass";
        }
        else{ //Tell the user about the empty file
            return "Empty";
        }
                
    }
    catch (IOException $error){
        throw $error;
    }
}

/* The uploadProductFile function takes a link, extracts its contents, sorts them into the contents of a product file (if it can), and saves them to another link (it can match the first link).
    Results depend on the sucess of the upload and the contents recieved.*/
function uploadProductFile($fileAddress, $target){
    //Declare an array that will hold the uploaded file's data while it's being edited into the system'm company data file.
    $transit = array();
            
    //We'll need a track of how much data we'll run through
    $entries = 1;
            
    //The rest of the function depends on reading and writing to files
    try{
        //Open the uploaded file
        $reader = fopen($fileAddress, "r");
                
        //Read the first entry to see what we are working with
        $transit[0] = fgetcsv($reader);
                
        //Check the contents of the first entry to see how we'll handle the upload, if we'll handle it at all
        if (count($transit[0]) == 2){ //If we were only provided with names and costs, assuming we recieved names
            //The header of the file (the first data entry) needs to be modified for proper product data file formatting
            $transit[0] = ProductHeader;
                    
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                    
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null, null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{ //Check all values of the entry and make sure it only has three columns
                    //Since the identifier is the first value, everything needs to move up for the to-be-generated ID
                    $transit[$entries][2] = floatval($transit[$entries][1]); //Initialize Product Price
                    $transit[$entries][1] = $transit[$entries][0]; //Initialize Product Name
                    
                    //Since the ID was not provided, we'll generate one
                    $transit[$entries][0] = numericIDGenerator($entries, "product", $transit);
                            
                    //Check if there's a value in name, use giberish if a name doesn't exist
                    if ($transit[$entries][1] == null){
                        $transit[$entries][1] = giberish(rand());
                    }
                            
                    //Check if there's a value in cost, use randomDecimal if the price is missing
                    if ($transit[$entries][2] == null){
                        $transit[$entries][2] = randomDecimal(0, 1999.99);
                    }
                            
                    //Transit might have more values than just product name, cost, and ID, remove any extra values
                    while (count($transit[$entries]) > 3){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        elseif (count($transit[0]) == 3){ //The user's providing the entire file.
            //The header of the file (the first data entry) needs to be modified for proper product data file formatting
            $transit[0] = ProductHeader;
            
            //And now we dig through the rest of the uploaded file and assign values to $transit based on what's presented to us
            while (!feof($reader)){
                //Assign the next entry to transit
                $transit[$entries] = fgetcsv($reader);
                        
                //Check if the latest entry was an empty one
                if ($transit[$entries] == array(null, null, null) || $transit[$entries] == null){
                    //Remove the empty array
                    $trash = array_pop($transit);
                }
                else{ //Check all values of the entry and make sure it only has three columns
                    //Check the value in ID, generate one with numericIDGenerator if its missing or a value repeated earlier in the array
                    if($transit[$entries][0] == null || searchArray($transit, $transit[$entries][0]) != $entries){
                        $transit[$entries][0] = numericIDGenerator($entries, "product", $transit);
                    }
                    
                    //Check the value in name, use giberish to create one if its empty
                    if($transit[$entries][1] == null){
                        $transit[$entries][1] = giberish(rand());
                    }
                            
                    //Check if there's a value in cost, use randomDecimal if the price is missing
                    if ($transit[$entries][2] == null){
                        $transit[$entries][2] = randomDecimal(0, 1999.99);
                    }
                            
                    //Transit might have more values than just product name, cost, and ID, remove any extra values
                    while (count($transit[$entries]) > 3){
                        $trash = array_pop($transit[$entries]);
                    }
                            
                    //Increase entries to mark a complete data entry
                    $entries++;
                }
            }
        }
        else{ //The file is incompatible with the company file format, close it and tell the user about the upload failure
            fclose($reader);
            return "Fail";
        }
                
        //Close the file
        fclose($reader);
                
        //If the code above gave us more than one entry, save the data and tell the user that the upload was successful
        if ($entries > 1){
            saveFile($transit, $target);
            return "Pass";
        }
        else{ //Delete the uploaded file and tell the user about the failure
            unlink($input);
            return "Empty";
        }
                
    }
    catch (IOException $error){
        throw $error;
    }
}

//The sort array function takes an array and sorts from the lowest value to the highest value.
function sortArray ($sortThis){
    //This sort for loop is going to be a doozy...
    for ($examine = 0; $examine < count($sortThis); $examine++){
        //We'll need an inner loop for comparison purposes
        for ($track = $examine + 1; $track < count($sortThis); $track++){
            //Compare the value in examine's element to the value in track's element, if examine has the bigger value, swap the elements
            if ($sortThis[$examine] > $sortThis[$track]){
                $hold = $sortThis[$track];
                $sortThis[$track] = $sortThis[$examine];
                $sortThis[$examine] = $hold;
                        
                //Decrease $examine for not holding the array with the lowest ID, then break the loop.
                $examine--;
                break;
            }
        }
    }
            
    //Return the sorted array
    return $sortThis;
}

//The sort array function takes an 3D array like company, customer, or product and sorts it by the number in sortBy, which represents an element slot number
function sort3DArray ($sortThis, $sortBy){
    //This sort for loop is going to be a doozy...
    for ($examine = 1; $examine < count($sortThis); $examine++){
        //We'll need an inner loop for comparison purposes
        for ($track = $examine + 1; $track < count($sortThis); $track++){
            //Compare the ID in examine's array to the ID in track's array, if examine has the bigger ID, swap the arrays
            if ($sortThis[$examine][$sortBy] > $sortThis[$track][$sortBy]){
                $hold = $sortThis[$track];
                $sortThis[$track] = $sortThis[$examine];
                $sortThis[$examine] = $hold;
                        
                //Decrease $examine for not holding the array with the lowest ID, then break the loop.
                $examine--;
                break;
            }
        }
    }
            
    //Return the sorted array
    return $sortThis;
}

/* The uploadCompanyPriceFile function takes a link, extracts its contents, sorts them into the contents of a company price file (if it can), and returns its findings to the user.
    Results depend on the sucess of the upload and the contents recieved.*/
function uploadCompanyPriceFile ($fileAddress, $isActive){
    //Declare necessary arrays
    $transit = array(CompanyPriceHeader);
    $scrapyard = array(array(null, null, null));
    $companyIDs = array(null);
    $productIDs = array(null);
    
    //And trackers for transit and scrapyard
    $legalEntries = 1;
    $scraps = 0;
    
    //If we're lucky, we'll get to return this end message!
    $success = "Uploaded company product price data file successfully. Please note that we did change and/or sort everything, so load the file to see the changes (they tend to be rather drastic if your upload file isn't sorted alphabeticly, from lowest to highest value).<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
    
    //But if we're not, we'll have times where an entry gives us nothing, making this true
    $empty = true;
    
    //Regardless, this boolean will help with future value tracking
    $noID = false;
    
    //Prepare for file reading!
    try{
        //Open the file we were provided!
        $reader = fopen($fileAddress, "r");
        
        //Send the first header entry to the scrapyard
        $scrapyard[$scraps] = fgetcsv($reader);

        //Check if the file size is either three, four, five, or six columns long
        if (count($scrapyard[$scraps]) == 3 || count($scrapyard[$scraps]) == 4){ //Three or Four column entries, I guess everything's going to the scrapyard.
            
            //We'll need a boolean to help us track if the user provided a 3-column file or a 4-column file
            if (count($scrapyard[$scraps]) == 3){
                $noID = true;
            }
            
            //Dig through the file
            while (!feof($reader)){
                //Send the next entry to the scrapyard
                $scrapyard[$scraps] = fgetcsv($reader);
                
                //Before we check the next entry, we need to refer to $noID to know if we need to move the values up
                if ($noID){
                    //Company Price ID doesn't exist, so values need to move up to compensate for it
                    $scrapyard[$scraps][3] = $scrapyard[$scraps][2]; //Initialize product status
                    $scrapyard[$scraps][2] = intval($scrapyard[$scraps][1]); //Initialize product stock
                    $scrapyard[$scraps][1] = floatval($scrapyard[$scraps][0]); // Initialize Product Price
                    $scrapyard[$scraps][0] = null; //Empty the Identifier holder
                }
                
                //Check if the latest entry was an empty one
                if ($scrapyard[$scraps] == null || $scrapyard[$scraps] == array(null, null, null, null)){
                    //Remove the empty array
                    $trash = array_pop($scrapyard);
                }
                else {
                    //Check the values for null, then act accordingly if you find it. Start with product price
                    if (floatval($scrapyard[$scraps][1]) == null){
                        $scrapyard[$scraps][1] = 0.00;
                    }
                    else {
                        $empty = false;
                        
                        //We got an input from the user, but if it's less than 0, then its 0.
                        if (floatval($scrapyard[$scraps][1]) < 0){
                            $scrapyard[$scraps][1] = 0.00;
                        }
                        else {
                            $scrapyard[$scraps][1] = floatval($scrapyard[$scraps][1]);
                        }
                    }
                    
                    //Now check product stock
                    if (intval($scrapyard[$scraps][2]) == null){
                        $scrapyard[$scraps][2] = 0;
                    }
                    else{
                        $empty = false;
                        
                        //We got an input from the user, but if it's less than 0, then its 0.
                        if (intval($scrapyard[$scraps][2]) < 0){
                            $scrapyard[$scraps][2] = 0;
                        }
                        else {
                            $scrapyard[$scraps][2] = intval($scrapyard[$scraps][2]);
                        }
                    }
                    
                    //Now check if we have an active product
                    if ($scrapyard[$scraps][3] == null){
                        //Check the value in isActive, if true, set scrapyard 2 to Active, but set it to Inactive if it's false
                        if ($isActive){
                            $scrapyard[$scraps][3] = "Active";
                        }
                        else {
                            $scrapyard[$scraps][3] = "Inactive";
                        }
                    }
                    else {
                        $empty = false;
                        
                        //According to generateSalesOrder, anything that isn't active is inactive, let's make it so
                        if ($scrapyard[$scraps][3] != "Active"){
                            $scrapyard[$scraps][3] = "Inactive";
                        }
                    }
                    
                    //Check if we have an empty array
                    if ($empty && $scrapyard[$scraps][0] == null){
                        //Remove the empty array
                        $trash = array_pop($scrapyard);
                    }
                    else{
                        //Ensure that there are only four elements in the array
                        while (count($scrapyard[$scraps]) > 4){
                            $trash = array_pop($scrapyard[$scraps]);
                        }
                        
                        //Increase scraps because we have a new addition to the scrap yard
                        $scraps++;
                    }
                }
            }
        }
        elseif (count($scrapyard[$scraps]) == 5 || count($scrapyard[$scraps]) == 6){
            //Add an additional bit of information to success
            $success .= "<br>If you do not see an entry with a set of IDs you provided, those IDs don't exist in our company and product files.<br>If they did exist in those files, you'd be able to upload the missing IDs you were looking to upload.";
            
            //We'll need a boolean to help us track if the user provided a 5-column file or a 6-column file
            if (count($scrapyard[$scraps]) == 5){
                $noID = true;
            }
            
            //Dig through the file
            while (!feof($reader)){
                //Send the next entry to the scrapyard
                $scrapyard[$scraps] = fgetcsv($reader);
                
                //Before we check the next entry, we need to refer to $noID to know if we need to move any value up
                if ($noID){
                    //Company Price ID doesn't exist, so values need to move up to compensate for it
                    $scrapyard[$scraps][5] = $scrapyard[$scraps][4]; //Initialize product status
                    $scrapyard[$scraps][4] = intval($scrapyard[$scraps][3]); //Initialize product stock
                    $scrapyard[$scraps][3] = floatval($scrapyard[$scraps][2]); // Initialize Product Price
                    $scrapyard[$scraps][2] = $scrapyard[$scraps][1]; //Initialize product ID
                    $scrapyard[$scraps][1] = $scrapyard[$scraps][0]; //Initialize company ID
                    $scrapyard[$scraps][0] = null; //Empty the Identifier holder
                }
                
                //Check if the latest entry was an empty one
                if ($scrapyard[$scraps] == null || $scrapyard[$scraps] == array(null, null, null, null, null, null)){
                    //Remove the empty array
                    $trash = array_pop($scrapyard);
                }
                else {
                    //Check the values for null, then act accordingly if you find it. Start with product price
                    if (floatval($scrapyard[$scraps][3]) == null){
                        $scrapyard[$scraps][3] = 0.00;
                    }
                    else {
                        $empty = false;
                        
                        //We got an input from the user, but if it's less than 0, then its 0.
                        if (floatval($scrapyard[$scraps][3]) < 0){
                            $scrapyard[$scraps][3] = 0;
                        }
                        else {
                            $scrapyard[$scraps][3] = floatval($scrapyard[$scraps][3]);
                        }
                    }
                    
                    //Now check product stock
                    if (intval($scrapyard[$scraps][4]) == null){
                        $scrapyard[$scraps][4] = 0;
                    }
                    else{
                        $empty = false;
                        
                        //We got an input from the user, but if it's less than 0, then its 0.
                        if (intval($scrapyard[$scraps][4]) < 0){
                            $scrapyard[$scraps][4] = 0;
                        }
                        else {
                            $scrapyard[$scraps][4] = intval($scrapyard[$scraps][4]);
                        }
                    }
                    
                    //Now check if we have an active product
                    if ($scrapyard[$scraps][5] == null){
                        //Check the value in isActive, if true, set scrapyard 2 to Active, but set it to Inactive if it's false
                        if ($isActive){
                            $scrapyard[$scraps][5] = "Active";
                        }
                        else {
                            $scrapyard[$scraps][5] = "Inactive";
                        }
                    }
                    else {
                        $empty = false;
                        
                        //According to generateSalesOrder, anything that isn't active is inactive, let's make it so
                        if ($scrapyard[$scraps][5] != "Active"){
                            $scrapyard[$scraps][5] = "Inactive";
                        }
                    }
                    
                    //Check if we have IDs that aren't the company price ID in our array
                    if ($scrapyard[$scraps][1] != null && $scrapyard[$scraps][2] != null){
                        //If the IDs are new, add them to the ID collections, starting with company ID
                        if ($companyIDs == array(null)){
                            //First one's free
                            $companyIDs[0] = $scrapyard[$scraps][1];
                        }
                        else{
                            //After that, we need to start making comparisons
                            $noMatch = true;
                            foreach($companyIDs as $identity){
                                if ($scrapyard[$scraps][1] == $identity){
                                    $noMatch = false;
                                    break;
                                }
                            }

                            //Only add a new ID to companyIDs if there are no matches
                            if ($noMatch){
                                $companyIDs[count($companyIDs)] = $scrapyard[$scraps][1];
                            }
                        }

                        //Now check product ID
                        if ($productIDs == array(null)){
                            //First one's free
                            $productIDs[0] = $scrapyard[$scraps][2];
                        }
                        else{
                            //After that, we need to start making comparisons
                            $noMatch = true;
                            foreach($productIDs as $identity){
                                if ($scrapyard[$scraps][2] == $identity){
                                    $noMatch = false;
                                    break;
                                }
                            }

                            //Only add a new ID to productIDs if there are no matches
                            if ($noMatch){
                                $productIDs[count($productIDs)] = $scrapyard[$scraps][2];
                            }
                        }
                        
                        //Ensure that the array's length is six columns long
                        while (count($scrapyard[$scraps]) > 6){
                            $trash = array_pop($scrapyard[$scraps]);
                        }

                        //Add check to the current transit entry
                        $transit[$legalEntries] = $scrapyard[$scraps];

                        //Increase legalEntries to ready it for the next entry
                        $legalEntries++;
                    }
                    else {
                        //Check if we have an empty array
                        if ($empty && $scrapyard[$scraps][0] == null){
                            //Remove the empty array
                            $trash = array_pop($scrapyard);
                        }
                        else{
                            //Set the scrapyard entry to its ID and the last three entries
                            $scrapyard[$scraps] = array($scrapyard[$scraps][0], $scrapyard[$scraps][3], $scrapyard[$scraps][4], $scrapyard[$scraps][5]);

                            //Increase scraps because we have a new addition to the scrap yard
                            $scraps++;
                        }
                    }
                }
            }
        }
        else{
            //Close the reader and tell the user the upload failed due to an inadequate number of file columns
            fclose($reader);
            return array(null, null, null, null, "Uploaded file is incompatable with the company product price format, failed to upload company product price data file to the system.<br>Try uploading a file with three or five columns next time, like the recommendation in the generate command page told you to do.");
        }
        
        //Close the reader
        fclose($reader);
        
        //Sort the arrays stored in the company IDs and the productIDs
        $companyIDs = sortArray($companyIDs);
        $productIDs = sortArray($productIDs);
        
        //Return an array of necessary information
        return array($transit, $scrapyard, $companyIDs, $productIDs, $success);
    }
    catch (IOException $error){
        throw $error;
        
        //Send an error message
        return array(null, null, null, null, "Uploaded file could not be read, failed to upload company product price data file to the system");
    }
}

/* The uploadCustomerInterestFile function takes a link, extracts its contents, sorts them into the contents of a customer interest file (if it can), and returns the findings to the user.
    Results depend on the sucess of the upload and the contents recieved.*/
function uploadCustomerInterestFile ($fileAddress){
    //Declare necessary arrays
    $transit = array(CustomerInterestHeader);
    $scrapyard = array(array(null, null, null));
    $customerIDs = array(null);
    $productIDs = array(null);
    
    //And trackers for transit and scrapyard
    $legalEntries = 1;
    $scraps = 0;
    
    //If we're lucky, we'll get to return this end message!
    $success = "Uploaded customer product interest data file successfully. Please note that we did change and/or sort everything, so load the file to see the changes (they tend to be rather drastic if your upload file isn't sorted alphabeticly, from lowest to highest value).<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
    
    //But if we're not, we'll have times where an entry gives us nothing, making this true
    $empty = true;
    
    //Regardless, this boolean will help with future value tracking
    $noID = false;
    
    //Prepare for file reading!
    try{
        //Open the file we were provided!
        $reader = fopen($fileAddress, "r");
        
        //Send the first header entry to the scrapyard
        $scrapyard[$scraps] = fgetcsv($reader);
        
        //Check if the file size is either three, four, five, or six columns long
        if (count($scrapyard[$scraps]) == 3 || count($scrapyard[$scraps]) == 4){ //Three or Four column entries, I guess everything's going to the scrapyard.
            
            //We'll need a boolean to help us track if the user provided a 3-column file or a 4 -column file
            if (count($scrapyard[$scraps]) == 3){
                $noID = true;
            }
            
            //Dig through the file
            while (!feof($reader)){
                //Send the next entry to the scrapyard
                $scrapyard[$scraps] = fgetcsv($reader);
                
                //Before we check the next entry, we need to refer to $noID to know if we need to move the values up
                if ($noID){
                    //Customer Interest ID doesn't exist, so values need to move up to compensate for it
                    $scrapyard[$scraps][3] = intval($scrapyard[$scraps][2]); //Initialize customer purchase maximum if purchase occurs
                    $scrapyard[$scraps][2] = intval($scrapyard[$scraps][1]); //Initialize customer purcahse minimum if purchase occurs
                    $scrapyard[$scraps][1] = intval($scrapyard[$scraps][0]); //Initialize customer interest
                    $scrapyard[$scraps][0] = null; //Empty the Identifier element
                }
                
                //Check if the latest entry was an empty one
                if ($scrapyard[$scraps] == null || $scrapyard[$scraps] == array(null, null, null)){
                    //Remove the empty array
                    $trash = array_pop($scrapyard);
                }
                else {
                    //Check the values for null, then act accordingly if you find it. Save the IDs for generation and start with customer interest
                    if (intval($scrapyard[$scraps][1]) == null){
                        $scrapyard[$scraps][1] = 0;
                    }
                    else {
                        //If the input is less than 0, set it to 0
                        if (intval($scrapyard[$scraps][1]) < 0){
                            $scrapyard[$scraps][1] = 0;
                        } //If the input is over 10, set it to 10
                        elseif (intval($scrapyard[$scraps][1]) > 10){
                            $scrapyard[$scraps][1] = 10;
                        } //No need for change, save as is!
                        else { 
                            $scrapyard[$scraps][1] = intval($scrapyard[$scraps][1]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Now check the minimum amount of customer product purchases (if they purchase)
                    if (intval($scrapyard[$scraps][2]) == null){
                        $scrapyard[$scraps][2] = 0;
                    }
                    else{
                        //If the input is less than 0, set it to 0, otherwise you can save it as is
                        if (intval($scrapyard[$scraps][2]) < 0){
                            $scrapyard[$scraps][2] = 0;
                        }
                        else{
                            $scrapyard[$scraps][2] = intval($scrapyard[$scraps][2]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Now check the maximum amount of customer product purchases (if they purchase)
                    if (intval($scrapyard[$scraps][3]) == null){
                        $scrapyard[$scraps][3] = 0;
                    }
                    else {
                        //If the input is less than 0, set it to 0, otherwise you can save it as is
                        if (intval($scrapyard[$scraps][3]) < 0){
                            $scrapyard[$scraps][3] = 0;
                        }
                        else{
                            $scrapyard[$scraps][3] = intval($scrapyard[$scraps][3]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Check if we have an empty array
                    if ($empty){
                        //Remove the empty array
                        $trash = array_pop($scrapyard);
                    }
                    else{
                        //Ensure that the max purchase amount is more than the minimum, generate a random value between the minimum and 100 above if it's not
                        if ($scrapyard[$scraps][3] < $scrapyard[$scraps][2]){
                            $scrapyard[$scraps][3] = rand($scrapyard[$scraps][2], $scrapyard[$scraps][2] + 100);
                        }
                        
                        //Ensure that there are only four elements in the array
                        while (count($scrapyard[$scraps]) > 4){
                            $trash = array_pop($scrapyard[$scraps]);
                        }
                        
                        //Increase scraps because we have a new addition to the scrap yard
                        $scraps++;
                    }
                }
            }
        }
        elseif (count($scrapyard[$scraps]) == 5 || count($scrapyard[$scraps]) == 6){
            //Add an additional bit of information to success
            $success .= "<br>If you do not see an entry with a set of IDs you provided, those IDs don't exist in our customer and product files.<br>If they did exist in those files, you'd be able to upload the missing IDs you were looking to upload.";
            
            //We'll need a boolean to help us track if the user provided a 5-column file or a 6-column file
            if (count($scrapyard[$scraps]) == 5){
                $noID = true;
            }
            
            //Dig through the file
            while (!feof($reader)){
                //Send the next entry to the scrapyard
                $scrapyard[$scraps] = fgetcsv($reader);
                
                //Before we check the next entry, we need to refer to $noID to know if we need to move the values up
                if ($noID){
                    //Customer Interest ID doesn't exist, so values need to move up to compensate for it
                    $scrapyard[$scraps][5] = intval($scrapyard[$scraps][4]); //Initialize customer purchase maximum if purchase occurs
                    $scrapyard[$scraps][4] = intval($scrapyard[$scraps][3]); //Initialize customer purcahse minimum if purchase occurs
                    $scrapyard[$scraps][3] = intval($scrapyard[$scraps][2]); //Initialize customer interest
                    $scrapyard[$scraps][2] = intval($scrapyard[$scraps][1]); //Initialize product ID
                    $scrapyard[$scraps][1] = intval($scrapyard[$scraps][0]); //Initialize customer ID
                    $scrapyard[$scraps][0] = null; //Empty the identifier element
                }
                
                //Check if the latest entry was an empty one
                if ($scrapyard[$scraps] == null || $scrapyard[$scraps] == array(null, null, null, null, null)){
                    //Remove the empty array
                    $trash = array_pop($scrapyard);
                }
                else {
                    //Check the values for null, then act accordingly if you find it. Save the identifier for generation and start with customer interest
                    if (intval($scrapyard[$scraps][3]) == null){
                        $scrapyard[$scraps][3] = 0.00;
                    }
                    else {
                        //If the input is less than 0, set it to 0
                        if (intval($scrapyard[$scraps][3]) < 0){
                            $scrapyard[$scraps][3] = 0;
                        } //If the input is over 10, set it to 10
                        elseif (intval($scrapyard[$scraps][3]) > 10){
                            $scrapyard[$scraps][3] = 10;
                        } //No need for change, save as is!
                        else { 
                            $scrapyard[$scraps][3] = intval($scrapyard[$scraps][3]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Now check product stock
                    if (intval($scrapyard[$scraps][4]) == null){
                        $scrapyard[$scraps][4] = 0;
                    }
                    else{
                        //If the input is less than 0, set it to 0, otherwise you can save it as is
                        if (intval($scrapyard[$scraps][4]) < 0){
                            $scrapyard[$scraps][4] = 0;
                        }
                        else{
                            $scrapyard[$scraps][4] = intval($scrapyard[$scraps][4]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Now check if we have an active product
                    if ($scrapyard[$scraps][5] == null){
                        $scrapyard[$scraps][5] = 0;
                    }
                    else {
                        //If the input is less than 0, set it to 0, otherwise you can save it as is
                        if (intval($scrapyard[$scraps][5]) < 0){
                            $scrapyard[$scraps][5] = 0;
                        }
                        else{
                            $scrapyard[$scraps][5] = intval($scrapyard[$scraps][5]);
                        }
                        
                        //Regardless of the input, treat it like a value and set $empty to false
                        $empty = false;
                    }
                    
                    //Check if we have IDs in our array
                    if ($scrapyard[$scraps][1] != null && $scrapyard[$scraps][2] != null){
                        //If the IDs are new, add them to the ID collections, starting with customer ID
                        if ($customerIDs == array(null)){
                            //First one's free
                            $customerIDs[0] = $scrapyard[$scraps][1];
                        }
                        else{
                            //After that, we need to start making comparisons
                            $noMatch = true;
                            foreach($customerIDs as $identity){
                                if ($scrapyard[$scraps][1] == $identity){
                                    $noMatch = false;
                                    break;
                                }
                            }

                            //Only add a new ID to customerIDs if there are no matches
                            if ($noMatch){
                                $customerIDs[count($customerIDs)] = $scrapyard[$scraps][1];
                            }
                        }

                        //Now check product ID
                        if ($productIDs == array(null)){
                            //First one's free
                            $productIDs[0] = $scrapyard[$scraps][2];
                        }
                        else{
                            //After that, we need to start making comparisons
                            $noMatch = true;
                            foreach($productIDs as $identity){
                                if ($scrapyard[$scraps][2] == $identity){
                                    $noMatch = false;
                                    break;
                                }
                            }

                            //Only add a new ID to productIDs if there are no matches
                            if ($noMatch){
                                $productIDs[count($productIDs)] = $scrapyard[$scraps][2];
                            }
                        }
                        
                        //Ensure that the array's size is five columns long
                        while (count($scrapyard[$scraps]) > 6){
                            $trash = array_pop($scrapyard[$scraps]);
                        }

                        //Add check to the current transit entry
                        $transit[$legalEntries] = $scrapyard[$scraps];

                        //Increase legalEntries to ready it for the next entry
                        $legalEntries++;
                    }
                    else {
                        //Check if we have an empty array
                        if ($empty){
                            //Remove the empty array
                            $trash = array_pop($scrapyard);
                        }
                        else{
                            //Set the scrapyard entry to its last three entries
                            $scrapyard[$scraps] = array($scrapyard[$scraps][0], $scrapyard[$scraps][3], $scrapyard[$scraps][4], $scrapyard[$scraps][5]);

                            //Increase scraps because we have a new addition to the scrap yard
                            $scraps++;
                        }
                    }
                }
            }
        }
        else {
            //Close the reader and tell the user the upload failed due to an inadequate number of file columns
            fclose($reader);
            return array(null, null, null, null, "Uploaded file is incompatable with the customer product interest format, failed to upload customer product interest data file to the system.<br>Try uploading a file with three or five columns next time, like the recommendation in the generate command page told you to do.");
        }
        
        //Close the reader
        fclose($reader);
        
        //Sort the arrays stored in the customer IDs and the productIDs
        $customerIDs = sortArray($customerIDs);
        $productIDs = sortArray($productIDs);
        
        //Return an array of necessary information
        return array($transit, $scrapyard, $customerIDs, $productIDs, $success);
    }
    catch (IOException $error){
        //Throw the error
        throw $error;
        
        //Send an error message array
        return array(null, null, null, null, "Uploaded file could not be read, failed to upload customer product interest data file to the system");
    }
}

/* The generateCompanyPriceData function takes a link and four arrays representing entries with and without IDs, an array containing company IDs and an array containing product IDs and uses them to save data to a the link provided. 
   Results depend on the success of the upload and the contents recieved.*/
function generateCompanyPriceData ($fileAddress, $transit, $scrapyard, $companyIDs, $productIDs){
    //Create a tracker for the scrapyard parameter, the number of identifers we've generated, the number of entries we've saved, and (if necessary) prices.
    $scraps = 0;
    $entry = 0;
    $identifiers = 1;
    $saves = 1;
        
    //Don't run the next loop without productIDs
    if ($productIDs != null){
        //We'll also want an array for product prices, but we'll want to initialize each element in the array to evade future errors
        for ($track = 0; $track < count($productIDs); $track++){
            $productPrices[$track] = null;
        }
    }
        
    //The rest of the function depends on a file
    try{
        //If we are working with the local system (CompanyPriceLink) we can initialize the price reference with items that aren't null
        if ($fileAddress == CompanyPriceLink){
            //Load the product file so there's something to reference
            $tempArray = loadFile(ProductLink);
                
            //Don't run the next loop without productIDs
            if ($productIDs != null){
                //Check if an ID matches an entry in the productID array
                foreach ($productIDs as $products){
                    foreach ($tempArray as $archive){
                        if ($products == $archive[0]){
                            //If we have an ID match, save it to the productPrices array, increase entry, and break the loop. (I hope it doesn't break both loops).
                            $productPrices[$entry] = $archive[2];
                            $entry++;
                            break;
                        }
                    }
                }
            }
        }
            
        //Initialize an array we'll save later with the headers to the file with the first array in transit (use the header constant if you don't have it)
        if ($transit == null || $transit[0] == null){
            $saveLater[0] = CompanyPriceHeader;
        }
        else{
            $saveLater[0] = $transit[0];
        }
            
        //Don't run the loop if we don't have company or product IDs. 
        if ($companyIDs != null && $productIDs != null){
            //Run a for loop in a foreach loop to populate the file
            foreach($companyIDs as $companies){
                for($products = 0; $products < count($productIDs); $products++){
                    //Set entry to -1 so it's repurposed for tracking the entries in transit and that it doesn't trigger the matching ID set condition used later
                    $entry = -1;

                    //Check if transit exists and see if it has an entry that matches the provided IDs
                    if ($transit != null){
                        for ($contents = 0; $contents < count($transit); $contents++){
                            if ($transit[$contents][1] == $companies && $transit[$contents][2] == $productIDs[$products]){
                                $entry = $contents;
                                break;
                            }
                        }
                    }

                    //If we have an entry match, save it after checking its identifier. The entry variable will change back to -1 in the next loop's iteration.
                    if ($entry > -1){
                        //Check if the there's a value in the ID element of the data entry
                        if ($transit[$entry][0] == null){
                            //No ID? Generate IDs with saveLater, transit, and scrapyard as references until they all agree on an identifier.
                            do {
                                //Set the Identifier to test in the array we'll eventually save
                                $transit[$entry][0] = numericIDGenerator($identifiers, "companyPrice", $saveLater);

                                //Generate test values with the transit and scrapyard arrays
                                $testTransit = numericIDGenerator($identifiers, "companyPrice", $transit);
                                $testScrapyard = numericIDGenerator($identifiers, "companyPrice", $scrapyard);

                                //Increase the value in identifiers to ensure the value isn't repeated again
                                $identifiers++;
                            } while ($transit[$entry][0] != $testTransit && $transit[$entry][0] != $testScrapyard && $testTransit != $testScrapyard);
                        }

                        $saveLater[$saves] = $transit[$entry];
                    }
                    else {
                        //If there's no match, we'll check if there's a scrapyard and if there are still contents for us to use.
                        if ($scrapyard != null && $scraps < count($scrapyard)){
                            //Add the scrapyard's next entry to the save later array
                            $saveLater[$saves] = array($scrapyard[$scraps][0], $companies, $productIDs[$products], $scrapyard[$scraps][1], $scrapyard[$scraps][2], $scrapyard[$scraps][3]);

                            //Increase the number of used scraps
                            $scraps++;
                        } //No scrap, guess we have to generate the entry
                        else {
                            //Store an incomplete array in the save later array, we'll be completing it shortly
                            $saveLater[$saves] = array(null, $companies, $productIDs[$products], null, null, null);

                            /* Start with product price, 
                               This element will use the productPrice array, 
                               If the source file is part of the system, the array will already be popluated 
                               If an entry isn't populated, generate a value between 0 and 1999.99 for it*/ 
                            if ($productPrices[$products] == null){
                                $productPrices[$products] = randomDecimal(0, 1999.99);
                            }
                            //Now that we know that the productPrice element in question is filled, we can use it to generate a price
                            $saveLater[$saves][3] = randomDecimal($productPrices[$products], $productPrices[$products] * 2.5);

                            //Now generate a random number for stock, regardless of fileAddress, it will always be between 0 and 1000
                            $saveLater[$saves][4] = rand(0, 1000);

                            //Finally, fill the product's active status, the default status depends on the source file
                            if ($fileAddress == CompanyPriceLink){
                                //If we're working with the file above, the default status will be Active
                                $saveLater[$saves][5] = "Active";
                            }
                            else {
                                //If we're working with anything else, the default status will be Inactive
                                $saveLater[$saves][5] = "Inactive";
                            }
                        }

                        //Check if the ID element of the current element of the save later array is filled. If not, then fill it.
                        if ($saveLater[$saves][0] == null){
                            //No ID, generate IDs with saveLater, transit, and scrapyard as references until they all agree on an identifier.
                            do{
                                //Set the Identifier to test in the array we'll eventually save
                                $saveLater[$saves][0] = numericIDGenerator($identifiers, "companyPrice", $saveLater);

                                //Generate test values with the transit and scrapyard arrays
                                $testTransit = numericIDGenerator($identifiers, "companyPrice", $transit);
                                $testScrapyard = numericIDGenerator($identifiers, "companyPrice", $scrapyard);

                                //Increase the value in identifiers to ensure the value isn't repeated again
                                $identifiers++;
                            } while ($saveLater[$saves][0] != $testTransit && $saveLater[$saves][0] != $testScrapyard && $testTransit != $testScrapyard);
                        }
                    }

                    //Increase the number of saved entries so we don't overwrite the entry we just wrote
                    $saves++;
                }
            }
        }
        
        //Open the file
        $reader = fopen($fileAddress, "w");
        
        //Run a foreach loop to save the data in the save later array
        foreach ($saveLater as $saveNow){
            fputcsv($reader, $saveNow);
        }
            
        //Close the file
        fclose($reader);
            
        //If the file we're using is in the system, update its relatives
        if ($fileAddress == CompanyPriceLink){
            updateFiles("companyPrice");
        }
            
        //Tell the user that the data generation was successful
        return "Company Product Price Data File Generated";
    }
    catch (IOException $error){
        throw $error;
            
        //Error message
        return "Failed to generate company product price data.";
    }
}

/* The generateCustomerInterestData function takes a link and four arrays representing entries with and without IDs, an array containing customer IDs and an array containing product IDs and uses them to save data to a the link provided. 
   Results depend on the success of the upload and the contents recieved.*/
function generateCustomerInterestData ($fileAddress, $transit, $scrapyard, $customerIDs, $productIDs){
    //Create a tracker for the scrapyard parameter, the number of identifiers we've generated, and the number of entries we've saved
    $scraps = 0;
    $identifiers = 1;
    $saves = 1;
        
    //Initialize an array we'll save later with the headers to the file with the first array in transit (use the header constant if you don't have it)
    if ($transit == null || $transit[0] == null){
        $saveLater[0] = CustomerInterestHeader;
    }
    else{
        $saveLater[0] = $transit[0];
    }
        
    //Run a nested foreach loop to populate the file, but only if we have the required identifiers
    if ($customerIDs != null && $productIDs != null){
        foreach($customerIDs as $customers){
            foreach($productIDs as $products){
                //Set entry to -1 so we're not always triggering the matching ID set condition used later
                $entry = -1;

                //Check if transit exists and see if it has an entry that matches the provided IDs
                if ($transit != null){
                    for ($contents = 0; $contents < count($transit); $contents++){
                        if ($transit[$contents][1] == $customers && $transit[$contents][2] == $products){
                            $entry = $contents;
                            break;
                        }
                    }
                }

                //If we have an entry match, save it after checking its identifier. The entry variable will change back to -1 in the next loop's iteration.
                if ($entry > -1){
                    //Check if there's a value in the ID element of the data entry
                    if ($transit[$entry][0] == null){
                        //No ID? Generate IDs with saveLater, transit, and scrapyard as references until they all agree on an identifier.
                        do {
                            //Set the Identifier to test in the array we'll eventually save
                            $transit[$entry][0] = numericIDGenerator($identifiers, "customerInterest", $saveLater);

                            //Generate test values with the transit and scrapyard arrays
                            $testTransit = numericIDGenerator($identifiers, "customerInterest", $transit);
                            $testScrapyard = numericIDGenerator($identifiers, "customerInterest", $scrapyard);

                            //Increase the value in identifiers to ensure the value isn't repeated again
                            $identifiers++;
                        } while ($transit[$entry][0] != $testTransit && $transit[$entry][0] != $testScrapyard && $testTransit != $testScrapyard);
                    }

                    $saveLater[$saves] = $transit[$entry];
                }
                else {
                    //If there's no match, we'll check if there's a scrapyard and if there are still contents for us to use.
                    if ($scrapyard != null && $scraps < count($scrapyard)){
                        //Add the scrapyard's next entry to the save later array
                        $saveLater[$saves] = array($scrapyard[$scraps][0], $customers, $products, $scrapyard[$scraps][1], $scrapyard[$scraps][2], $scrapyard[$scraps][3]);

                        //Increase the number of used scraps
                        $scraps++;
                    } //No scrap, guess we have to generate the entry
                    else {
                        //Store an incomplete array in the save later array, we'll be completing it shortly
                        $saveLater[$saves] = array(null, $customers, $products, null, null, null);

                        //Start with customer interest and a number between 0 and 10
                        $saveLater[$saves][3] = rand(0, 10);

                        //Now generate the minimum customer purchases if they do purchase with a number between 1 and 20
                        $saveLater[$saves][4] = rand(1, 20);

                        //Finally, generate the maximum customer purchase if they do purchase with a number between their minimum purchases and 100 above
                        $saveLater[$saves][5] = rand($saveLater[$saves][4], $saveLater[$saves][4] + 100);
                    }

                    //Check if the ID element of the current element of the save later array is filled. If not, then fill it.
                    if ($saveLater[$saves][0] == null){
                        //No ID, generate IDs with saveLater, transit, and scrapyard as references until they all agree on an identifier.
                        do{
                            //Set the Identifier to test in the array we'll eventually save
                            $saveLater[$saves][0] = numericIDGenerator($identifiers, "customerInterest", $saveLater);

                            //Generate test values with the transit and scrapyard arrays
                            $testTransit = numericIDGenerator($identifiers, "customerInterest", $transit);
                            $testScrapyard = numericIDGenerator($identifiers, "customerInterest", $scrapyard);

                            //Increase the value in identifiers to ensure the value isn't repeated again
                            $identifiers++;
                        } while ($saveLater[$saves][0] != $testTransit && $saveLater[$saves][0] != $testScrapyard && $testTransit != $testScrapyard);
                    }
                }
                //Increase the number of saved entries so we don't overwrite the entry we just wrote
                $saves++;
            }
        }
    }
    
    //Save the data in the save later array
    try{
        //Open the file
        $reader = fopen($fileAddress, "w");
        
        //Run a foreach loop to save the data
        foreach ($saveLater as $saveNow){
            fputcsv($reader, $saveNow);
        }
        
        //Close the file
        fclose($reader);
        
        //If the file we're using is in the system, update its relatives
        if ($fileAddress == CustomerInterestLink){
            updateFiles("customerInterest");
        }
        
        //Tell the user that the data generation was successful
        return "Customer Product Interest Data Dile Generated";
    }
    catch (IOException $error){
        throw $error;
        
        //Error Message
        return "Failed to generate customer product interest data.";
    }
}
?>