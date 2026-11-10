-- MAGX project inquiries from the "Let's Work Together" form (Supabase/PostgreSQL)
CREATE TABLE IF NOT EXISTS inquiries (
    id bigserial PRIMARY KEY,
    full_name text NOT NULL,
    email text NOT NULL,
    company text,
    budget text,
    message text NOT NULL,
    status text NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'replied', 'archived')),
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_inquiries_status_created
    ON inquiries (status, created_at DESC);
