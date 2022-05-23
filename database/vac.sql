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
    name VARCHAR(250),
    active TINYINT(1) DEFAULT 1
);

CREATE TABLE categories (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250),
    active TINYINT(1) DEFAULT 1
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

INSERT INTO categories (name)
VALUES ('Breakfast'), ('Lunch'), ('Dinner'), ('Sweets'), ('Nightlife')

SELECT * FROM adventures
INNER JOIN adventure_here_category_map AS ahcm ON ahcm.adventure_id = adventures.id
INNER JOIN here_categories ON here_categories.id = ahcm.here_category_id
WHERE
	here_categories.id IN (SELECT here_categories.id FROM here_categories
    WHERE categories.id IN (2))
    AND price_level IN (2,1);

SELECT adventures.id, (IFNULL(month_recommendations.total, 0) + IFNULL(two_month_recommendations.total, 0) + IFNULL(three_month_recommendations.total, 0)) AS weight FROM adventures
LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) AS total FROM recommendations WHERE created_at >= DATE(NOW() - INTERVAL 1 MONTH) GROUP BY adventure_id) AS month_recommendations ON adventures.id = month_recommendations.adventure_id
LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) / 2 AS total FROM recommendations WHERE created_at < DATE(NOW() - INTERVAL 1 MONTH) AND created_at >= DATE(NOW() - INTERVAL 2 MONTH) GROUP BY adventure_id) AS two_month_recommendations ON adventures.id = two_month_recommendations.adventure_id
LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) / 3 AS total FROM recommendations WHERE created_at < DATE(NOW() - INTERVAL 2 MONTH) AND created_at >= DATE(NOW() - INTERVAL 3 MONTH) GROUP BY adventure_id) AS three_month_recommendations ON adventures.id = three_month_recommendations.adventure_id
WHERE (IFNULL(month_recommendations.total, 0) + IFNULL(two_month_recommendations.total, 0) + IFNULL(three_month_recommendations.total, 0))	 > 0 ORDER BY -LOG(1-RAND())/weight;



