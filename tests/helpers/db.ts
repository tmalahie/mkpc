import mysql from 'mysql2/promise';

// Direct DB access for test fixtures and cleanup. docker-compose.yml publishes
// MariaDB on 8306 both locally and in CI, so tests can tidy up after themselves
// without exposing a reset endpoint on the site.
//
// MKPC_-prefixed rather than the bare DB_HOST / DB_USER names, because this module
// only ever issues DELETEs and those generic names are already in use for other
// databases (see the migration rule in CLAUDE.md).
//
// Sibling worktrees share one MariaDB container while each gets its own web port,
// and owner tags are per spec file rather than per checkout - so running the suite
// in two checkouts at once makes them clean up each other's fixtures.
const config = {
  host: process.env.MKPC_DB_HOST || '127.0.0.1',
  port: Number(process.env.MKPC_DB_PORT || 8306),
  user: process.env.MKPC_DB_USER || 'mkpc_user',
  password: process.env.MKPC_DB_PASSWORD || 'mkpc_pwd',
  database: process.env.MKPC_DB_NAME || 'mkpc',
  multipleStatements: false,
};

// A fresh connection per call: cleanup runs a handful of times per file, and a
// pool would keep the Playwright process alive after the last test.
export async function sql(query: string, params: any[] = []): Promise<any> {
  const conn = await mysql.createConnection(config);
  try {
    // query() rather than execute(): prepared statements cannot expand an array
    // into an IN (?) list, which the cleanups use to delete a batch of ids.
    const [rows] = await conn.query(query, params);
    return rows;
  } finally {
    await conn.end();
  }
}
