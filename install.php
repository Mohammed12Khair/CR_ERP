<?php

$dbname = 'qwnukwnp_demo';
$dbuser = 'qwnukwnp_demo';
$dbpass = 'qwnukwnp_demp';
$dbhost = 'localhost';
$connect = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname) or die("Unable to connect to '$dbhost'");

//mysql_select_db($dbname) or die("Could not open the database '$dbname'");
echo "\n\nSTART";
//$result = mysql_query("SELECT id, name FROM employees");
//Clean up
$cleans=["transaction_sell_linesClone","transaction_sell_lines_purchase_linesClone","transaction_sell_lines_clones",
"transactions_clones_edit","transaction_sell_linesClone_edit","transaction_sell_lines_purchase_linesClone_edit"
,"purchase_lines_edit","deletereasone","transactions_clones"];

foreach($cleans as $clean){
    echo "\n";
    echo $clean;
    try{
        mysqli_query($connect,"DROP TABLE " . $clean);
        echo "\nSuccess";
    }catch(Exception $e){
        echo "\nError ". $e;
    }

}


$changes=[
"create table transactions_clones as SELECT * FROM transactions where 1=3",
"create table transaction_sell_linesClone as SELECT * FROM transaction_sell_lines where 1=3",
"create table transaction_sell_lines_purchase_linesClone as select * from transaction_sell_lines_purchase_lines where 4=0",
"create table transaction_sell_linesClone as select * from transaction_sell_lines where 1=10",
"create table transaction_sell_lines_clones as select * from transaction_sell_lines where 1=10",
"create table transaction_sell_lines_purchase_linesClone as select * from transaction_sell_lines_purchase_lines where 1=10",
"ALTER TABLE `bookings` ADD `booking_invoice` INT NULL AFTER `booking_note`",
"create table transactions_clones_edit as select * from transactions_clones where 5=0",
"create table transaction_sell_linesClone_edit as select * from transaction_sell_lines where 1=10",
"create table transaction_sell_lines_purchase_linesClone_edit as select * from transaction_sell_lines_purchase_lines where 1=10",
"create table purchase_lines_edit as select * from purchase_lines where 1=9",
"ALTER TABLE `transaction_payments` ADD `cheque_date` VARCHAR(10) NULL AFTER `updated_at`",
"CREATE TABLE deletereasone ( `id` INT NULL AUTO_INCREMENT ,  `userid` INT(11) NULL ,  `transaction_id` INT(11) NULL ,  `deletereasone` INT(11) NULL ,  `insert_at` TIMESTAMP NOT NULL ,    PRIMARY KEY  (`id`)) ENGINE = InnoDB;",
"ALTER TABLE `deletereasone` ADD `insert_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `deletereasone`",
"ALTER TABLE `deletereasone` CHANGE `deletereasone` `deletereasone` VARCHAR(500) NULL DEFAULT NULL; ",
"ALTER TABLE `transaction_sell_lines_clones` ADD `mfg_waste_percent` INT(11) NULL AFTER `quantity`",
"ALTER TABLE `business` ADD `pur_invoice_man` INT(1) NULL DEFAULT '0' AFTER `is_active`, ADD `sel_invoice_man` INT(1) NULL DEFAULT '0' AFTER
`pur_invoice_man`"
];
foreach($changes as $change){
    echo "\n";
    echo $change;
    try{
        mysqli_query($connect,$change);
        echo "\nSuccess";
    }catch(Exception $e){
        echo "\nError ". $e;
    }
}
