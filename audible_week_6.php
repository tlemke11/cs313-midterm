<?php
require_once('dbconnection.php'); //include the db

//quieries
$main_cat_sql = 'SELECT main_category_id, name FROM main_categories ORDER BY name';
$sub_cat_sql = "SELECT sub_category_id,name,main_category_id FROM sub_categories WHERE main_category_id=:mainID ORDER BY name";
$mainID = '';

?>

<html>
  <head>
    <title>Audible Daily Deal Selector</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div id="container">
      <div id="centered">
        <h1>Audible Daily Deal Selector</h1>
        
        
        <?php
        if(isset($_POST['submit'])){
          print "<p>Thank you for Signing Up!</p>";
          
          $categorySubscriptions = $_POST['categories'];
          //$subCateg = $_POST['categories'][1];
          $email = $_POST['email'];
          print_r($categorySubscriptions);
         // print_r($subCateg);
          

          
          //insert the user first
          $sqlUserInsert = "INSERT INTO users (email) VALUES(:email)";
          $userQry = $connection->prepare($sqlUserInsert);
          $userQry->bindParam(':email', $email, PDO::PARAM_STR, 30);
          $userQry->execute();
          $userID = $connection->lastInsertId();
          
          $sqlCatInsert = "INSERT INTO email_subscription(user_id, category_id, category_type) VALUES(:user_id,:category_id,:category_type)";
          
          foreach ($categorySubscriptions as $ids){
            $extractedCat = explode('-',$ids);
            $mainCatQry = $connection->prepare($sqlCatInsert);
            $mainCatQry->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $mainCatQry->bindParam(':category_id', $extractedCat[0], PDO::PARAM_INT);
            $mainCatQry->bindParam(':category_type', $extractedCat[1], PDO::PARAM_INT);
            $mainCatQry->execute();  
          }
          

        } else {
          $self = $_SERVER['PHP_SELF'];
          print "<form action='$self' method='POST'>";

            //This isn't super-clean - hopefully I can make it cleaner later
            //http://php.net/manual/en/pdo.query.php
            //$i = 0;
            //print "<div class='wrapper'>";
            foreach ($connection->query($main_cat_sql) as $row){
              //if ($i == 25){
              //  print "</div>";
              //  print "<div class='wrapper'>";
              //  $i = 0;
              //}
              //$i++;
              $mainID = $row['main_category_id'];
              $mainName = $row['name'];
         
              print "<input type='checkbox' name='categories[]' value='$mainID-0'><strong>$mainName</strong><br>";
              
              //http://stackoverflow.com/questions/15385965/php-pdo-with-foreach-and-fetch
              //See your common sense's answer
              $subArray = $connection->prepare($sub_cat_sql);
              $subArray->bindParam(':mainID', $mainID, PDO::PARAM_INT);
              $subArray->execute();
              $subCats = $subArray->fetchAll();
                
              foreach ($subCats as $rows){
                //$i++;
                $subID = $rows['sub_category_id'];
                $subName = $rows['name'];
                print "<input type='checkbox' name='categories[]' value='$subID-1'>$subName<br>";
              }
              //print "</div>";
              print "<br>";
            }
            
            print "<br>";
            print "Email:<input type='email' name='email'>";
            print "<br>";
            print "<input type='submit' name='submit' value='Submit'>";
            print "</form>";
        }    
          ?>
      </div>
    </div>
  <div id="footer">
    Copyright Tyler Lemke 2016
  <div>
  </body>
</html>