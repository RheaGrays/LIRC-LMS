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

function checkServerReady(url, callback) {
    let attempts = 0;
    const maxAttempts = 60; // 60 * 500ms = 30 seconds max wait

    const doCheck = () => {
        attempts++;
        const req = http.get(url, { timeout: 2000 }, (res) => {
            res.resume();
            if (res.statusCode === 200 || res.statusCode === 302 || res.statusCode === 404 || res.statusCode === 500) {
                callback(true);
            } else if (attempts < maxAttempts) {
                setTimeout(doCheck, 500);
            } else {
                callback(false);
            }
        });
        
        req.on('timeout', () => {
            req.destroy();
            if (attempts < maxAttempts) setTimeout(doCheck, 500);
            else callback(false);
        });

        req.on('error', (err) => {
            if (attempts < maxAttempts) {
                setTimeout(doCheck, 500);
            } else {
                callback(false);
            }
        });
    };

    doCheck();
}

function findPhpExecutable() {
    const projectPath = app.isPackaged 
        ? path.join(process.resourcesPath, 'php_bundle')
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

function startPhpServer() {
    const projectPath = app.isPackaged 
        ? path.join(process.resourcesPath, 'app')
        : path.join(__dirname, '..');

    const phpExec = findPhpExecutable();
    const phpDir = path.dirname(phpExec);

    try {
        // Move storage to AppData to avoid Read-Only errors in Program Files
        const userStoragePath = path.join(app.getPath('userData'), 'laravel_storage');
        const requiredDirs = [
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'logs'
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

        const env = { 
            ...process.env, 
            PATH: `${phpDir};${process.env.PATH}`,
            LARAVEL_STORAGE_PATH: userStoragePath
        };

        phpProcess = spawn(phpExec, ['artisan', 'serve', '--port=8000', '--no-reload'], {
            cwd: projectPath,
            detached: false,
            stdio: 'ignore',
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

    // Grant camera/media permissions explicitly
    mainWindow.webContents.session.setPermissionRequestHandler((webContents, permission, callback) => {
        callback(true);
    });

    mainWindow.webContents.session.setPermissionCheckHandler((webContents, permission) => {
        return true;
    });

    mainWindow.webContents.session.setDevicePermissionHandler((details) => {
        return true;
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

    const renderErrorPage = () => {
        if (!mainWindow) return;
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>LEMS Server Connection Error</title>
                <style>
                    body { font-family: system-ui, sans-serif; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                    .card { background: #1e293b; border: 1px solid #334155; padding: 40px; border-radius: 20px; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
                    h2 { color: #f43f5e; margin-top: 0; }
                    p { color: #94a3b8; font-size: 14px; line-height: 1.6; }
                    .code { font-family: monospace; background: #020617; color: #38bdf8; padding: 10px; border-radius: 8px; font-size: 13px; margin: 15px 0; display: block; text-align: left; }
                    button { background: #c41e3a; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 14px; margin-top: 15px; }
                    button:hover { background: #991b1b; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>Server Not Running</h2>
                    <p>LEMS could not connect to the local server or host network server at <code>http://127.0.0.1:8000</code>.</p>
                    <p><strong>To resolve this on the Host PC:</strong></p>
                    <span class="code">1. Start MySQL in XAMPP<br>2. Run: php artisan serve --host=0.0.0.0 --port=8000</span>
                    <button onclick="window.location.reload()">Retry Connection</button>
                </div>
            </body>
            </html>
        `;
        mainWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(html)}`);
    };

    checkServerReady('http://127.0.0.1:8000/kiosk', (isLocalReady) => {
        if (!mainWindow) return;
        if (isLocalReady) {
            mainWindow.loadURL(localTargetUrl);
        } else if (networkTargetUrl && hostConfig) {
            // Only attempt network fallback if lems.host.json is configured
            const networkCheckUrl = `http://${hostConfig.host}:${hostConfig.port}/kiosk`;
            checkServerReady(networkCheckUrl, (isNetworkReady) => {
                if (!mainWindow) return;
                if (isNetworkReady) {
                    mainWindow.loadURL(networkTargetUrl);
                } else {
                    renderErrorPage();
                }
            });
        } else {
            renderErrorPage();
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

app.whenReady().then(() => {
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
