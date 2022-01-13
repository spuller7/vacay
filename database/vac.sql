SELECT * FROM vacay.adventures;

CREATE TABLE adventures (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    google_place_id VARCHAR(250) NOT NULL,
    here_place_id VARCHAR(250),
    price_level tinyint(4) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
	modified_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

CREATE TABLE recommendations (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(250),
    adventure_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
);

CREATE TABLE results (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(32),
    adventure_id INT NOT NULL,
    create_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
);

CREATE TABLE adventure_here_category_map (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    adventure_id INT NOT NULL,
    here_category_id INT NOT NULL
);

CREATE TABLE here_categories (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(250)
);

CREATE TABLE categories (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250),
    active TINYINT(1) DEFAULT 1;
);

CREATE TABLE here_category_category_map (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    here_category_id INT NOT NULL,
    category_id INT NOT NULL
);

CREATE TABLE addresses (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    adventure_id INT NOT NULL,
    address VARCHAR(100),
    city VARCHAR(100),
    zipcode CHAR(10),
    state CHAR(2),
    country VARCHAR(100),
    latitude DECIMAL,
    longitude DECIMAL
);

SELECT * FROM adventures
INNER JOIN adventure_here_category_map AS ahcm ON ahcm.adventure_id = adventures.id
INNER JOIN here_categories ON here_categories.id = ahcm.here_category_id
WHERE
	here_categories.id IN (SELECT here_categories.id FROM here_categories
    INNER JOIN here_category_category_map AS hccm ON hccm.here_category_id = here_categories.id
    INNER JOIN categories ON hccm.category_id = categories.id
    WHERE categories.id IN (2))
    AND price_level IN (2,1);



