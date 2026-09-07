-- Yur Shack orders — lightweight PostgreSQL schema
-- Run once as a privileged DB user, then grant to the app role.

CREATE TABLE IF NOT EXISTS orders (
    id              BIGSERIAL PRIMARY KEY,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    status          TEXT NOT NULL DEFAULT 'new',
    customer_name   TEXT NOT NULL,
    customer_email  TEXT NOT NULL,
    customer_phone  TEXT,
    business_name   TEXT,
    website_domain  TEXT,
    domain_arrangement TEXT NOT NULL,
    initial_term    TEXT,
    payment_method  TEXT,
    special_scope   TEXT,
    services        JSONB NOT NULL DEFAULT '[]'::jsonb,
    estimated_setup_gbp   NUMERIC(10,2),
    estimated_monthly_gbp NUMERIC(10,2),
    estimated_yearly_gbp  NUMERIC(10,2),
    notes           TEXT,
    ip_address      TEXT,
    user_agent      TEXT
);

CREATE INDEX IF NOT EXISTS orders_created_at_idx ON orders (created_at DESC);
CREATE INDEX IF NOT EXISTS orders_status_idx ON orders (status);
CREATE INDEX IF NOT EXISTS orders_customer_email_idx ON orders (lower(customer_email));
CREATE INDEX IF NOT EXISTS orders_services_gin ON orders USING GIN (services);

COMMENT ON TABLE orders IS 'Website service order requests from yurshack.com';
COMMENT ON COLUMN orders.services IS 'JSON array of selected service keys and offered/standard prices';

-- After creating your app role, grant access, e.g.:
--   GRANT CONNECT ON DATABASE yurshack TO yurshack;
--   GRANT USAGE ON SCHEMA public TO yurshack;
--   GRANT SELECT, INSERT, UPDATE ON orders TO yurshack;
--   GRANT USAGE, SELECT ON SEQUENCE orders_id_seq TO yurshack;
