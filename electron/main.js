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
    const maxAttempts = 25; // max 7.5 seconds wait

    const doCheck = () => {
        attempts++;
        http.get(url, (res) => {
            if (res.statusCode === 200 || res.statusCode === 302 || res.statusCode === 404 || res.statusCode === 500) {
                callback(true);
            } else if (attempts < maxAttempts) {
                setTimeout(doCheck, 300);
            } else {
                callback(false);
            }
        }).on('error', () => {
            if (attempts < maxAttempts) {
                setTimeout(doCheck, 300);
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

        const env = { 
            ...process.env, 
            PATH: `${phpDir};${process.env.PATH}`,
            LARAVEL_STORAGE_PATH: userStoragePath
        };

        phpProcess = spawn(phpExec, ['artisan', 'serve', '--port=8000'], {
            cwd: projectPath,
            detached: false,
            stdio: 'ignore',
            env: env
        });

        phpProcess.on('error', (err) => {
            console.error('PHP spawn error:', err);
            if (phpExec !== 'php') {
                try {
                    const fallbackProcess = spawn('php', ['artisan', 'serve', '--port=8000'], {
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

    mainWindow.webContents.once('did-finish-load', () => {
        if (mainWindow && !mainWindow.isVisible()) {
            mainWindow.show();
        }
    });

    if (isAdmin) {
        mainWindow.maximize();
    }

    // Security: Block non-local external navigation while permitting any private local network IP (192.168.x.x, 10.x.x.x, 127.0.0.1, localhost)
    mainWindow.webContents.on('will-navigate', (event, url) => {
        const isLocal = url.startsWith('http://127.0.0.1') || 
                        url.startsWith('http://localhost') || 
                        url.startsWith('http://192.168.') || 
                        url.startsWith('http://10.');
        if (!isLocal) {
            event.preventDefault();
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

    // Try local server first, then host network IP
    const localTargetUrl = `http://127.0.0.1:8000${targetRoute}`;
    const networkTargetUrl = `http://192.168.100.14:8000${targetRoute}`;

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
        } else {
            // Check network host IP if local server is not running
            checkServerReady('http://192.168.100.14:8000/kiosk', (isNetworkReady) => {
                if (!mainWindow) return;
                if (isNetworkReady) {
                    mainWindow.loadURL(networkTargetUrl);
                } else {
                    renderErrorPage();
                }
            });
        }
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
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
