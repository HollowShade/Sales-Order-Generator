<!doctype html>
<html lang="en">

    <head>
        <!-- Necessary Descriptive Stuff -->
        <title>Data File Generation</title>
        <meta name="author" value="Dakota Gray">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Stylesheet Reference -->
        <link rel="stylesheet" href="Styles/classStyles.css">
    </head>
    
    <body>
        <?php require 'Scripts/functions.php';
            //Get some variables out of the way
            $source = null;
        
            //Retrieve the value of source/generate
            if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["generate"])){
                $source = inputSecurity($_POST["generate"]);
            }
        ?>
        
        <!-- Introduce the User to the Page -->
        <h2>Data File Generator</h2>
        <p>Give us a file and we will upload it to our system</p>
        
        <!-- Form one: Taking a file as a data generation input -->
        <form method="post" action=index.php enctype="multipart/form-data">
            <?php
                //The label for upload depends on the value of source
                switch ($source){
                    case "salesOrder":
                        //Create a label for upload based on a sales order data file
                        echo "<label for=upload><p>I highly recommend submitting a spreadsheet with six columns (representing round/month, step/day, product price, products sold, revenue, and cost).<br>You can also submit a spreadsheet with nine columns (Company ID, round, step, Customer ID, Product ID, Product Price, Products Sold, Revenue, Cost), importing a sales order data file into the system, although I would advise against this as a file replacment might not consider the system's files.<br>Either way, you can add a Sales Order ID by adding it to either data set as the first item, making seven and ten an acceptable total number of columns<br>Don't worry, we'll have you covered if you don't provide any or all IDs.</p></label>";
                        break;
                    case "company":
                        //Create a label for upload based on a company data file
                        echo "<label for=upload><p>I recommend submitting a spreadsheet with one column (representing the company names).<br>You can also submit a spreadsheet with two columns (ID, Name), importing a company data file into the system.</p></label>";
                        break;
                    case "customer":
                        //Create a label for upload based on a customer data file
                        echo "<label for=upload><p>I recommend submitting a spreadsheet with three columns (representing the customers' names, adresses, and areas).<br>You can also submit a spreadsheet with four columns (ID, Name, Address, Area), importing a customer data file into the system.</p></label>";
                        break;
                    case "product":
                        //Create a label for upload based on a product data file
                        echo "<label for=upload><p>I recommend submitting a spreadsheet with two columns (representing product names and their prices).<br>You can also submit a spreadsheet with three columns (ID, Name, Cost), importing a product data file into the system.</p></label>";
                        break;
                    case "companyPrice":
                        //Create a label for upload based on a company product price data file
                        echo "<label for=upload><p>I highly recommend submitting a spreadsheet with three columns (representing the price a company has on one of its products, how much of that stock they have at the moment, and whether they sell it or not).<br>You can also submit a spreadsheet with five columns (companyID, productID, cost, stock, isSelling), importing a company product price data file into the system, although I would advise against this as a file replacement might not consider the companies and products we have on file.<br>Either way, you can add a Company Product Price ID by adding it to either data set as the first item, making four and six an acceptable total number of columns<br>Don't worry, we'll have you covered if you don't provide any or all IDs.</p></label>";
                        break;
                    case "customerInterest":
                        //Create a label for upload based on a customer product interest data file
                        echo "<label for=upload><p>I highly recommend submitting a spreadsheet with three columns (representing the customer's level of interest in a product, the minimum amount of that product they will buy, and the maximum amount of that product they will buy, if they buy).<br>You can also submit a spreadsheet with five columns (customerID, productID, interest level, minimum number of stock purchases, maximum number of stock purchases), importing a customer product interest data file into the system, although I would advise against this as a file replacement might not consider the customers and products we have on file.<br>Either way, you can add a Customer Product Interest ID by adding it to either data set as the first item, making four and six an acceptable total number of columns<br>Don't worry, we'll have you covered if you don't provide any or all IDs.</p></label>";
                        break;
                    default:
                        //This is what happens when a value is null. No value, no label.
                }
            ?>
            <input type="file" name="upload" accept=".csv" required>
            <br>
            <button type="submit" name="dataFile" value=<?php echo $source; ?>>Upload File to the System</button>
            <!-- Hidden value containing an action to tell the system that we need to generate data -->
            <input type="text" name="action" value="generate" hidden>
        </form><br>
        
        <p>Alternatively...</p>
        <?php 
        /* The contents of form two and whether it exists or not depends on the contents of source.
            If null, we immediately call form three, the links form.
        */
        if ($source == null){
            echo "<p>Wait, how did you get here?! This page doesn't function properly unless if you come from the main menu!<br>";
            echo "Well great, this page serves you no purpose as it is right now. Your best bet to restore its functionality is to go back to the main menu, then click a button that involves generating data.<br>";
            echo "<b>And whatever you do, DO NOT SUBMIT A FILE WITH THIS PAGE!</b></p>";
            echo "<form method=get action=index.php>";
                echo "<button type=submit name=return>Okay...</button>";
            echo "</form>";
        } //If we have a value in source, check what it is and create a form accordingly
        else{
            echo "<form method=post action=index.php>";
                /*Check soruce to see what data we need from the user to generate the data, and have the form ask for it.
                  I was going to use a switch, but some forms carry similar characteristics. For starters, companyPrice and customerInterest don't require an input, so all you need to do is press a button
                */
                if ($source == "companyPrice" || $source == "customerInterest"){
                    echo "You can click the button below and we'll generate the data for you, assuming you've created all the prerequisite files.<br>";
                }
                else{
                    echo "<p>You can fill out the form below to have the system generate the data file for you, although I'd suggest uploading a file, unless if you're generating sales data.</p>";
                    //Then handle the remaining four values (salesOrder, company, customer, and product) with a switch statement
                    switch ($source){
                        case "salesOrder": //While company, customer, and product ask for a number of entries, salesOrder asks for days and months twice, so it was bound to get a unique form.
                            //Warn the user about how long data generation can take in a sales order.
                            echo "<p>Whether you upload a file to generate your data or use the input form below, please remain advised that a Sales Order file can take a long time to generate when there are a lot of data entries in its reference company, customer, and product files (which are the system's local files).<br>With that said, be prepared for a long wait time when generating Sales Order Data while you have a large amount of reference data.</p>";
                            echo "<label for=primaryCount>Rounds to simulate in ERPSim: </label>";
                            echo "<input type=number name=primaryCount min=1 required><br>";
                            echo "<label for=secondaryCount>Steps in each Round: </label>";
                            echo "<input type=number name=secondaryCount min=1 required><br>";
                            echo "<label for=charliesCount>Round where sales begin: </label>";
                            echo "<input type=number name=charliesCount min=1 required><br>";
                            echo "<label for=deltasCount>Step where sales begin in the first sales round: </label>";
                            echo "<input type=number name=deltasCount min=1 required><br>";
                            break;
                        case "customer": //The one thing setting customer apart from company and product is that it also asksfor the number of areas to incorporate.  Since location can be its own table, it might not stay that way for long.
                            echo "<label for=primaryCount>Areas to generate: </label>";
                            echo "<input type=number name=primaryCount min=1 required><br>";
                            echo "<label for=secondaryCount>Customers to generate in each area: </label>";
                            echo "<input type=number name=secondaryCount min=1 required><br>";
                            break;
                        default: //Since company and product generate in the same vain, they're grouped together
                            echo "<label for=primaryCount>Entries to Generate: </label>";
                            echo "<input type=number name=primaryCount min=1 required><br>";
                    }
                }
                //The end of the file always has this submit button, a hidden value with upload set to "null", and a hidden value with action set to generate.
                echo "<button type=submit name=dataFile value=" . $source . ">Generate Data</button>";
                echo "<input type=text name=upload value=null hidden>";
                echo "<input type=text name=action value=generate hidden>";
            echo "</form>";
            
            //After the second form, introduce the final form
            echo "<br><br>If you stumbled into this page on accident, you can click the link below and return to the main menu.<br>";
            
            //The final form
            echo "<form method=get action=index.php>";
                echo "<button type=submit name=return>Okay, take me back to the Main Menu</button>";
            echo "</form>";
        }
        ?>
    </body>
    
</html>