import mysql from 'mysql2/promise';

// Direct DB access for test fixtures and cleanup.
//
// docker-compose.yml publishes MariaDB on 8306 both locally and in CI, so tests
// can tidy up after themselves without exposing a reset endpoint on the site.
// Every value is overridable, mirroring how playwright.config.ts handles BASE_URL.
//
// The variables are MKPC_DB_*-prefixed rather than the bare DB_HOST / DB_USER
// names: this module only ever issues DELETEs, and those generic names are
// already in use for other databases (see the migration rule in CLAUDE.md), so
// an unrelated export in the shell must not be able to redirect them.
//
// Note for multi-worktree setups: sibling checkouts share one MariaDB container
// on 8306 while each gets its own web port. Owner tags are per spec file, not
// per checkout, so two worktrees running the suite at the same time will clean
// up each other's fixtures. Run one suite at a time, or point MKPC_DB_PORT and
// BASE_URL at a dedicated stack.
const config = {
  host: process.env.MKPC_DB_HOST || '127.0.0.1',
  port: Number(process.env.MKPC_DB_PORT || 8306),
  user: process.env.MKPC_DB_USER || 'root',
  password: process.env.MKPC_DB_PASSWORD || 'root',
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
