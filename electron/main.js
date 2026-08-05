import { app, BrowserWindow } from 'electron';
import { spawn } from 'child_process';
import path from 'path';
import http from 'http';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

let mainWindow = null;
let phpProcess = null;

function checkServerReady(url, callback) {
    http.get(url, (res) => {
        if (res.statusCode === 200 || res.statusCode === 302 || res.statusCode === 404 || res.statusCode === 500) {
            callback(true);
        } else {
            setTimeout(() => checkServerReady(url, callback), 300);
        }
    }).on('error', () => {
        setTimeout(() => checkServerReady(url, callback), 300);
    });
}

function startPhpServer() {
    const phpPath = 'C:\\xampp\\php\\php.exe';
    const projectPath = path.join(__dirname, '..');

    try {
        phpProcess = spawn(phpPath, ['artisan', 'serve', '--port=8000'], {
            cwd: projectPath,
            detached: false,
            stdio: 'ignore'
        });
    } catch (e) {
        console.error('Failed to spawn PHP:', e);
    }
}

function createWindow() {
    const iconPath = path.join(__dirname, '../public/cjc-logo.jpeg');
    const isAdmin = process.argv.includes('--admin');
    const targetRoute = isAdmin ? '/admin/login' : '/kiosk';

    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        title: isAdmin ? 'LEMS Admin - Cor Jesu College Library' : 'LEMS Kiosk - Cor Jesu College Library',
        icon: iconPath,
        autoHideMenuBar: true,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.js')
        }
    });

    mainWindow.maximize();

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
