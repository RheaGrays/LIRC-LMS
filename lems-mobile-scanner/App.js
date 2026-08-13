import React, { useState, useEffect, useRef } from 'react';
import { Text, View, TouchableOpacity, Animated, Image } from 'react-native';
import { useCameraPermissions } from 'expo-camera';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';

import { DEFAULT_API_URL, QUEUE_KEY, SERVER_URL_KEY } from './config';
import styles from './styles';

import SplashScreen from './components/SplashScreen';
import ResultCard from './components/ResultCard';
import ManualEntryModal from './components/ManualEntryModal';
import SettingsModal from './components/SettingsModal';
import ScannerView from './components/ScannerView';

export default function App() {
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [showManual, setShowManual] = useState(false);
  const [showSettings, setShowSettings] = useState(false);
  const [serverUrl, setServerUrl] = useState(DEFAULT_API_URL);
  const [queueCount, setQueueCount] = useState(0);
  const [isProcessing, setIsProcessing] = useState(false);
  const [result, setResult] = useState(null); // {status, message}
  
  // Splash Screen State
  const [showSplash, setShowSplash] = useState(true);
  const [splashProgress, setSplashProgress] = useState(0);
  const [splashStatus, setSplashStatus] = useState('Initializing Hardware Scanner...');
  const fadeAnim = useRef(new Animated.Value(1)).current;
  
  useEffect(() => {
    loadSettings();
    loadQueueCount();
    syncQueue();
    runSplashSequence();
  }, []);

  const loadSettings = async () => {
    try {
      const savedUrl = await AsyncStorage.getItem(SERVER_URL_KEY);
      if (savedUrl) {
        setServerUrl(savedUrl);
      }
    } catch (e) {}
  };

  const saveSettings = async (tempUrl) => {
    if (!tempUrl.trim()) return;
    let url = tempUrl.trim();
    // Clean up duplicate protocol prefixes if user typed or pasted http://http://
    url = url.replace(/^(https?:\/\/)+/i, '');
    url = 'http://' + url;
    if (!url.endsWith('/api/kiosk/process')) {
      url = url.replace(/\/$/, '') + '/api/kiosk/process';
    }
    try {
      await AsyncStorage.setItem(SERVER_URL_KEY, url);
      setServerUrl(url);
      setShowSettings(false);
      // Immediately test & sync queue with new URL
      syncQueue(url);
    } catch (e) {}
  };

  const runSplashSequence = () => {
    let p = 0;
    const timer = setInterval(() => {
      p += 1;
      setSplashProgress(Math.min(p, 100));

      if (p < 30) {
        setSplashStatus('Initializing Hardware Scanners...');
      } else if (p < 65) {
        setSplashStatus('Connecting to Library Database...');
      } else if (p < 90) {
        setSplashStatus('Loading Mobile Interface...');
      } else {
        setSplashStatus('Welcome to CJC Library!');
      }

      if (p >= 100) {
        clearInterval(timer);
        setTimeout(() => {
          Animated.timing(fadeAnim, {
            toValue: 0,
            duration: 700,
            useNativeDriver: true,
          }).start(() => {
            setShowSplash(false);
          });
        }, 500);
      }
    }, 40);
  };

  const loadQueueCount = async () => {
    try {
      const queue = JSON.parse(await AsyncStorage.getItem(QUEUE_KEY)) || [];
      setQueueCount(queue.length);
    } catch (e) {
      console.error(e);
    }
  };

  const syncQueue = async (overrideUrl = null) => {
    const targetUrl = overrideUrl || serverUrl;
    try {
      const queueStr = await AsyncStorage.getItem(QUEUE_KEY);
      if (!queueStr) return;
      let queue = JSON.parse(queueStr);
      if (queue.length === 0) return;
      
      const newQueue = [...queue];
      for (let i = 0; i < queue.length; i++) {
        const id = queue[i];
        try {
          await axios.post(targetUrl, { student_id: id }, { timeout: 12000 });
          newQueue.splice(newQueue.indexOf(id), 1); // Remove if successful
        } catch (err) {
          // Keep in queue on error (no network, timeout, or 500)
        }
      }
      await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(newQueue));
      setQueueCount(newQueue.length);
    } catch (e) {}
  };

  const processId = async (id) => {
    if(isProcessing) return;
    setIsProcessing(true);
    setScanned(true);
    try {
      const res = await axios.post(serverUrl, { student_id: id }, { timeout: 12000 });
      // Server responded — success or logical error (student not found, etc.)
      const status = res.data.status || 'success';
      const message = res.data.message || 'Processed.';
      setResult({ status, message });
      syncQueue(); // trigger sync in case we just came back online
    } catch (err) {
      // Check if server responded with an error (e.g. 422, 500)
      if (err.response) {
        // Server is reachable but returned an error
        const message = err.response.data?.message || 'Server error. Please try again.';
        setResult({ status: 'error', message });
      } else {
        // No response = network issue, save offline
        const queue = JSON.parse(await AsyncStorage.getItem(QUEUE_KEY)) || [];
        queue.push(id);
        await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
        setQueueCount(queue.length);
        const hostUrl = serverUrl.replace(/\/api\/kiosk\/process$/, '');
        const detail = err.message ? ` (${err.message})` : '';
        setResult({ status: 'offline', message: `Server Unreachable: ${hostUrl}${detail}\nSaved offline (${id})` });
      }
    } finally {
      setIsProcessing(false);
      setTimeout(() => {
        setResult(null);
        setScanned(false);
      }, 3000);
    }
  };

  const handleBarCodeScanned = ({ type, data }) => {
    processId(data);
  };

  if (!permission) {
    return <View style={styles.container}><Text style={styles.textWhite}>Requesting camera permission...</Text></View>;
  }
  if (!permission.granted) {
    return (
      <View style={[styles.container, { justifyContent: 'center', alignItems: 'center', padding: 20 }]}>
        <Text style={[styles.textWhite, { fontSize: 18, marginBottom: 20 }]}>Camera access is required to scan IDs.</Text>
        <TouchableOpacity style={[styles.buttonSubmit, { width: '100%' }]} onPress={requestPermission}>
          <Text style={styles.buttonText}>Grant Camera Permission</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={{ flex: 1 }}>
      <View style={styles.container}>
      <StatusBar style={showSplash ? "dark" : "light"} />
      <View style={styles.header}>
        <View style={styles.headerTitleRow}>
            <Image source={require('./assets/CorJesu_Logo.png')} style={{width: 32, height: 32, marginRight: 8, resizeMode: 'contain'}} />
            <Text style={styles.title}>LEMS Scanner</Text>
        </View>
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
            <View style={styles.queueBadge}>
                <Ionicons name="cloud-offline" size={14} color="#c41e2a" style={{marginRight: 4}} />
                <Text style={styles.queueText}>Queue: {queueCount}</Text>
            </View>
            <TouchableOpacity 
                onPress={() => setShowSettings(true)}
                style={{ padding: 6, backgroundColor: '#f1f5f9', borderRadius: 8 }}>
                <Ionicons name="settings-outline" size={18} color="#475569" />
            </TouchableOpacity>
        </View>
      </View>
      
      <View style={styles.scannerWrapper}>
        {!result ? (
            <ScannerView scanned={scanned} isProcessing={isProcessing} onBarCodeScanned={handleBarCodeScanned} />
        ) : (
            <ResultCard result={result} />
        )}
      </View>

      <View style={styles.footer}>
         <TouchableOpacity style={styles.button} onPress={() => setShowManual(true)}>
            <Ionicons name="keypad" size={18} color="#fff" style={{marginRight: 6}} />
            <Text style={styles.buttonText}>Manual Entry</Text>
         </TouchableOpacity>
         <TouchableOpacity style={styles.buttonSecondary} onPress={() => syncQueue()}>
            <Ionicons name="sync" size={18} color="#4b5563" style={{marginRight: 6}} />
            <Text style={styles.buttonTextSecondary}>Sync Queue</Text>
         </TouchableOpacity>
      </View>

      <ManualEntryModal visible={showManual} onClose={() => setShowManual(false)} onSubmit={processId} />

      <SettingsModal visible={showSettings} onClose={() => setShowSettings(false)} serverUrl={serverUrl} onSave={saveSettings} />
      </View>

      <SplashScreen visible={showSplash} fadeAnim={fadeAnim} progress={splashProgress} status={splashStatus} />
    </View>
  );
}
