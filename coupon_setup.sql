-- Coupon table
CREATE TABLE  coupon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed',
    discount_value DECIMAL(10, 2) NOT NULL,
    max_uses INT NOT NULL DEFAULT 5,
    times_used INT DEFAULT 0,
    expiration_date DATETIME,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Coupon usage tracking table
CREATE TABLE  coupon_uses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupon(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES purchase_order(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_coupon (coupon_id, user_id)
);

-- Add coupon_id to purchase_order table
ALTER TABLE purchase_order 
ADD COLUMN IF NOT EXISTS coupon_id INT,
ADD COLUMN IF NOT EXISTS coupon_discount DECIMAL(10, 2) DEFAULT 0,
ADD FOREIGN KEY (coupon_id) REFERENCES coupon(id) ON DELETE SET NULL;
