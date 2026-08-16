import mysql from 'mysql2/promise';

// Direct DB access for test fixtures and cleanup.
//
// docker-compose.yml publishes MariaDB on 8306 both locally and in CI, so tests
// can tidy up after themselves without exposing a reset endpoint on the site.
// Every value is overridable, mirroring how playwright.config.ts handles BASE_URL.
const config = {
  host: process.env.DB_HOST || '127.0.0.1',
  port: Number(process.env.DB_PORT || 8306),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || 'root',
  database: process.env.DB_NAME || 'mkpc',
  multipleStatements: false,
};

// A fresh connection per call: cleanup runs a handful of times per file, and a
// pool would keep the Playwright process alive after the last test.
export async function sql(query: string, params: any[] = []): Promise<any> {
  const conn = await mysql.createConnection(config);
  try {
    // query() rather than execute(): prepared statements cannot expand an array
    // into an IN (?) list, which the scope-based cleanups rely on.
    const [rows] = await conn.query(query, params);
    return rows;
  } finally {
    await conn.end();
  }
}
