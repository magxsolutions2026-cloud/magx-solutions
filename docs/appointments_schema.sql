-- MAGX appointment booking schema (Supabase/PostgreSQL)
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS appointments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    full_name text NOT NULL,
    email text NOT NULL,
    phone text,
    preferred_date date NOT NULL,
    preferred_time time NOT NULL,
    service_type text,
    notes text,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    zoom_link text,
    google_event_id text,
    outlook_event_id text,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_appointments_status_date_time
    ON appointments (status, preferred_date, preferred_time);

-- Enforces no overlapping pending/approved appointments for a single slot.
CREATE UNIQUE INDEX IF NOT EXISTS uq_appointments_active_slot
    ON appointments (preferred_date, preferred_time)
    WHERE status IN ('pending', 'approved');
