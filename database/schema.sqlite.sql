PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'Administradora',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_clients_name ON clients (name);
CREATE INDEX IF NOT EXISTS idx_clients_phone ON clients (phone);

CREATE TABLE IF NOT EXISTS pets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients (id) ON DELETE RESTRICT,
    name TEXT NOT NULL,
    breed TEXT NOT NULL,
    sex TEXT NOT NULL DEFAULT 'Não informado'
);
CREATE INDEX IF NOT EXISTS idx_pets_client ON pets (client_id);

CREATE TABLE IF NOT EXISTS services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price NUMERIC NOT NULL,
    cost NUMERIC NOT NULL DEFAULT 0,
    duration INTEGER NOT NULL,
    active INTEGER NOT NULL DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_services_active ON services (active);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    price NUMERIC NOT NULL,
    cost NUMERIC NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_products_name ON products (name);
CREATE INDEX IF NOT EXISTS idx_products_category ON products (category);

CREATE TABLE IF NOT EXISTS appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients (id) ON DELETE RESTRICT,
    pet_id INTEGER NOT NULL REFERENCES pets (id) ON DELETE RESTRICT,
    service_id INTEGER NOT NULL REFERENCES services (id) ON DELETE RESTRICT,
    professional TEXT NOT NULL,
    appointment_date TEXT NOT NULL,
    appointment_time TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Agendado',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_appointments_schedule ON appointments (appointment_date, appointment_time, status);
CREATE INDEX IF NOT EXISTS idx_appointments_client ON appointments (client_id);
CREATE INDEX IF NOT EXISTS idx_appointments_service ON appointments (service_id);

CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_date TEXT NOT NULL,
    description TEXT NOT NULL,
    type TEXT NOT NULL,
    value NUMERIC NOT NULL,
    is_manual INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_transactions_date_type ON transactions (transaction_date, type);

CREATE TABLE IF NOT EXISTS sales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sale_date TEXT NOT NULL,
    product_id INTEGER NOT NULL REFERENCES products (id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    client TEXT NOT NULL DEFAULT 'Venda balcão',
    unit_price NUMERIC NOT NULL,
    total NUMERIC NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_sales_date ON sales (sale_date);
CREATE INDEX IF NOT EXISTS idx_sales_product ON sales (product_id);
