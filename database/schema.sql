CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(60) NOT NULL DEFAULT 'Administradora',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(160) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clients_name (name),
    INDEX idx_clients_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    sex VARCHAR(30) NOT NULL DEFAULT 'Não informado',
    CONSTRAINT fk_pets_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT,
    INDEX idx_pets_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration INT UNSIGNED NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_services_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(80) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    INDEX idx_products_name (name),
    INDEX idx_products_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    pet_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    professional VARCHAR(120) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Agendado',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_pet FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE RESTRICT,
    INDEX idx_appointments_schedule (appointment_date, appointment_time, status),
    INDEX idx_appointments_client (client_id),
    INDEX idx_appointments_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    transaction_date DATE NOT NULL,
    description VARCHAR(180) NOT NULL,
    type VARCHAR(30) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    is_manual TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_date_type (transaction_date, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    client VARCHAR(120) NOT NULL DEFAULT 'Venda balcão',
    unit_price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sales_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
    INDEX idx_sales_date (sale_date),
    INDEX idx_sales_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
