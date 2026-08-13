import { app, BrowserWindow } from 'electron';
import { spawn } from 'child_process';
import path from 'path';
import http from 'http';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

let mainWindow = null;
let phpProcess = null;

/**
 * BUG-04-FIX: 500 is NO LONGER treated as "server ready".
 * Previously, a Laravel crash (HTTP 500) was accepted as "ready", causing Electron
 * to load a blank error page → white screen. Now only 200/302 are accepted.
 * The callback receives { ready, serverError } so the caller can show a targeted error.
 */
function checkServerReady(url, callback) {
    let attempts = 0;
    const maxAttempts = 60; // 60 * 500ms = 30 seconds max wait
    let sawServerError = false;

    const doCheck = () => {
        attempts++;
        const req = http.get(url, { timeout: 2000 }, (res) => {
            res.resume();
            if (res.statusCode === 200 || res.statusCode === 302) {
                callback({ ready: true, serverError: false });
            } else if (res.statusCode >= 500) {
                // Server IS running but Laravel is crashing — keep retrying
                // (may be transient: caches warming, migrations running)
                sawServerError = true;
                if (attempts < maxAttempts) {
                    setTimeout(doCheck, 500);
                } else {
                    callback({ ready: false, serverError: true });
                }
            } else if (attempts < maxAttempts) {
                setTimeout(doCheck, 500);
            } else {
                callback({ ready: false, serverError: sawServerError });
            }
        });
        
        req.on('timeout', () => {
            req.destroy();
            if (attempts < maxAttempts) setTimeout(doCheck, 500);
            else callback({ ready: false, serverError: sawServerError });
        });

        req.on('error', (err) => {
            if (attempts < maxAttempts) {
                setTimeout(doCheck, 500);
            } else {
                callback({ ready: false, serverError: sawServerError });
            }
        });
    };

    doCheck();
}

function findPhpExecutable() {
    const projectPath = app.isPackaged 
        ? path.join(process.resourcesPath, 'app', 'php_bundle')
        : path.join(__dirname, '../php_bundle');

    const bundledPhp = path.join(projectPath, 'php.exe');
    if (fs.existsSync(bundledPhp)) {
        return bundledPhp;
    }

    const userProfile = process.env.USERPROFILE || 'C:\\Users\\Default';
    const candidatePaths = [
        path.join(userProfile, '.config\\herd\\bin\\php84\\php.exe'),
        path.join(userProfile, '.config\\herd\\bin\\php83\\php.exe'),
        path.join(userProfile, '.config\\herd\\bin\\php82\\php.exe'),
        'C:\\xampp\\php\\php.exe',
        'D:\\xampp\\php\\php.exe',
        'E:\\xampp\\php\\php.exe',
        'C:\\php\\php.exe',
        'C:\\laragon\\bin\\php\\current\\php.exe',
        'C:\\Program Files\\PHP\\php.exe',
        'C:\\tools\\php\\php.exe'
    ];

    for (const p of candidatePaths) {
        if (fs.existsSync(p)) {
            return p;
        }
    }
    return 'php'; // default system PATH fallback
}

/**
 * BUG-04 FIX: Read the network host IP from lems.host.json instead of hardcoding it.
 *
 * Create a file named `lems.host.json` in the project root with:
 *   { "host": "192.168.X.X", "port": 8000 }
 *
 * If the file is missing or has no host, the network fallback is skipped entirely
 * and the error page is shown when the local server is not running.
 */
function readHostConfig() {
    const configPaths = [
        app.isPackaged
            ? path.join(process.resourcesPath, 'lems.host.json')
            : path.join(__dirname, '../lems.host.json'),
        path.join(app.getPath('userData'), 'lems.host.json'), // also check AppData
    ];

    for (const configPath of configPaths) {
        if (fs.existsSync(configPath)) {
            try {
                const raw = fs.readFileSync(configPath, 'utf8');
                const cfg = JSON.parse(raw);
                if (cfg.host && typeof cfg.host === 'string') {
                    return { host: cfg.host, port: cfg.port || 8000 };
                }
            } catch (e) {
                console.warn('lems.host.json parse error:', e.message);
            }
        }
    }
    return null; // no config — skip network fallback
}

/**
 * Build the environment variables object used by both migrations and artisan serve.
 * Centralised here so the two call-sites cannot drift out of sync.
 *
 * BUG-03-FIX: LARAVEL_STORAGE_PATH now receives the FULL absolute path (with
 * drive letter). Only the cache-related env vars (APP_PACKAGES_CACHE, etc.)
 * still have the drive letter stripped because Laravel's normalizeCachePath()
 * only recognises '/' and '\\' as absolute-path prefixes.
 * bootstrap/app.php has been patched to call addAbsoluteCachePathPrefix()
 * so even cache paths work with the full drive-letter path.
 */
function buildLaravelEnv(phpDir, projectPath) {
    const userStoragePath = path.join(app.getPath('userData'), 'laravel_storage');

    // BUG-02-FIX: Create ALL directories Laravel requires — including sessions
    // and cache/data which were previously missing from the packaged build.
    const requiredDirs = [
        'framework/cache/data',
        'framework/sessions',
        'framework/views',
        'framework/testing',
        'logs',
        'app/public'
    ];

    if (!fs.existsSync(userStoragePath)) {
        fs.mkdirSync(userStoragePath, { recursive: true });
    }

    requiredDirs.forEach(dir => {
        const fullPath = path.join(userStoragePath, dir);
        if (!fs.existsSync(fullPath)) {
            fs.mkdirSync(fullPath, { recursive: true });
        }
    });

    // Ensure bootstrap/cache exists in the project path
    const bootstrapCachePath = path.join(projectPath, 'bootstrap', 'cache');
    if (!fs.existsSync(bootstrapCachePath)) {
        fs.mkdirSync(bootstrapCachePath, { recursive: true });
    }

    const sqliteOverride = app.isPackaged ? {
        DB_CONNECTION: 'sqlite',
        DB_DATABASE: path.join(app.getPath('userData'), 'lems.sqlite'),
    } : {};

    // BUG-03-FIX: Use the FULL absolute path for LARAVEL_STORAGE_PATH.
    // useStoragePath() in bootstrap/app.php stores it as-is — no normalization.
    // Cache env vars (APP_PACKAGES_CACHE etc.) still need the drive letter stripped
    // because normalizeCachePath() only treats '/' and '\\' as absolute prefixes,
    // BUT bootstrap/app.php now also calls addAbsoluteCachePathPrefix() to cover
    // Windows drive-letter paths. We keep the strip as a belt-and-suspenders fallback.
    const cacheSafePath = process.platform === 'win32'
        ? userStoragePath.replace(/^[a-zA-Z]:/, '')
        : userStoragePath;

    return {
        ...process.env,
        PATH: `${phpDir};${process.env.PATH}`,
        LARAVEL_STORAGE_PATH: userStoragePath,          // full path — Bug #3 fix
        APP_PACKAGES_CACHE: path.join(cacheSafePath, 'packages.php'),
        APP_SERVICES_CACHE: path.join(cacheSafePath, 'services.php'),
        VIEW_COMPILED_PATH: path.join(cacheSafePath, 'framework', 'views'),
        ...sqliteOverride,
    };
}

function startPhpServer() {
    const projectPath = app.isPackaged 
        ? path.join(process.resourcesPath, 'app')
        : path.join(__dirname, '..');

    const phpExec = findPhpExecutable();
    const phpDir = path.dirname(phpExec);

    try {
        const env = buildLaravelEnv(phpDir, projectPath);

        const outLog = fs.openSync(path.join(app.getPath('userData'), 'php_server.log'), 'a');
        const errLog = fs.openSync(path.join(app.getPath('userData'), 'php_error.log'), 'a');
        
        phpProcess = spawn(phpExec, ['artisan', 'serve', '--port=8000', '--no-reload'], {
            cwd: projectPath,
            detached: false,
            stdio: ['ignore', outLog, errLog],
            env: env
        });

        phpProcess.on('error', (err) => {
            console.error('PHP spawn error:', err);
            if (phpExec !== 'php') {
                try {
                    const fallbackProcess = spawn('php', ['artisan', 'serve', '--port=8000', '--no-reload'], {
                        cwd: projectPath,
                        detached: false,
                        stdio: 'ignore',
                        env: env
                    });
                    fallbackProcess.on('error', (e) => console.error('System PHP error:', e));
                    phpProcess = fallbackProcess;
                } catch (e) {
                    console.error('Fallback spawn exception:', e);
                }
            }
        });
    } catch (e) {
        console.error('Failed to spawn PHP server:', e);
    }
}

/**
 * Run Laravel migrations automatically on every launch.
 * Safe to run repeatedly — Laravel skips already-applied migrations.
 * Ensures the SQLite database and all tables exist before artisan serve starts.
 */
function runMigrations(phpExec, projectPath, env) {
    return new Promise((resolve) => {
        console.log('[LEMS] Running database migrations...');
        const migrate = spawn(phpExec, ['artisan', 'migrate', '--force', '--seed'], {
            cwd: projectPath,
            detached: false,
            stdio: 'ignore',
            env: env,
        });
        migrate.on('close', (code) => {
            console.log(`[LEMS] Migration finished (exit ${code})`);
            resolve();
        });
        migrate.on('error', (err) => {
            console.error('[LEMS] Migration error:', err);
            resolve(); // still continue — serve may work with existing DB
        });
    });
}

function createWindow() {
    const iconPath = path.join(__dirname, '../public/CorJesu Logo.png');
    const isAdmin = process.argv.includes('--admin');
    const targetRoute = isAdmin ? '/admin/login' : '/kiosk?boot=1';

    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        show: false,
        backgroundColor: '#fcf9f2',
        kiosk: !isAdmin,
        fullscreen: !isAdmin,
        title: isAdmin ? 'LEMS Admin - Library Entrance Monitoring System' : 'LEMS Kiosk - Library Entrance Monitoring System',
        icon: iconPath,
        autoHideMenuBar: true,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.js')
        }
    });

    // SEC-REM-01 FIX: Only grant permissions that LEMS actually needs (camera for registration).
    // Previously callback(true) granted everything: microphone, geolocation, notifications, MIDI, etc.
    const ALLOWED_PERMISSIONS = new Set(['camera', 'media', 'mediaKeySystem']);
    mainWindow.webContents.session.setPermissionRequestHandler((webContents, permission, callback) => {
        callback(ALLOWED_PERMISSIONS.has(permission));
    });

    mainWindow.webContents.session.setPermissionCheckHandler((webContents, permission) => {
        return ALLOWED_PERMISSIONS.has(permission);
    });

    mainWindow.webContents.session.setDevicePermissionHandler((details) => {
        return details.deviceType === 'camera';
    });

    mainWindow.webContents.once('did-finish-load', () => {
        if (mainWindow && !mainWindow.isVisible()) {
            mainWindow.show();
        }
    });

    if (isAdmin) {
        mainWindow.maximize();
    }

    // SEC-04 FIX: Navigation allowlist is now explicit and deny-by-default.
    // Each allowed prefix is documented. Both http:// and https:// are permitted for
    // localhost/127.0.0.1 (e.g. if behind a local TLS proxy). LAN ranges (192.168.*, 10.*)
    // are http-only because they run plain artisan serve. All other URLs are blocked.
    const allowedNavPrefixes = [
        'http://127.0.0.1',
        'https://127.0.0.1',
        'http://localhost',
        'https://localhost',
        'http://192.168.',   // campus LAN — http only (artisan serve)
        'http://10.',        // alternate private range — http only
    ];
    mainWindow.webContents.on('will-navigate', (event, url) => {
        const isAllowed = allowedNavPrefixes.some(prefix => url.startsWith(prefix));
        if (!isAllowed) {
            event.preventDefault();
            console.warn('[LEMS] Blocked navigation to non-local URL:', url);
        }
    });

    // Security: Block new windows/popups
    mainWindow.webContents.setWindowOpenHandler(() => {
        return { action: 'deny' };
    });

    // Security: Block DevTools shortcuts (F12, Ctrl+Shift+I)
    mainWindow.webContents.on('before-input-event', (event, input) => {
        if (input.key === 'F12' || (input.control && input.shift && input.key.toLowerCase() === 'i')) {
            event.preventDefault();
        }
    });

    // BUG-04 FIX: Host IP is now read from lems.host.json — not hardcoded.
    const localTargetUrl = `http://127.0.0.1:8000${targetRoute}`;
    const hostConfig = readHostConfig();
    const networkTargetUrl = hostConfig
        ? `http://${hostConfig.host}:${hostConfig.port}${targetRoute}`
        : null;

    /**
     * BUG-04-FIX: Two distinct error pages — "server not running" vs "server crashed".
     * Previously both cases showed white screen because 500 was treated as ready.
     */
    const renderErrorPage = (serverError = false) => {
        if (!mainWindow) return;
        const userDataPath = app.getPath('userData').replace(/\\/g, '/');
        const title = serverError ? 'Server Error' : 'Server Not Running';
        const heading = serverError ? 'Server Started but Crashed' : 'Server Not Running';
        const message = serverError
            ? `LEMS server started but Laravel returned an error (HTTP 500). This usually means a missing directory, database issue, or configuration problem.`
            : `LEMS could not connect to the local server or host network server at <code>http://127.0.0.1:8000</code>.`;
        const hint = serverError
            ? `Check the error logs for details:<br><code style="word-break:break-all;">${userDataPath}/php_error.log</code><br><code style="word-break:break-all;">${userDataPath}/php_server.log</code>`
            : `The database is created automatically on first launch.<br>No XAMPP or MySQL required.`;

        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>LEMS — ${title}</title>
                <style>
                    body { font-family: system-ui, sans-serif; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                    .card { background: #1e293b; border: 1px solid #334155; padding: 40px; border-radius: 20px; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
                    h2 { color: #f43f5e; margin-top: 0; }
                    p { color: #94a3b8; font-size: 14px; line-height: 1.6; }
                    .code { font-family: monospace; background: #020617; color: #38bdf8; padding: 10px; border-radius: 8px; font-size: 13px; margin: 15px 0; display: block; text-align: left; }
                    button { background: #c41e3a; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 14px; margin-top: 15px; }
                    button:hover { background: #991b1b; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>${heading}</h2>
                    <p>${message}</p>
                    <p><strong>${serverError ? 'How to debug:' : 'To resolve this on the Host PC:'}</strong></p>
                    <span class="code">${hint}</span>
                    <button onclick="window.location.reload()">Retry Connection</button>
                </div>
            </body>
            </html>
        `;
        mainWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(html)}`);
    };

    checkServerReady('http://127.0.0.1:8000/kiosk', (result) => {
        if (!mainWindow) return;
        if (result.ready) {
            mainWindow.loadURL(localTargetUrl);
        } else if (networkTargetUrl && hostConfig) {
            // Only attempt network fallback if lems.host.json is configured
            const networkCheckUrl = `http://${hostConfig.host}:${hostConfig.port}/kiosk`;
            checkServerReady(networkCheckUrl, (netResult) => {
                if (!mainWindow) return;
                if (netResult.ready) {
                    mainWindow.loadURL(networkTargetUrl);
                } else {
                    renderErrorPage(result.serverError || netResult.serverError);
                }
            });
        } else {
            renderErrorPage(result.serverError);
        }
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// Force Chromium to treat the local network IP as a secure origin, 
// so navigator.mediaDevices (the webcam) is not blocked on http://192.168.x.x
const hostConfig = readHostConfig();
if (hostConfig && hostConfig.host) {
    app.commandLine.appendSwitch('unsafely-treat-insecure-origin-as-secure', `http://${hostConfig.host}:${hostConfig.port || 8000}`);
}

app.whenReady().then(async () => {
    const projectPath = app.isPackaged
        ? path.join(process.resourcesPath, 'app')
        : path.join(__dirname, '..');

    // BUG-05-FIX: Delete leftover public/hot file.
    // If this file exists, Laravel's @vite() directive loads assets from
    // http://localhost:5173 (dev server) instead of the compiled build/ dir,
    // causing a white screen because the dev server is not running.
    const hotFilePath = path.join(projectPath, 'public', 'hot');
    if (fs.existsSync(hotFilePath)) {
        try { fs.unlinkSync(hotFilePath); } catch (e) {
            console.warn('[LEMS] Could not delete public/hot:', e.message);
        }
    }

    // BUG-01-FIX: Recreate the public/storage symlink at runtime.
    // The original symlink points to the dev machine's absolute path and breaks
    // on any other PC. We recreate it pointing to the AppData storage location.
    if (app.isPackaged) {
        const publicStorageLink = path.join(projectPath, 'public', 'storage');
        const appPublicDir = path.join(app.getPath('userData'), 'laravel_storage', 'app', 'public');
        try {
            // Remove broken symlink/file if it exists
            if (fs.existsSync(publicStorageLink) || fs.lstatSync(publicStorageLink).isSymbolicLink()) {
                fs.unlinkSync(publicStorageLink);
            }
        } catch (e) { /* does not exist — fine */ }
        try {
            fs.symlinkSync(appPublicDir, publicStorageLink, 'junction');
            console.log('[LEMS] Recreated public/storage symlink →', appPublicDir);
        } catch (e) {
            console.warn('[LEMS] Could not create public/storage symlink:', e.message);
        }
    }

    // Run migrations BEFORE starting artisan serve so the SQLite database
    // and all tables are ready before the first HTTP request comes in.
    if (app.isPackaged) {
        const phpExec = findPhpExecutable();
        const phpDir = path.dirname(phpExec);
        const env = buildLaravelEnv(phpDir, projectPath);
        await runMigrations(phpExec, projectPath, env);
    }

    startPhpServer();
    createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
});

app.on('window-all-closed', () => {
    if (phpProcess) {
        try {
            if (process.platform === 'win32') {
                import('child_process').then(cp => {
                    cp.exec(`taskkill /pid ${phpProcess.pid} /T /F`, (err, stdout, stderr) => {
                        if (process.platform !== 'darwin') app.quit();
                    });
                });
                return; // wait for taskkill to finish before quitting
            } else {
                phpProcess.kill();
            }
        } catch (e) {}
    }
    if (process.platform !== 'darwin') app.quit();
});
