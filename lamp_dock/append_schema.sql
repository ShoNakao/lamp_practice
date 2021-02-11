CREATE TABLE orders (
    order_id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    order_datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    primary key(order_id),
    INDEX(user_id)
);

CREATE TABLE order_details (
    order_id int(11) NOT NULL,
    item_id int(11) NOT NULL,
    order_price int(11) NOT NULL,
    order_amount int(11) NOT NULL,
    INDEX(item_id)
)