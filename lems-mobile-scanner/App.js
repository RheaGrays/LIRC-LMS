import React, { useState, useEffect, useRef } from 'react';
import { StyleSheet, Text, View, TextInput, Modal, TouchableOpacity, AppState, Platform, Image, Animated, ImageBackground } from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';

const API_URL = 'http://192.168.100.14:8000/api/kiosk/process';
const QUEUE_KEY = '@offline_queue';

export default function App() {
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [showManual, setShowManual] = useState(false);
  const [manualId, setManualId] = useState('');
  const [queueCount, setQueueCount] = useState(0);
  const [isProcessing, setIsProcessing] = useState(false);
  const [result, setResult] = useState(null); // {status, message}
  
  // Splash Screen State
  const [showSplash, setShowSplash] = useState(true);
  const [splashProgress, setSplashProgress] = useState(0);
  const [splashStatus, setSplashStatus] = useState('Initializing Hardware Scanner...');
  const fadeAnim = useRef(new Animated.Value(1)).current;
  
  useEffect(() => {
    loadQueueCount();
    syncQueue();
    runSplashSequence();
  }, []);

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

  const syncQueue = async () => {
    try {
      const queueStr = await AsyncStorage.getItem(QUEUE_KEY);
      if (!queueStr) return;
      let queue = JSON.parse(queueStr);
      if (queue.length === 0) return;
      
      const newQueue = [...queue];
      for (let i = 0; i < queue.length; i++) {
        const id = queue[i];
        try {
          await axios.post(API_URL, { student_id: id }, { timeout: 4000 });
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
      const res = await axios.post(API_URL, { student_id: id }, { timeout: 5000 });
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
        setResult({ status: 'offline', message: `No network. Saved offline (${id})` });
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
        <View style={styles.queueBadge}>
            <Ionicons name="cloud-offline" size={14} color="#c41e2a" style={{marginRight: 4}} />
            <Text style={styles.queueText}>Queue: {queueCount}</Text>
        </View>
      </View>
      
      <View style={styles.scannerWrapper}>
        {!result ? (
            <View style={styles.scannerContainer}>
            <CameraView 
                style={StyleSheet.absoluteFillObject} 
                facing="back"
                barcodeScannerSettings={{ barcodeTypes: ['qr', 'code128', 'code39', 'upc_a'] }}
                onBarcodeScanned={scanned ? undefined : handleBarCodeScanned} 
            />
            <View style={styles.overlay}>
                <View style={styles.reticle} />
                {isProcessing && <Text style={styles.processingText}>Processing...</Text>}
            </View>
            </View>
        ) : (
            <View style={styles.resultContainer}>
                <View style={[styles.resultIcon, result.status === 'success' ? styles.iconSuccess : result.status === 'offline' ? styles.iconOffline : styles.iconError]} />
                <Text style={styles.resultTitle}>{result.status === 'success' ? 'Success' : (result.status === 'offline' ? 'Saved Offline' : 'Error')}</Text>
                <Text style={styles.resultMessage}>{result.message}</Text>
            </View>
        )}
      </View>

      <View style={styles.footer}>
         <TouchableOpacity style={styles.button} onPress={() => setShowManual(true)}>
            <Ionicons name="keypad" size={18} color="#fff" style={{marginRight: 6}} />
            <Text style={styles.buttonText}>Manual Entry</Text>
         </TouchableOpacity>
         <TouchableOpacity style={styles.buttonSecondary} onPress={syncQueue}>
            <Ionicons name="sync" size={18} color="#4b5563" style={{marginRight: 6}} />
            <Text style={styles.buttonTextSecondary}>Sync Queue</Text>
         </TouchableOpacity>
      </View>

      <Modal visible={showManual} transparent animationType="slide">
         <View style={styles.modalBg}>
            <View style={styles.modalContent}>
               <Text style={styles.modalTitle}>Type ID Manually</Text>
               <TextInput 
                  style={styles.input} 
                  placeholder="e.g. 2024-00123" 
                  placeholderTextColor="#999"
                  value={manualId} 
                  onChangeText={setManualId}
                  autoCapitalize="none"
                  autoFocus
               />
               <TouchableOpacity style={styles.buttonSubmit} onPress={() => { setShowManual(false); processId(manualId); setManualId(''); }}>
                  <Text style={styles.buttonText}>Submit ID</Text>
               </TouchableOpacity>
               <TouchableOpacity style={styles.buttonCancel} onPress={() => setShowManual(false)}>
                  <Text style={styles.buttonTextCancel}>Cancel</Text>
               </TouchableOpacity>
            </View>
         </View>
      </Modal>
      </View>

      {/* CINEMATIC SPLASH SCREEN OVERLAY */}
      {showSplash && (
        <Animated.View style={[styles.splashContainer, { opacity: fadeAnim }]}>
           <ImageBackground source={require('./assets/bg.jpg')} style={styles.splashBg} imageStyle={{ opacity: 0.08 }}>
               
               {/* Top Bar */}
               <View style={styles.splashTopBar}>
                   <View style={styles.splashBadge}>
                       <Ionicons name="hardware-chip" size={12} color="#7f1d1d" />
                       <Text style={styles.splashBadgeText}>LEMS OS V1.0</Text>
                   </View>
                   <View style={styles.splashBadgeRight}>
                       <View style={{ alignItems: 'flex-end', marginRight: 6 }}>
                           <Text style={styles.splashCjcText}>COR JESU COLLEGE</Text>
                       </View>
                       <Image source={require('./assets/CorJesu_Logo.png')} style={{width: 20, height: 20}} />
                   </View>
               </View>

               {/* Center Content */}
               <View style={styles.splashCenterContent}>
                   {/* Emblem */}
                   <View style={styles.emblemContainer}>
                       <View style={styles.emblemGlow} />
                       <View style={styles.emblemOuterRing}>
                           <View style={styles.emblemInnerRing}>
                               <Image source={require('./assets/CorJesu_Logo.png')} style={{width: 80, height: 80, resizeMode: 'contain'}} />
                           </View>
                       </View>
                   </View>

                   <Text style={styles.splashMainTitle}>COR JESU COLLEGE</Text>
                   
                   <View style={styles.splashDivider}>
                       <View style={styles.splashDividerLine} />
                       <View style={styles.splashDividerDiamond} />
                       <View style={styles.splashDividerLine} />
                   </View>

                   <Text style={styles.splashSubTitle}>LIBRARY INFORMATION & RESOURCE CENTER</Text>
                   <Text style={styles.splashDescText}>Library Entrance Monitoring & Attendance System</Text>
               </View>

               {/* Bottom Loading Bar */}
               <View style={styles.splashBottomSection}>
                   <View style={styles.loadingBarContainer}>
                       <View style={styles.loadingBarTrack}>
                           <View style={[styles.loadingBarFill, { width: `${splashProgress}%` }]} />
                       </View>
                       <Text style={styles.loadingBarPercent}>{splashProgress}%</Text>
                   </View>
                   <Text style={styles.loadingStatusText}>{splashStatus}</Text>
               </View>

           </ImageBackground>
        </Animated.View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#c41e2a', // CJC Red theme
    paddingTop: 50,
  },
  textWhite: {
    color: '#fff',
    textAlign: 'center',
    marginTop: 20,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingBottom: 20,
  },
  headerTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  title: {
    color: '#fff',
    fontSize: 22,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  queueBadge: {
    backgroundColor: '#fff', 
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    flexDirection: 'row',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  queueText: {
    color: '#c41e2a',
    fontWeight: 'bold',
    fontSize: 12,
  },
  scannerWrapper: {
    flex: 1,
    backgroundColor: '#000',
    overflow: 'hidden',
    position: 'relative',
  },
  scannerContainer: {
    flex: 1,
  },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    justifyContent: 'center',
    alignItems: 'center',
  },
  reticle: {
    width: 250,
    height: 250,
    borderWidth: 2,
    borderColor: 'rgba(255, 255, 255, 0.4)',
    borderRadius: 20,
  },
  processingText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
    marginTop: 20,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
  },
  resultContainer: {
    flex: 1,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  resultIcon: {
    width: 80,
    height: 80,
    borderRadius: 40,
    marginBottom: 20,
  },
  iconSuccess: {
    backgroundColor: '#4ade80',
  },
  iconOffline: {
    backgroundColor: '#60a5fa',
  },
  iconError: {
    backgroundColor: '#f87171',
  },
  resultTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1f2937',
    marginBottom: 10,
  },
  resultMessage: {
    fontSize: 16,
    color: '#6b7280',
    textAlign: 'center',
  },
  footer: {
    flexDirection: 'row',
    padding: 20,
    backgroundColor: '#fff',
    gap: 12,
    borderTopWidth: 1,
    borderTopColor: '#f3f4f6',
    paddingBottom: Platform.OS === 'ios' ? 40 : 20,
  },
  button: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: '#c41e2a',
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  buttonSecondary: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: '#f9fafb',
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#e5e7eb',
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 15,
  },
  buttonTextSecondary: {
    color: '#4b5563',
    fontWeight: 'bold',
    fontSize: 15,
  },
  modalBg: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 30,
    borderTopRightRadius: 30,
    padding: 30,
    paddingBottom: Platform.OS === 'ios' ? 50 : 30,
  },
  modalTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#c41e2a',
    marginBottom: 20,
  },
  input: {
    backgroundColor: '#f9fafb',
    borderWidth: 1,
    borderColor: '#e5e7eb',
    borderRadius: 12,
    padding: 16,
    fontSize: 18,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
    marginBottom: 20,
  },
  buttonSubmit: {
    backgroundColor: '#c41e2a',
    padding: 18,
    borderRadius: 12,
    alignItems: 'center',
    marginBottom: 10,
  },
  buttonCancel: {
    backgroundColor: '#f3f4f6',
    padding: 18,
    borderRadius: 12,
    alignItems: 'center',
  },
  buttonTextCancel: {
    color: '#4b5563',
    fontWeight: 'bold',
    fontSize: 16,
  },
  
  // SPLASH SCREEN STYLES
  splashContainer: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#fcf9f2',
    zIndex: 9999,
    elevation: 9999,
  },
  splashBg: {
    flex: 1,
    justifyContent: 'space-between',
    padding: 20,
    paddingTop: 50,
  },
  splashTopBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
  },
  splashBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.8)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  splashBadgeText: {
    fontSize: 9,
    fontWeight: 'bold',
    color: '#1e293b',
    marginLeft: 4,
    letterSpacing: 1,
  },
  splashBadgeRight: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.8)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  splashCjcText: {
    fontSize: 10,
    fontWeight: '900',
    color: '#7f1d1d',
    letterSpacing: 1,
  },
  splashMottoText: {
    fontSize: 7,
    fontWeight: 'bold',
    color: '#d97706',
    letterSpacing: 0.5,
  },
  splashCenterContent: {
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 50,
  },
  emblemContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  emblemGlow: {
    position: 'absolute',
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(217, 119, 6, 0.2)',
    transform: [{ scale: 1.2 }],
  },
  emblemOuterRing: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#d97706',
    padding: 6,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
  },
  emblemInnerRing: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 60,
    alignItems: 'center',
    justifyContent: 'center',
  },
  splashMainTitle: {
    fontSize: 28,
    fontWeight: '900',
    color: '#7f1d1d',
    letterSpacing: 1,
    textAlign: 'center',
  },
  splashDivider: {
    flexDirection: 'row',
    alignItems: 'center',
    width: 200,
    marginVertical: 12,
  },
  splashDividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: '#d97706',
    opacity: 0.5,
  },
  splashDividerDiamond: {
    width: 6,
    height: 6,
    backgroundColor: '#d97706',
    transform: [{ rotate: '45deg' }],
    marginHorizontal: 10,
  },
  splashSubTitle: {
    fontSize: 11,
    fontWeight: '900',
    color: '#7f1d1d',
    letterSpacing: 2,
    textAlign: 'center',
  },
  splashDescText: {
    fontSize: 10,
    color: '#64748b',
    fontWeight: '600',
    marginTop: 6,
    letterSpacing: 0.5,
  },
  splashBottomSection: {
    alignItems: 'center',
    paddingBottom: 20,
  },
  loadingBarContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '80%',
    marginBottom: 8,
  },
  loadingBarTrack: {
    flex: 1,
    height: 10,
    backgroundColor: 'rgba(0,0,0,0.05)',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.1)',
    overflow: 'hidden',
    padding: 2,
  },
  loadingBarFill: {
    height: '100%',
    backgroundColor: '#c41e2a',
    borderRadius: 10,
  },
  loadingBarPercent: {
    fontSize: 12,
    fontWeight: '900',
    color: '#7f1d1d',
    marginLeft: 10,
    width: 35,
  },
  loadingStatusText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#475569',
  }
});
