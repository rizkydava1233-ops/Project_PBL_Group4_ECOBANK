CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  pass_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('admin','nasabah','petugas')),
  status TEXT NOT NULL DEFAULT 'aktif' CHECK (status IN ('aktif','menunggu')),
  addr TEXT,
  bonus INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS rates (type TEXT PRIMARY KEY, points INT NOT NULL CHECK (points > 0));
CREATE TABLE IF NOT EXISTS rewards (
  id SERIAL PRIMARY KEY, ic TEXT NOT NULL DEFAULT 'gift', name TEXT NOT NULL,
  cost INT NOT NULL CHECK (cost > 0), stock0 INT NOT NULL CHECK (stock0 >= 0)
);
CREATE TABLE IF NOT EXISTS pickups (
  id SERIAL PRIMARY KEY,
  uid INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  pid INT REFERENCES users(id) ON DELETE SET NULL,
  items JSONB NOT NULL, done JSONB,
  addr TEXT NOT NULL, when_date DATE NOT NULL, slot TEXT NOT NULL,
  photo TEXT, proof TEXT,
  status TEXT NOT NULL DEFAULT 'menunggu' CHECK (status IN ('menunggu','diterima','berangkat','selesai','dibatalkan')),
  pts INT NOT NULL DEFAULT 0, actual NUMERIC(10,2), done_at DATE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS pickups_uid ON pickups(uid);
CREATE INDEX IF NOT EXISTS pickups_status ON pickups(status);
CREATE TABLE IF NOT EXISTS redeems (
  id SERIAL PRIMARY KEY,
  uid INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  rid INT REFERENCES rewards(id) ON DELETE SET NULL,
  name TEXT NOT NULL, ic TEXT NOT NULL, cost INT NOT NULL,
  status TEXT NOT NULL DEFAULT 'diproses' CHECK (status IN ('diproses','selesai')),
  code TEXT NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS posts (
  id SERIAL PRIMARY KEY, title TEXT NOT NULL, body TEXT NOT NULL,
  kind TEXT NOT NULL DEFAULT 'edu' CHECK (kind IN ('edu','info')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
