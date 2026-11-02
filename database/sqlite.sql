CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    external_id TEXT UNIQUE,
    title TEXT NOT NULL CHECK (length(title) BETWEEN 3 AND 120),
    requester TEXT NOT NULL CHECK (length(requester) BETWEEN 2 AND 80),
    category TEXT NOT NULL CHECK (category IN ('hardware', 'software', 'access', 'other')),
    priority TEXT NOT NULL CHECK (priority IN ('low', 'normal', 'high')),
    status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'in_progress', 'done')),
    description TEXT NOT NULL DEFAULT '' CHECK (length(description) <= 1500),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets(status);
CREATE INDEX IF NOT EXISTS idx_tickets_created_at ON tickets(created_at DESC);

