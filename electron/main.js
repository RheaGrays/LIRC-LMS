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

    try {
        phpProcess = spawn(phpExec, ['artisan', 'serve', '--port=8000'], {
            cwd: projectPath,
            detached: false,
            stdio: 'ignore'
        });

        phpProcess.on('error', (err) => {
            console.error('PHP spawn error:', err);
            // If custom path failed, try system 'php' safely without throwing modal exception
            if (phpExec !== 'php') {
                try {
                    const fallbackProcess = spawn('php', ['artisan', 'serve', '--port=8000'], {
                        cwd: projectPath,
                        detached: false,
                        stdio: 'ignore'
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
    const targetRoute = isAdmin ? '/admin/login' : '/kiosk';

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

    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
    });

    // Safety fallback: force show window after 800ms if ready-to-show hasn't fired yet
    setTimeout(() => {
        if (mainWindow && !mainWindow.isVisible()) {
            mainWindow.show();
        }
    }, 800);

    if (isAdmin) {
        mainWindow.maximize();
    }

    // Security: Block all external navigation
    mainWindow.webContents.on('will-navigate', (event, url) => {
        if (!url.startsWith('http://127.0.0.1:8000')) {
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

    const targetUrl = `http://127.0.0.1:8000${targetRoute}`;

    checkServerReady('http://127.0.0.1:8000/kiosk', () => {
        if (mainWindow) {
            mainWindow.loadURL(targetUrl);
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
        try { phpProcess.kill(); } catch (e) {}
    }
    if (process.platform !== 'darwin') app.quit();
});
