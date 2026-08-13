import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { CameraView } from 'expo-camera';
import styles from '../styles';

export default function ScannerView({ scanned, isProcessing, onBarCodeScanned }) {
  return (
    <View style={styles.scannerContainer}>
      <CameraView 
          style={StyleSheet.absoluteFillObject} 
          facing="back"
          barcodeScannerSettings={{ barcodeTypes: ['qr', 'code128', 'code39', 'upc_a'] }}
          onBarcodeScanned={scanned ? undefined : onBarCodeScanned} 
      />
      <View style={styles.overlay}>
          <View style={styles.reticle} />
          {isProcessing && <Text style={styles.processingText}>Processing...</Text>}
      </View>
    </View>
  );
}
