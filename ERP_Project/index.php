<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>ERP Data Generation Menu</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Stylesheet Reference -->
        <link rel="stylesheet" href="Styles/classStyles.css">
    </head>

    <body>
        <!-- Intro -->
        <h1>ERP Data Generation Menu</h1>
        <h3 class="sideNote">There wasn't enough time to add style, but it will come later.</h3>
        
        <!-- PHP code for variable assignment and the generation functions below -->
        <?php require 'Scripts/functions.php';
            //Data generation can take a long time, so let's disable the time limit so the program can get the job done.
            set_time_limit(0);
        
            //There are five variables that the functions below concern themselves with: link, file, action, firstCount, and secondCount.
            $link = null;
            $file = null;
            $action = "None";
            $firstCount = 0;
            $secondCount = 0;
        
            //Check if we're got data from a post method before we start assigning data.
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
                //Retrieve the action from the source page and give it to action
                if(!empty($_POST["action"])){
                    $action = inputSecurity($_POST["action"]);
                }
                
                //Check if there was what file type we've recieved, if we've recieved a file type
                if(!empty($_POST["dataFile"])){
                    $file = inputSecurity($_POST["dataFile"]);
                    
                    //If there is a value in from, call the delete foreign file function to handle potential upload bloat.
                    if(!empty($_POST["from"])){
                        deleteForeignFiles($file, inputSecurity($_POST["from"]));
                    }
                }
                
                //Check the value of action to see if it's generate. We'll also want to know if file has a value so we're not blindly generating data
                if ($action == "generate" && $file != null){
                    //We're going to be generating data! Now let's get some pieces of data that belong to both types of generation, starting with the file's address
                    if(!empty($_POST["upload"])){
                        $link = inputSecurity($_POST["upload"]);
                    }
                    
                    //Check the value of link, if it's null, then we're going to need more values
                    if ($link == "null"){
                        //If we're here, we're generating the data, this calls for a number input if the action says we're not generating for companyPrice or customerInterest
                        if(!empty($_POST["primaryCount"])){
                            $firstCount = inputSecurity($_POST["primaryCount"]);
                        }
                        
                        //We only need a secondary count if we're generating customers or sales orders
                        if(!empty($_POST["secondaryCount"])){
                            $secondCount = inputSecurity($_POST["secondaryCount"]);
                        }
                        
                        //But if we're generating sales orders, we'll need help from charile and delta
                        if(!empty($_POST["charliesCount"])){
                            $thirdCount = inputSecurity($_POST["charliesCount"]);
                        }
                        
                        if(!empty($_POST["deltasCount"])){
                            $fourthCount = inputSecurity($_POST["deltasCount"]);
                        }
                    }
                }
            }
        ?>
        
        <!-- Sales Order Form -->
        <div class="threeColumns">
            <h2>Sales Order Data</h2>
            <p class="sideNote">Please generate data in the five data types below before generating a sales order form.
                <br>Caution: A Sales Order file can take a long time to generate when there are a lot of data entries in the company, customer, and product files it's referencing, so be prepared for a long wait when generating Sales Order Data when playing with big numbers.</p>
            <form method="post">
                <button type="submit" id="createSalesOrder" name="saved" value="select" formaction="createSalesOrder.php">Create/Edit Sales Order</button>
                <br>
                <br>
                <button type="submit" id="generateSalesOrder" name="generate" value="salesOrder" formaction="generate.php">Generate Sales Orders</button>
                <?php //Check if we need to generate sales order data
                    if ($action == "generate" && $file == "salesOrder"){
                        //Before running any of these functions, we need to know the prerequisite files exist
                        if (file_exists(CompanyLink) && file_exists(CustomerLink) && file_exists(ProductLink) && file_exists(CompanyPriceLink) && file_exists(CustomerInterestLink)){
                            //Now that we know the prerequisite exists, check if we have a link so we know how to generate the data
                            if ($link == "null"){ //No file, we're generating the order
                                echo generateSalesOrder($firstCount, $secondCount, $thirdCount, $fourthCount, null, null);
                            }
                            else{ //There's a file, we're uploading the data
                                echo uploadSalesOrder();
                            }
                        }
                        else {
                            //Oh no, we're missing a required file! Better send an error message.
                            echo "A prerequisite file was missing. Failed to generate sales data.";

                            //If we had an attempted upload, add an extra message
                            if ($link != "null"){
                                echo "<br>Yes, a data generation error can occur when uploading data.<br>When generating data for sales orders, we use all of the prerequisite files.<br>These prerequisite files are company, customer, product, company product price, and customer product interest.<br>Be sure you have all of the prerequisite files generated or upload before you try another upload.";
                            }
                        }
                    }
                ?>
                <br>
                <br>
                <button type="submit" id="loadSalesOrder" name="dataFile" value="salesOrder" formaction="loadFile.php">Load Sales Orders</button>
            </form>
            <br>
        </div>
        
        <!-- Company Form -->
        <div class="threeColumns">
            <h3>Company Data Generation Menu</h3>
            <form method="post">
                <button type="submit" id="createCompany" name="create" value="company" formaction="createCompany.php"  formmethod="get">Create Companies</button>
                <br>
                <br>
                <button type="submit" id="generateCompany" name="generate" value="company" formaction="generate.php">Generate Companies</button>
                <?php //Check if we need to generate company data
                    if ($action == "generate" && $file == "company"){
                        //Check if we have a link so we know how to generate the data
                        if ($link == "null"){ //No file, we're generating the companies
                            echo generateCompany($firstCount);
                        }
                        else{ //There's a file, we're uploading the data
                            echo uploadCompany();
                        }
                    }
                ?>
                <br>
                <br>
                <button type="submit" id="loadCompany" name="dataFile" value="company" formaction="loadFile.php">Load Companies</button>
            </form>
            <br>
        </div>
        
        <!-- Customer Form -->
        <div class="threeColumns">
            <h3>Customer Data Generation Menu</h3>
            <form method="post">
                <button type="submit" id="createCustomer" name="create" value="customer" formaction="createCustomer.php" formmethod="get">Create Customers</button>
                <br>
                <br>
                <button type="submit" id="generateCustomer" name="generate" value="customer" formaction="generate.php">Generate Customers</button>
                <?php //Check if we need to generate customer data
                    if ($action == "generate" && $file == "customer"){
                        //Check if we have a link so we know how to generate the data
                        if ($link == "null"){ //No file, we're generating the customers
                            echo generateCustomer($firstCount, $secondCount);
                        }
                        else{ //There's a file, we're uploading the data
                            echo uploadCustomer();
                        }
                    }
                ?>
                <br>
                <br>
                <button type="submit" id="loadCustomer" name="dataFile" value="customer" formaction="loadFile.php">Load Customers</button>
            </form>
            <br>
        </div>
        
        <!-- Product Form -->
        <h3>Product Data Generation Menu</h3>
        <form method="post">
            <button type="submit" id="createProduct" name="create" value="product" formaction="createProduct.php" formmethod="get">Create Products</button>
            <br>
            <br>
            <button type="submit" id="generateProduct" name="generate" value="product" formaction="generate.php">Generate Products</button>
            <?php //Check if we need to generate product data
                if ($action == "generate" && $file == "product"){
                    //Check if we have a link so we know how to generate the data
                    if ($link == "null"){ //No file, we're generating the products
                        echo generateProduct($firstCount);
                    }
                    else{ //There's a file, we're uploading the data
                        echo uploadProduct();
                    }
                }
            ?>
            <br>
            <br>
            <button type="submit" id="loadProduct" name="dataFile" value="product" formaction="loadFile.php">Load Products</button>
        </form>
        <br>
        
        <!-- Company Prices Form -->
        <h4>Company Product Price Data Generation Menu</h4>
        <p class="sideNote">Please generate Company and Product Data Before Generating Company Product Price data</p>
        <form method="post">
            <button type="submit" id="generateCompanyPrice" name="generate" value="companyPrice" formaction="generate.php">Generate Company Product Prices</button>
            <?php //Check if we need to generate data for company prices
                if ($action == "generate" && $file == "companyPrice"){
                    //We need data, but first, we need to know if it exists
                    if (file_exists(CompanyLink) && file_exists(ProductLink)){
                        //Now that we know our data exists, create arrays to house the IDs of the system's companys and products. Start by initializing variables for the company IDs reference
                        $companyIDs = null;
                        $productIDs = null;
                        $transit = loadFile(CompanyLink);
                        for($track = 1; $track < count($transit); $track++){
                            //Here's a variable to ensure the arrays start at 0
                            $field = $track - 1;
                            
                            //Now use it to add an ID to the company IDs array
                            $companyIDs[$field] = $transit[$track][0];
                        }

                        //Prepare product ID reference
                        $transit = loadFile(ProductLink);
                        for($track = 1; $track < count($transit); $track++){
                            //Here's a variable to ensure the arrays start at 0
                            $field = $track - 1;
                            
                            //Now use it to add an ID to the product IDs array
                            $productIDs[$field] = $transit[$track][0];
                        }

                        //Now that we have our IDs, let's check if we have a link so we know how to generate the data
                        if ($link == "null"){ //No file, we're generating the company product prices
                            echo generateCompanyPriceData(CompanyPriceLink, null, null, $companyIDs, $productIDs);
                        }
                        else{ //There's a file, we're uploading the data
                            echo uploadForeign($file, $productIDs, $companyIDs);
                        }
                    }
                    else {
                        //Oh no, we're missing a required file! Better send an error message.
                        echo "A prerequisite file was missing. Failed to generate company product price data.";
                        
                        //If we had an attempted upload, add an extra message
                        if ($link != "null"){
                            echo "<br>Yes, a data generation error can occur when uploading data.<br>We use both the local company file and the local product file in generation to ensure that the ID pairs in the uploaded file make sense to the system.<br>Now make sure you have both the local product and company file ready before trying another upload.";
                        }
                    }
                }
            ?>
            <br>
            <br>
            <button type="submit" id="loadCompanyPrice" name="dataFile" value="companyPrice" formaction="loadFile.php">Load Company Product Prices</button>
        </form>
        <br>
        
        <!-- Customer Interest Form -->
        <h4>Customer Product Intrest Data Generation Menu</h4>
        <p class="sideNote">Please generate Customer and Product Data Before Generating Customer Product Interest data</p>
        <form method="post">
            <button type="submit" id="generateCustomerInterest" name="generate" value="customerInterest" formaction="generate.php">Generate Customer Product Interest</button>
            <?php //Check if we need to generate data for customer interest
                if ($action == "generate" && $file == "customerInterest"){
                    //We need data, but first, we need to know if it exists
                    if (file_exists(CustomerLink) && file_exists(ProductLink)){
                        //Now that we know our data exists, create arrays to house the IDs of the system's customers and products. Start by initializing variables for the customer IDs reference
                        $customerIDs = null;
                        $productIDs = null;
                        $transit = loadFile(CustomerLink);
                        for($track = 1; $track < count($transit); $track++){
                            //Here's a variable to ensure the arrays start at 0
                            $field = $track - 1;
                            
                            //Now use it to add an ID to the customer IDs array
                            $customerIDs[$field] = $transit[$track][0];
                        }

                        //Prepare product ID reference
                        $transit = loadFile(ProductLink);
                        for($track = 1; $track < count($transit); $track++){
                            //Here's a variable to ensure the arrays start at 0
                            $field = $track - 1;
                            
                            //Now use it to add an ID to the product IDs array
                            $productIDs[$field] = $transit[$track][0];
                        }

                        //Now that we have our IDs, let's check if we have a link so we know how to generate the data
                        if ($link == "null"){ //No file, we're generating the customer product interests
                            echo generateCustomerInterestData(CustomerInterestLink, null, null, $customerIDs, $productIDs);
                        }
                        else{ //There's a file, we're uploading the data
                            echo uploadForeign($file, $productIDs, $customerIDs);
                        }
                    }
                    else {
                        //Oh no, we're missing a required file! Better send an error message.
                        echo "A prerequisite file was missing. Failed to generate customer product interest data.";
                        
                        //If we had an attempted upload, add an extra message
                        if ($link != "null"){
                            echo "<br>Yes, a data generation error can occur when uploading data.<br>We use both the local customer file and the local product file in generation to ensure that the ID pairs in the uploaded file make sense to the system.<br>Now make sure you have both the local product and customer file ready before trying another upload.";
                        }
                    }
                }
            ?>
            <br>
            <br>
            <button type="submit" id="loadCustomerInterest" name="dataFile" value="customerInterest" formaction="loadFile.php">Load Customer Product Interest</button>
        </form>
        <br>
    </body>
    
    <?php
    
    //The generateSalesOrder function generates sales orders based on the numbers provided and past data files
    function generateSalesOrder($months, $days, $startMonth, $startDay, $IDTransit, $noIDTransit){
        //The sales order data file has five prerquisite files, the variables below hold the links to those files.
        $companyLink = CompanyLink;
        $customerLink = CustomerLink;
        $productLink = ProductLink;
        $priceLink = CompanyPriceLink;
        $interestLink = CustomerInterestLink;
        
        //Check if these files exist,
        if (file_exists($companyLink) && file_exists($customerLink) && file_exists($productLink) && file_exists($priceLink) && file_exists($interestLink)){
            try{
                //Load the contents of the file links above into the arrays below
                $companyReference = loadFile($companyLink);
                $customerReference = loadFile($customerLink);
                $productReference = loadFile($productLink);
                $priceReference = loadFile($priceLink);
                $interestReference = loadFile($interestLink);
                
                //Declare five variables, one is a file related to the sales order data file, another is a 3D array starting with an array with the file's headers, which will be sent to a sales order data file. The other three are for tracking entries generated in the upload sales order function
                $input[0] = SalesOrderHeader;
                $file = fopen(SalesOrderLink, "w");
                $noIDEntries = 0;
                $IDEntries = -1;
                $identifiers = 1; //This is more of a tracker of identifiers generated
                $saves = 1;
                
                //Check where we got our data, if it's from upload (where one or both of the transit arrays), check our month and day parameters
                if ($IDTransit != null || $noIDTransit != null){
                    //Check if lowDay matches highDay and if lowMonth and highMonth, if they are, make them unequal.
                    if($startMonth == $months){
                        $startMonth = 1;
                    }
                    if($startDay == $days){
                        $startDay = $days / 2;
                    }
                }
                
                //And now, the mother of all nested for loops
                for ($companyTrack = 1; $companyTrack < count($companyReference); $companyTrack++){
                    for ($monthTrack = 1; $monthTrack <= $months; $monthTrack++){
                        for ($dayTrack = 1; $dayTrack <= $days; $dayTrack++){
                            for ($customerTrack = 1; $customerTrack < count($customerReference); $customerTrack++){
                                for ($productTrack = 1; $productTrack < count($productReference); $productTrack++){
                                    //Check if we're in an active day before generating data
                                    if ($monthTrack >= $startMonth){
                                        if ($monthTrack > $startMonth || $dayTrack >= $startDay){
                                            //Set up the next input array
                                            $input[$saves] = array(null, null, null, null, null, null, null, null, null, null, null, null, null);
                                            
                                            //Reset our success variables to their default state
                                            $IDEntries = -1;
                                            $saveSuccess = true;
                                            
                                            //Check if we have IDEntries before looking through them
                                            if ($IDTransit != null){
                                                //Check if the IDs match a set of IDs in IDTransit
                                                for ($IDTrack = 0; $IDTrack < count($IDTransit); $IDTrack++){
                                                    if ($IDTransit[$IDTrack][1] == $companyReference[$companyTrack][0] && $IDTransit[$IDTrack][2] == $monthTrack && $IDTransit[$IDTrack][3] == $dayTrack && $IDTransit[$IDTrack][4] == $customerReference[$customerTrack][0] && $IDTransit[$IDTrack][5] == $productReference[$productTrack][0]){
                                                        $IDEntries = $IDTrack;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            //Check the value of $IDEntries to see if it has changed
                                            if ($IDEntries > -1){
                                                //Use the values in the IDEntry to create the array we want to save
                                                $input[$saves] = array($IDTransit[$IDEntries][0], $IDTransit[$IDEntries][1], null, $IDTransit[$IDEntries][2], $IDTransit[$IDEntries][3], $IDTransit[$IDEntries][4], null, $IDTransit[$IDEntries][5], null, $IDTransit[$IDEntries][6], $IDTransit[$IDEntries][7], $IDTransit[$IDEntries][8], $IDTransit[$IDEntries][9]);

                                                //Fill the null values with values based on other values in the array
                                                foreach ($companyReference as $IDs){
                                                    if ($IDs[0] == $input[$saves][1]){
                                                        $input[$saves][2] = $IDs[1];
                                                        break;
                                                    }
                                                }

                                                foreach ($customerReference as $IDs){
                                                    if ($IDs[0] == $input[$saves][5]){
                                                        $input[$saves][6] = $IDs[3];
                                                        break;
                                                    }
                                                }

                                                foreach ($productReference as $IDs){
                                                    if ($IDs[0] == $input[$saves][7]){
                                                        $input[$saves][8] = $IDs[1];
                                                        break;
                                                    }
                                                }
                                                //Before we save the IDEntry in question, we have to make sure its final four (6-9) values aren't null, starting with the product price
                                                if (floatval($input[$saves][9]) == null){
                                                    //Run through two for loops to find the company price entry we need to reference to fill this value
                                                    for($CIDS = 1; $CIDS < count($companyReference); $CIDS++){
                                                        for($PIDS = 1; $PIDS < count($productReference); $PIDS++){
                                                            if ($input[$saves][1] == $companyReference[$CIDS][0] && $input[$saves][7] == $productReference[$PIDS][0]){
                                                                //Use CIDS and PIDS to determine what entry holds the company price
                                                                $targetEntry = $PIDS + (($CIDS - 1) * (count($productReference) - 1));

                                                                //Give the price in priceReference[$targetEntry][3] to $input[$saves][9]
                                                                $input[$saves][9] = $priceReference[$targetEntry][3];

                                                                //Break the loops
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                                else {
                                                    $input[$saves][9] = floatval($input[$saves][9]);
                                                }

                                                //Now check products sold
                                                if (intval($input[$saves][10]) == null){
                                                    //Run through two for loops to find the customer interest entry we need to reference to fill this value
                                                    for($CIDS = 1; $CIDS < count($customerReference); $CIDS++){
                                                        for($PIDS = 1; $PIDS < count($productReference); $PIDS++){
                                                            if ($input[$saves][5] == $customerReference[$CIDS][0] && $input[$saves][7] == $productReference[$PIDS][0]){
                                                                //Use CIDS and PIDS to determine what entry holds the customer purchase minimums and maximums
                                                                $targetEntry = $PIDS + (($CIDS - 1) * (count($productReference) - 1));

                                                                //Give a price between priceReference[$targetEntry][4] and priceReference[$targetEntry][5] to $input[$saves][10]
                                                                $input[$saves][10] = rand($interestReference[$targetEntry][4], $interestReference[$targetEntry][5]);

                                                                //Break the loops
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                                else {
                                                    $input[$saves][10] = intval($input[$saves][10]);
                                                }

                                                //Now check revenue
                                                if (floatval($input[$saves][11]) == null){
                                                    //Run through two for loops to find the company price entry we need to reference to fill this value
                                                    for($CIDS = 1; $CIDS < count($companyReference); $CIDS++){
                                                        for($PIDS = 1; $PIDS < count($productReference); $PIDS++){
                                                            if ($input[$saves][1] == $companyReference[$CIDS][0] && $input[$saves][7] == $productReference[$PIDS][0]){
                                                                //Use CIDS and PIDS to determine what entry holds the company price
                                                                $targetEntry = $PIDS + (($CIDS - 1) * (count($productReference) - 1));

                                                                //Multiply the value in input[$saves][10] by priceReference[$targetEntry][3] and save it to $input[$saves][11]
                                                                $input[$saves][11] = $input[$saves][10] * $priceReference[$targetEntry][3];

                                                                //Break the loops
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                                else {
                                                    $input[$saves][11] = floatval($input[$saves][11]);
                                                }

                                                //Now check costs
                                                if (floatval($input[$saves][12]) == null){
                                                    //Use a foreach loop to find the product we need to reference when reinitializing this value
                                                    foreach($productReference as $IDs){
                                                        if ($input[$saves][7] == $IDs[0]){
                                                            $input[$saves][12] = $input[$saves][10] * $IDs[2];
                                                        }
                                                    }
                                                }
                                                else {
                                                    $input[$saves][12] = floatval($input[$saves][12]);
                                                }
                                            }
                                            else{
                                                //Boolean Variable that determines if we generate a data entry or not
                                                $generate = false;
                                                
                                                //Integer variables that determine the active company price and customer interest entry
                                                $priceTarget = $productTrack + (($companyTrack - 1) * (count($productReference) - 1));
                                                $interestTarget = $productTrack + (($customerTrack - 1) * (count($productReference) - 1));

                                                /*Before we generate an entry, we have to determine if it's necessary, which requires running two tests.
                                                  Test 1: Determine if the company is selling the product with the following code*/
                                                if ($priceReference[$priceTarget][5] == "Active"){
                                                    $generate = true;
                                                }

                                                /*Test 2: Determine the value of input 10 (Products Sold), not only will we not have to generate it later, but if it's 0, we know an entry isn't needed.
                                                  input 10 should start at 0 so we can have a base of reference
                                                  Let's start with some necessary variables*/
                                                $input[$saves][10] = 0;
                                                $uninterest = rand(0, 10); //We'll compare this with interest later

                                                //Check if the target file has less interest than lack of interest
                                                if ($uninterest < $interestReference[$interestTarget][3]){
                                                    /*Here's the part where we check generate to make sure that loading a data set from noIDTransit doesn't lead to wasted data
                                                    Also, it's where we check there are still value from noIDTransit to retrieve
                                                    First, we need to check if $noIDTransit exists*/
                                                    if ($noIDTransit != null){
                                                        //Now we can check the contents of noIDTransit
                                                        if ($generate && $noIDEntries < count($noIDTransit)){
                                                            /*Pull data from the first part and the last four parts of the current no ID entry and assign them to the respective parts of input
                                                              0 in 0, 3 in 9, 4 in 10, 5 in 11, and 6 in 12.
                                                              We'll check these values for null soon*/
                                                            $input[$saves][0] = $noIDTransit[$noIDEntries][0];
                                                            $input[$saves][9] = $noIDTransit[$noIDEntries][3];
                                                            $input[$saves][10] = $noIDTransit[$noIDEntries][4];
                                                            $input[$saves][11] = $noIDTransit[$noIDEntries][5];
                                                            $input[$saves][12] = $noIDTransit[$noIDEntries][6];
                                                            $noIDEntries++;
                                                        }
                                                    }

                                                    //Check input 10 to see if it's still null. Give it a random value if it's not
                                                    if ($input[$saves][10] == null){
                                                        $input[$saves][10] += rand($interestReference[$interestTarget][4], $interestReference[$interestTarget][5]);
                                                    }
                                                }
                                                
                                                //Check if we need to generate an entry for the file
                                                if ($generate && $input[$saves][10] > 0){
                                                    //Generate inputs to generate the file
                                                    $input[$saves][1] = $companyReference[$companyTrack][0]; //0 is the company ID in the company file
                                                    $input[$saves][2] = $companyReference[$companyTrack][1]; //1 is the company name in the company file
                                                    $input[$saves][3] = $monthTrack;
                                                    $input[$saves][4] = $dayTrack;
                                                    $input[$saves][5] = $customerReference[$customerTrack][0]; //0 is the customer ID in the customer file
                                                    $input[$saves][6] = $customerReference[$customerTrack][3]; //3 is the customer area in the customer file
                                                    $input[$saves][7] = $productReference[$productTrack][0]; //0 is the product ID in the product file
                                                    $input[$saves][8] = $productReference[$productTrack][1]; //1 is the product name in the product file

                                                    /*Finding the final four inputs requires calculations from multiple tables, but we've already done ten, so we'll focus on 9, 11, and 12.
                                                      Input 9 asks for the price of a product, and all that takes is searching the price file for a specific company and product combo, but first we should check for null*/
                                                    if ($input[$saves][9] == null){
                                                        //This element is empty, fix it with the fourth value of the company product price reference
                                                        $input[$saves][9] = $priceReference[$priceTarget][3];
                                                    }

                                                    //The last two inputs take the third last input and multiply it by something to get their value, if they're not filled
                                                    if ($input[$saves][11] == null){
                                                        $input[$saves][11] = ($input[$saves][10] * $input[$saves][9]); //The multiplier is the fourth last input (Product Price)
                                                    }

                                                    if ($input[$saves][12] == null){
                                                        //The multiplier is the money the company paid to get the good (ProductReference[2])
                                                        $input[$saves][12] = ($input[$saves][10] * $productReference[$productTrack][2]);
                                                    }
                                                }
                                                else {
                                                    //If we're here, we failed to save an entry. Mark save success as false so we don't trigger the ID generator and incrementor of $saves found below
                                                    $saveSuccess = false;
                                                }
                                            }
                                            
                                            //Check if the system has a full entry
                                            if ($saveSuccess){
                                                //The system needs to check if there was a sales order identifier provided, regardless of how the entry's data was gathered
                                                if ($input[$saves][0] == null){
                                                    //No identifier? Generate IDs with input, transit and notransit as references until they all agree on an identifier
                                                    do {
                                                        //Set the Identifier to test in the array we'll eventually save
                                                        $input[$saves][0] = numericIDGenerator($identifiers, "salesOrder", $input);
                                                        
                                                        //Generate test values with both ID and no ID transits
                                                        $testIDs = numericIDGenerator($identifiers, "salesOrder", $IDTransit);
                                                        $testNoIDs = numericIDGenerator($identifiers, "salesOrder", $noIDTransit);
                                                        
                                                        //Increase the value in identifiers to ensure the value isn't repeated again
                                                        $identifiers++;
                                                    } while ($input[$saves][0] != $testIDs && $input[$saves][0] != $testNoIDs && $testIDs != $testNoIDs);
                                                }
                                                
                                                //Increase the number in saves so we don't overwrite complete inputs
                                                $saves++;
                                            }
                                            else {
                                                //Dispose of the incomplete array
                                                $trash = array_pop($input);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                //After the long process of generation, prepare to save the file by opening it
                $file = fopen(SalesOrderLink, "w");
                
                //Run a foreach loop to save the data in the input array
                foreach ($input as $itself){
                    fputcsv($file, $itself);
                }
                
                //Close the file
                fclose($file);
                
                //Tell the user that the sales order file was generated
                return "Sales order data file generated";
            }
            catch (IOException $error){
                throw $error;
                return "Failed to generate sales order data.";
            }
        }
        else {
            //The files don't exist, tell the user to have these files on the system before generating a sales order
            return "Missing some or all prerequisite files (company data, customer data, product data, company product price data, customer product interest data). Failed to generate sales order data.";
        }
        
    }
    
    //The uploadSalesOrder is a function that I don't expect to get much use, but incase if it does, it allows the user to upload a sales order file to replace the one in the system. If incomplete, the system will complete it.
    function uploadSalesOrder (){
        //A file was provided in the generate command page, use the uploadFile function to give a value to the variable below. If it carries an existing file, we've got work to do
        $input = uploadFile();
        
        if (file_exists($input)){
            /* Declare necessary array variables, both hold a lot of elements, so if you need a reference, refer to the sales order header constant and forget about
               company name, product name, and customer area.
               As for the values in noIDTransit, you're looking for months, days, Product Price, Products Sold, Revenue, and Cost
            */
            $IDTransit = array(array(null, null, null, null, null, null, null, null, null, null));
            $noIDTransit = array(array(null, null, null, null, null, null, null));
            
            //Declare some trackers for the arrays above
            $IDEntries = 0;
            $noIDEntries = 0;
            
            //And some trackers for the highest and lowest value of time
            $highMonth = 1;
            $lowMonth = 60;
            $highDay = 1;
            $lowDay = 365;
            
            //Also, a success message, if we get the chance to use it
            $success = "<br>Uploaded sales order data file successfully. Please note that we did change and/or sort everything, so load the file to see the changes (they tend to be rather drastic if your upload file isn't sorted alphabeticly, from lowest to highest value).<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
            
            //And a boolean for tracking if a sales order ID was provided or not
            $noID = false;
            
            try {
                //Open the uploaded file
                $reader = fopen($input, "r");
                
                //Read the first entry to see what we are working with
                $transit = fgetcsv($reader);
                
                //Check the size of the entry to see how the data is being uploaded
                if (count($transit) == 6 || count($transit) == 7){
                    
                    //Check the size of the entry again to see if a sales order ID was provided or not
                    if (count($transit) == 6){
                        //No sales order ID provided, tell the system that we need to do something special
                        $noID = true;
                    }
                    
                    //Dig through the file
                    while (!feof($reader)){
                        //Assign the next entry to noIDTransit
                        $noIDTransit[$noIDEntries] = fgetcsv($reader);

                        //Check if the latest entry was an empty one
                        if ($noIDTransit[$noIDEntries] == array(null, null, null, null, null, null, null) || $noIDTransit[$noIDEntries] == null){
                            //Remove the empty array
                            $trash = array_pop($noIDTransit);
                        }
                        else{ //Check all values of the entry and make sure it only has six columns
                            
                            //Before we check the next entry, we need to refer to $noID to know if we need to move the values up an element
                            if ($noID){
                                //Sales Order ID doesn't exist, so the values need to move up to compensate for it
                                $noIDTransit[$noIDEntries][6] = floatval($noIDTransit[$noIDEntries][5]); //Initialize the cost of goods sold
                                $noIDTransit[$noIDEntries][5] = floatval($noIDTransit[$noIDEntries][4]); //Initialize the revenue gained from the product
                                $noIDTransit[$noIDEntries][4] = intval($noIDTransit[$noIDEntries][3]); //Initialize the number of products sold
                                $noIDTransit[$noIDEntries][3] = floatval($noIDTransit[$noIDEntries][2]); //Initialize the company's price of the product
                                $noIDTransit[$noIDEntries][2] = intval($noIDTransit[$noIDEntries][1]); //Initialize the day/step
                                $noIDTransit[$noIDEntries][1] = intval($noIDTransit[$noIDEntries][0]); //Initialize the month/round
                                $noIDTransit[$noIDEntries][0] = null; //Empty the ID value.
                            }
                            
                            /* Now the first two values (month and day) are identifiers, 
                               Instead of tracking when they occur, we'll track the highest value provided to:
                                   Determine the number of rounds in the simulation
                                   Determine the number of steps in each round
                               In the 12 column path, we'll collect day if we also have IDs from the first three tables*/
                            if (intval($noIDTransit[$noIDEntries][1]) > $highMonth){
                                $highMonth = intval($noIDTransit[$noIDEntries][1]);
                            }
                            if (intval($noIDTransit[$noIDEntries][1]) < $lowMonth && intval($noIDTransit[$noIDEntries][0]) > 0){
                                $lowMonth = intval($noIDTransit[$noIDEntries][1]);
                                $lowDay = intval($noIDTransit[$noIDEntries][2]);
                            }
                            if (intval($noIDTransit[$noIDEntries][2]) > $highDay){
                                $highDay = intval($noIDTransit[$noIDEntries][2]);
                            }
                            if (intval($noIDTransit[$noIDEntries][2]) < $lowDay && intval($noIDTransit[$noIDEntries][2]) != 0 && $lowMonth == intval($noIDTransit[$noIDEntries][1])){
                                $lowDay = intval($noIDTransit[$noIDEntries][2]);
                            }
                            
                            //Check the other values for empty values. Since sales order ID is a string, we don't have anything to correct it to, so we'll start with company product price
                            if (floatval($noIDTransit[$noIDEntries][3]) == null || floatval($noIDTransit[$noIDEntries][3]) < 0){
                                $noIDTransit[$noIDEntries][3] = 0;
                            }
                            else {
                                $noIDTransit[$noIDEntries][3] = floatval($noIDTransit[$noIDEntries][3]);
                            }
                            
                            //Now check revenue
                            if (floatval($noIDTransit[$noIDEntries][5]) == null || floatval($noIDTransit[$noIDEntries][5]) < 0){
                                $noIDTransit[$noIDEntries][5] = 0;
                            }
                            else {
                                $noIDTransit[$noIDEntries][5] = floatval($noIDTransit[$noIDEntries][5]);
                            }
                            
                            //Now check cost
                            if (floatval($noIDTransit[$noIDEntries][6]) == null || floatval($noIDTransit[$noIDEntries][6]) < 0){
                                $noIDTransit[$noIDEntries][6] = 0;
                            }
                            else {
                                $noIDTransit[$noIDEntries][6] = floatval($noIDTransit[$noIDEntries][6]);
                            }
                            
                            //Products sold is the big one because if it's null, then the entry won't save because no products sold means no revenue or costs
                            if (intval($noIDTransit[$noIDEntries][4]) > 0){
                                //Remove any extra values
                                while (count($noIDTransit[$noIDEntries]) > 7){
                                    $trash = array_pop($noIDTransit[$noIDEntries]);
                                }

                                //Increase entries to mark a complete data entry and prevent it from being overwritten
                                $noIDEntries++;
                            }
                        }
                    }
                }
                elseif (count($transit) == 9 || count($transit) == 10){
                    //If we're uploading a complete file, we'll need to update the message in success
                    $success .= "<br>If you do not see an entry with a set of IDs you provided, those IDs don't exist in our customer and product files.";
                    
                    //Check the size of the entry again to see if a sales order ID was provided or not
                    if (count($transit) == 9){
                        //No sales order ID provided, tell the system that we need to do something special
                        $noID = true;
                    }
                    
                    //Dig through the file
                    while (!feof($reader)){
                        //Assign the next entry to transit
                        $transit = fgetcsv($reader);
                        
                        //Check if the latest entry was an empty one
                        if ($transit != null && $transit != array(null, null, null, null, null, null, null, null, null, null)){
                            
                            //Before we check the next entry, we need to refer to $noID to know if we need to move the values up an element
                            if ($noID){
                                //Sales Order ID doesn't exist, so the values need to move up to compensate for it
                                $transit[9] = floatval($transit[8]); //Initialize the cost of goods sold
                                $transit[8] = floatval($transit[7]); //Initialize the revenue gained from the product
                                $transit[7] = intval($transit[6]); //Initialize the number of products sold
                                $transit[6] = floatval($transit[5]); //Initialize the company's price of the product
                                $transit[5] = $transit[4]; //Initialize the product ID
                                $transit[4] = $transit[3]; //Initialize the customer ID
                                $transit[3] = intval($transit[2]); //Initialize the day/step
                                $transit[2] = intval($transit[1]); //Initialize the month/round
                                $transit[1] = $transit[0]; //Initialize the company ID
                                $transit[0] = null; //Empty the ID value.
                            }
                            
                            /* Now there are three things we need to do. use the values in days and months (2 and 3),
                               Check if there are null values in all non ID values (6-9)
                               and check if we have all IDs (1-5)
                               We'll start with the values in days and months*/
                            if (intval($transit[2]) > $highMonth){
                                $highMonth = intval($transit[2]);
                            }
                            if (intval($transit[2]) < $lowMonth && intval($transit[2]) > 0){
                                $lowMonth = intval($transit[2]);
                                $lowDay = intval($transit[3]);
                            }
                            if (intval($transit[3]) > $highDay){
                                $highDay = intval($transit[3]);
                            }
                            if (intval($transit[3]) < $lowDay && intval($transit[3]) > 0 && $lowMonth == intval($transit[2])){
                                $lowDay = intval($transit[3]);
                            }
                            
                            //Now check the non-ID values for empty values, starting with the company product price
                            if (floatval($transit[6]) == null){
                                $transit[6] = 0;
                            }
                            else {
                                $transit[6] = floatval($transit[6]);
                            }
                            
                            //Now check revenue
                            if (floatval($transit[8]) == null){
                                $transit[8] = 0;
                            }
                            else {
                                $transit[8] = floatval($transit[8]);
                            }
                            
                            //Now check cost
                            if (floatval($transit[9]) == null){
                                $transit[9] = 0;
                            }
                            else {
                                $transit[9] = floatval($transit[9]);
                            }
                            
                            //Products sold is the big one because if it's null, then the entry won't save because no products sold means no revenue or costs
                            if (intval($transit[7]) > 0){
                                //Now to check all IDs
                                if ($transit[1] != null && $transit[2] != null && $transit[3] != null && $transit[4] != null && $transit[5] != null){
                                    //Save transit to IDTransit after ensuring that there are 10 elements
                                    while (count($transit) > 10){
                                        $trash = array_pop($transit);
                                    }

                                    $IDTransit[$IDEntries] = $transit;
                                    $IDEntries++;
                                }
                                else {
                                    //We don't have all five IDs? Save an array of sales order ID, month, day, product price, products sold, revenue, and cost to noIDTransit
                                    $noIDTransit[$noIDEntries] = array($transit[0], $transit[2], $transit[3], $transit[6], $transit[7], $transit[8], $transit[9]);
                                    $noIDEntries++;
                                }
                            }
                        }
                    }
                }
                else{
                    fclose($reader);
                    unlink($input);
                    return "Uploaded file is incompatable with the sales order data file format, failed to upload sales order data file to the system.<br>Try uploading a file with six, seven, nine, or ten columns next time, like the recommendation in the generate command page told you to do";
                }
                
                //Close $reader
                fclose($reader);
                
                //Use the generateSalesOrder function to generate data with the data from the uploaded file
                $result = generateSalesOrder($highMonth, $highDay, $lowMonth, $lowDay, $IDTransit, $noIDTransit);
                
                //Delete the temporary file
                unlink($input);
                    
                //Check the message stored in the result before sending a function finished message
                if ($result == "Sales order data file generated"){
                    return $success;
                }
                else {
                    return "Failed to upload sales order data file to the system.<br>An error occurred while saving the data.";
                }
            }
            catch (IOException $error){
                unlink($input);
                return "Uploaded file could not be read, failed to upload sales order data file to the system";
            }
        }
        else {
            //No proper file, tell the user
            return "File Unrecognized, failed to upload sales order data file to the system";
        }
    }
    
    //The generateCompany function generates companies based on the number of companys it's asked to generate
    function generateCompany ($companyCount){
        //These are necessary arrays, one keeps track of the IDs used, another submits data to the file mentioned below
        $IDStorage = null;
        $dataStorage = CompanyHeader;
        
        try{
            //Open the file
            $file = fopen(CompanyLink, "w");
            
            //Use the initialized value in dataStorage to give the file a header
            fputcsv($file, $dataStorage);
            
            //Create a for loop to go generate the data requested 
            for ($track = 0; $track < $companyCount; $track++){
                //Change the values in dataStorage so we have a genuine ID (pregenerated ID, giberish header)
                $IDStorage[$track] = IDGenerator($IDStorage);
                $dataStorage[0] = $IDStorage[$track];
                $dataStorage[1] = giberish(rand());
                
                //Add the data from dataStorage to the file
                fputcsv($file, $dataStorage);
            }
            
            //Close the file once finished
            fclose($file);
            
            //Update files related to the company file
            updateFiles("company");
            
            //Tell the user that the company file was generated
            return "Company data file generated";
        }
        catch (IOException $error){
            return "Failed to generate company data";
        }
    }
    
    //The uploadCompany function allows the user to upload a company data file to replace the one in the system.  If incomplete, the system will complete it itself
    function uploadCompany (){
        //A file was provided in the generate command page, use the uploadFile function to give a value to the variable below. If it carries an existing file, we've got work to do
        $input = uploadFile();
        
        if (file_exists($input)){
            try{
                //Attempt to upload the file
                $result = uploadCompanyFile($input, CompanyLink);
                
                //Remove the temporary file
                unlink($input);
                
                //Use a switch to determine the end message
                switch ($result){
                    case "Pass":
                        //In addition to the end message, we'll have to update the files relative to the company file
                        updateFiles("company");
                        
                        //Tell the user of our success
                        return "Uploaded Company data file successfully. Please note that we did change some values, so load the file to see the changes.<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
                        break;
                    case "Fail": //Tell the user of the bad file
                        return "Uploaded file is incompatable with the company data file format, failed to upload company data file to the system.<br>Try uploading a file with one or two columns next time, like the recommendation in the generate command page told you to do";
                        break;
                    case "Empty": //Tell the user of the empty file
                        return "No data found in the uploaded file, failed to upload company data file to the system.<br>Make sure you have multiple entries in your file when you decide to upload it";
                        break;
                    default:
                        return "Uploaded file could not be read, failed to upload company data file to the system";
                }
            }
            catch (IOException $error){
                //Delete the uploaded file then tell the user about the failure
                unlink($input);
                return "Uploaded file could not be read, failed to upload company data file to the system";
            }
        }
        else {
            //No proper file, tell the user
            return "File Unrecognized, failed to upload company data file to the system";
        }
    }
    
    //The generateCustomer function generates customers based on the number of customers it's asked to generate and the number of areas they should be in
    function generateCustomer ($areas, $customerCount){
        //These are necessary arrays, one keeps track of the IDs used, another submits data to the file mentioned below
        $IDStorage = null;
        $dataStorage = CustomerHeader;
        
        try{
            //Open the file
            $file = fopen(CustomerLink, "w");
            
            //Use the initialized value in dataStorage to give the file a header
            fputcsv($file, $dataStorage);
            
            //Create two for loops to go generate the data requested, the outer loop runs through areas, the inner loop runs through customers 
            for ($progress = 0; $progress < $areas; $progress++){
                for ($track = 0; $track < $customerCount; $track++){
                    //Since we're working with two loops, we'll need a way to keep track of the total number of customers generated so the IDStorage array doesn't get confused. It would also be a good time to give it a value as well.
                    $totalTrack = $track + $customerCount * $progress;
                    $IDStorage[$totalTrack] = numericIDGenerator($totalTrack + 1, "customer", $IDStorage);
                    
                    //Change the values in dataStorage so we have a genuine entry (pregenerated ID, giberish name, giberish address, preset Area)
                    $dataStorage[0] = $IDStorage[$totalTrack];
                    $dataStorage[1] = giberish(rand());
                    $dataStorage[2] = giberish(rand());
                    $dataStorage[3] = presetAreas($progress);
                
                    //Add the data from dataStorage to the file
                    fputcsv($file, $dataStorage);
                }
            }
            
            //Close the file once finished
            fclose($file);
            
            //Update files related to the customer file
            updateFiles("customer");
            
            //Tell the user that the customer file was generated
            return "Customer data file generated.";
        }
        catch (IOException $error){
            return "Failed to generate customer data";
        }
    }
    
    //The uploadCustomer function allows the user to upload a customer data file to replace the one in the system.  If incomplete, the system will complete it itself
    function uploadCustomer (){
        //A file was provided in the generate command page, use the uploadFile function to give a value to the variable below. If it carries an existing file, we've got work to do
        $input = uploadFile();
        
        if (file_exists($input)){
            try{
                //Attempt to upload the file
                $result = uploadCustomerFile($input, CustomerLink);
                
                //Remove the temporary file
                unlink($input);
                
                //Use a switch to determine the end message
                switch ($result){
                    case "Pass":
                        //In addition to the end message, we'll have to update the files relative to the customer file
                        updateFiles("customer");
                        
                        //Tell the user of our success
                        return "Uploaded Customer data file successfully. Please note that we did change some values, so load the file to see the changes.<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
                        break;
                    case "Fail": //Tell the user of the bad file
                        return "Uploaded file is incompatable with the customer data file format, failed to upload product data file to the system.<br>Try uploading a file with three or four columns next time, like the recommendation in the generate command page told you to do";
                        break;
                    case "Empty": //Tell the user of the empty file
                        return "No data found in the uploaded file, failed to upload customer data file to the system.<br>Make sure you have multiple entries in your file when you decide to upload it";
                        break;
                    default:
                        return "Uploaded file could not be read, failed to upload customer data file to the system";
                }
            }
            catch (IOException $error){
                unlink($input);
                return "Uploaded file could not be read, failed to upload customer data file to the system";
            }
        }
        else {
            //No proper file, tell the user
            return "File Unrecognized, failed to upload customer data file to the system";
        }
    }
    
    //The generateProduct function generates products based on the number of products it's asked to generate
    function generateProduct ($productCount){
        //These are necessary arrays, one keeps track of the IDs used, another submits data to the file mentioned below
        $IDStorage = null;
        $dataStorage = ProductHeader;
        
        try{
            //Open the file
            $file = fopen(ProductLink, "w");
            
            //Use the initialized value in dataStorage to give the file a header
            fputcsv($file, $dataStorage);
            
            //Create a for loop to go generate the data requested 
            for ($track = 0; $track < $productCount; $track++){
                //Change the values in dataStorage so we have a genuine entry (pregenerated ID, giberish name, random decimal cash value)
                $IDStorage[$track] = numericIDGenerator($track + 1, "product", $IDStorage);
                $dataStorage[0] = $IDStorage[$track];
                $dataStorage[1] = giberish(rand());
                $dataStorage[2] = randomDecimal(0, 1999.99);
                
                //Add the data from dataStorage to the file
                fputcsv($file, $dataStorage);
            }
            
            //Close the file once finished
            fclose($file);
            
            //Update files related to the product file
            updateFiles("product");
            
            //Tell the user that the product file was generated
            return "Product data file generated";
        }
        catch (IOException $error){
            return "Failed to generate product data";
        }
    }
    
    //The uploadProduct function allows the user to upload a product data file to replace the one in the system.  If incomplete, the system will complete it itself
    function uploadProduct (){
        //A file was provided in the generate command page, use the uploadFile function to give a value to the variable below. If it carries an existing file, we've got work to do
        $input = uploadFile();
        
        if (file_exists($input)){
            try{
                //Attempt to upload the file
                $result = uploadProductFile($input, ProductLink);
                
                //Remove the temporary file
                unlink($input);
                
                //Use a switch to determine the end message
                switch ($result){
                    case "Pass":
                        //In addition to the end message, we'll have to update the files relative to the product file
                        updateFiles("product");
                        
                        //Tell the user of our success
                        return "Uploaded Product data file successfully. Please note that we did change some values, so load the file to see the changes.<br>Also note that we treat the first entry as a header, so if you're unhappy with how your data can't be edited, please modify your source file to have headers.";
                        break;
                    case "Fail": //Tell the user of the bad file
                        return "Uploaded file is incompatable with the product data file format, failed to upload product data file to the system.<br>Try uploading a file with two or three columns next time, like the recommendation in the generate command page told you to do";
                        break;
                    case "Empty": //Tell the user of the empty file
                        return "No data found in the uploaded file, failed to upload product data file to the system.<br>Make sure you have multiple entries in your file when you decide to upload it";
                        break;
                    default:
                        return "Uploaded file could not be read, failed to upload product data file to the system";
                }
            }
            catch (IOException $error){
                unlink($input);
                return "Uploaded file could not be read, failed to upload product data file to the system";
            }
        }
        else {
            //No proper file, tell the user
            return "File Unrecognized, failed to upload product data file to the system";
        }
    }
    
    //The uploadForeign function allows the user to upload a data file, then the system will check what got an upload request and make a proper replacement.  If the provided file is incomplete, the system will complete it itself.
    function uploadForeign ($action, $productIDs, $otherIDs){
        //A file was provided in the generate command page, use the uploadFile function to give a value to the variable below. If it carries an existing file, we've got work to do
        $input = uploadFile();
        
        if (file_exists($input)){
            try{
                //Check the action of the array we've recieved
                if ($action == "companyPrice"){
                    //Retrieve the necessary values from the uploadCompanyPriceFile function
                    $results = uploadCompanyPriceFile($input, true);

                    //If the first four results are empty, that's the sign of an error. It's message will be in the fifth element
                    if ($results[0] == null && $results[1] == null && $results[2] == null && $results[3] == null){
                        //We have bad results, return the fifth element
                        return $results[4];
                    }
                    else{
                        //We've got good results, now let's generate data with the generate company price data function
                        $result = generateCompanyPriceData(CompanyPriceLink, $results[0], $results[1], $otherIDs, $productIDs);

                        //Delete the temporary file
                        unlink($input);

                        //Return the success message if we have a positive result, otherwise, print the negative result
                        if ($result == "Company Product Price Data File Generated")
                            return $results[4];
                        else{
                            return "Failed to upload data due to a data generation issue.<br>The data generation was to fill in the unprovided gaps in case you were wondering.";
                        }
                    }
                }
                elseif ($action == "customerInterest"){
                    //Retrieve the necessary values from the uploadCustomerInterestFile function
                    $results = uploadCustomerInterestFile($input);
                    
                    //If the first four results are empty, that's the sign of an error. It's message will be in the fifth element
                    if ($results[0] == null && $results[1] == null && $results[2] == null && $results[3] == null){
                        //We have bad results, return the fifth element
                        return $results[4];
                    }
                    else{
                        //We've got good results, now let's generate data with the generate customer interest data function
                        $result = generateCustomerInterestData(CustomerInterestLink, $results[0], $results[1], $otherIDs, $productIDs);

                        //Delete the temporary file
                        unlink($input);

                        //Return the success message if we have a positive result, otherwise, print the negative result
                        if ($result == "Customer Product Interest Data Dile Generated")
                            return $results[4];
                        else{
                            return "Failed to upload data due to a data generation issue.<br>The data generation was to fill in the unprovided gaps in case you were wondering.";
                        }
                    }
                }
                else{ //If the file doesn't meet the format requirements, delete it and tell the user about the failed upload.
                    unlink($input);
                    return "Uploaded file is incompatable with either the company product price data file format or the customer product interest data file format, or the provided file was neither a company product price data file or a customer product interest data file<br>Failed to upload data to the system.<br>Try uploading a file with three, four, five, or six columns next time, like the recommendation in the generate command page told you to do.<br>Or try uploading a company product price or customer product interest file with the menu instead of being a web address using know-it-all.";
                }
            }
            catch (IOException $error){
                //Delete the uploaded file then tell the user about the failure
                unlink($input);
                return "Uploaded file could not be read, failed to upload data to the system";
            }
            
        }
        else {
            //No proper file, check the command and tell the user
            return "File Unrecognized, failed to upload data to the system";            
        }
    }
    ?>
</html>