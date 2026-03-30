-- ==========================================
-- DATABASE: estate_manager
-- AUTHOR: brian-web68
-- DESCRIPTION: Full Relational Schema
-- ==========================================

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `house_number` VARCHAR(10) NOT NULL,
    `role` ENUM('admin', 'member') DEFAULT 'member',
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(20) DEFAULT 'Unpaid',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. CONTRIBUTIONS TABLE
CREATE TABLE IF NOT EXISTS `contributions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11),
    `title` VARCHAR(100),
    `description` TEXT,
    `target_amount` DECIMAL(15,2),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. PAYMENTS TABLE (Updated with your actual data structure)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `contribution_id` INT(11) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `recorded_by` INT(11) DEFAULT NULL,
    `payment_type` VARCHAR(20) DEFAULT 'mpesa',
    `rent_month` VARCHAR(30),
    `checkout_id` VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_contribution` FOREIGN KEY (`contribution_id`) REFERENCES `contributions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. RENTS TABLE
CREATE TABLE IF NOT EXISTS `rents` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11),
    `amount_due` DECIMAL(10,2),
    `billing_date` DATE,
    `status` ENUM('Paid', 'Pending', 'Overdue') DEFAULT 'Pending',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- 5. EXPENSES & PROBLEMS
CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `description` VARCHAR(255),
    `amount` DECIMAL(10,2),
    `expense_date` DATE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `problems` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11),
    `report` TEXT,
    `status` ENUM('New', 'In Progress', 'Resolved') DEFAULT 'New',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- ==========================================
-- SEED DATA (Your actual records)
-- ==========================================

INSERT INTO `users` (`id`, `full_name`, `phone`, `house_number`, `role`, `password`, `status`) VALUES
(3, 'Test Landlord', '0706493316', 'A1', 'admin', '123', 'Paid'),
(4, 'Laureen monubi', '0757684599', '001', 'member', '$2y$10$.i2Q2JG6Ez.SUBsySlci4.qp7CclDWPf3aupzYoMzX4nmm0m3ToYS', 'Paid');

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_date`, `payment_type`, `rent_month`) VALUES
(1, 3, 100.00, '2026-03-29', 'mpesa', 'March 2026'),
(2, 4, 6000.00, '2026-03-29', 'mpesa', 'March 2026');

-- Performance Indexes
CREATE INDEX idx_rent_lookup ON payments(rent_month);
CREATE INDEX idx_mpesa_check ON payments(payment_type);
