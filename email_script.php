<?php
require_once('dbconnection.php'); //include the db
/**
 * Created by PhpStorm.
 * User: Tyler
 * Date: 11/5/2016
 * Time: 8:01 PM
 */


//FIRST - WE WLL GET THE CATEGORY FROM THE PAGE

//explained on Stack Overflow
//http://stackoverflow.com/questions/15617512/get-json-object-from-url
ini_set("allow_url_fopen", 1); //allow using url as a local file object
$jsonObject = file_get_contents('https://extraction.import.io/query/extractor/b5738496-2c96-4850-8668-806e4616b86a?_apikey=a363bd2c63eb454893ac2c85bfe2d73ec18988f01be8104e20eb5bd5065a4cbde4a5d073afde054b2378ef33a25dfc7650bd11dca41fbdd08c4eaae6a1a66604080704114790a6a2c95cffd71fc54d3c&url=http%3A%2F%2Fwww.audible.com%2FDailyDeal'); //get import.io stuff
$jsonConvertedObject = json_decode($jsonObject, 2);//convert into array
$dailyCategory = $jsonConvertedObject['extractorData']['data'][0]['group'][0]['categories'][0]['text'];

//echo $dailyCategory;

$dailyCategory = explode(" > ",$dailyCategory);

//print_r($dailyCategory);
$mainCategoryText = htmlspecialchars(trim($dailyCategory[1]));
$subCategoryText = htmlspecialchars(trim($dailyCategory[2]));

//Get main Category ID
$mainCatInsert =
        "SELECT main_category_id FROM
          main_categories
         WHERE name =:mainCategoryText";

$mainCatQry = $connection->prepare($mainCatInsert);
$mainCatQry->bindParam('mainCategoryText', $mainCategoryText, PDO::PARAM_STR);
$mainCatQry->execute();
$temp = $mainCatQry->fetchObject();
$mainCategory = $temp->main_category_id;

//Get sub Category ID
$subCatInsert =
    "SELECT sub_category_id FROM
          sub_categories
         WHERE name =:subCategoryText";

$subCatQry = $connection->prepare($subCatInsert);
$subCatQry ->bindParam('subCategoryText', $subCategoryText, PDO::PARAM_STR);
$subCatQry ->execute();
$temp = $subCatQry->fetchObject();
var_dump($temp);
$subCategory = $temp->sub_category_id;

echo "subcat:$subCategory mainCat:$mainCategory";

//NOW WITH PAGE CATEGORY - WE WILL GET THE EMAILS ASSOCIATED WITH THE TWO CATEGORIES
$sqlUserInsert =
    "SELECT DISTINCT email FROM users uu
      JOIN
	    email_subscription ee
      ON
	  uu.user_id=ee.user_id
      WHERE
	  (ee.category_id =:mainCategory AND ee.category_type = 0)
	  OR
	  (ee.category_id =:subCategory AND ee.category_type = 1)
	  ";

$userQry = $connection->prepare($sqlUserInsert);
$userQry->bindParam(':mainCategory', $mainCategory, PDO::PARAM_INT);
$userQry->bindParam(':subCategory', $subCategory, PDO::PARAM_INT);
$userQry->execute();
$emails = $userQry->fetchAll();

//var_dump($emails);

//array_merge($emails, $userQry->fetchll();) //dont need this but it was a cool function to learn

//iterate through each email
foreach($emails as $email){
    //send the email out
    $message = "A book on your subscription list is for sale today. Please visit http://audible.com/dailydeal. ";
    $subject = "Audible Daily Deal For You";
    mail($email[0],$subject,$message);
}


?>

