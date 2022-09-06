
drop table transaction_sell_linesClone purge;




create table transaction_sell_lines_clones as select * from transaction_sell_lines where 1=10

create table transaction_sell_lines_purchase_linesClone as select * from transaction_sell_lines_purchase_lines where 1=10

ALTER TABLE `bookings` ADD `booking_invoice` INT NULL AFTER `booking_note`; 

ALTER TABLE contacts ADD UNIQUE (mobile);





