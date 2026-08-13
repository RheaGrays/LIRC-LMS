import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, Modal, TouchableOpacity, KeyboardAvoidingView, Platform } from 'react-native';
import styles from '../styles';

export default function SettingsModal({ visible, onClose, serverUrl, onSave }) {
  const [tempUrl, setTempUrl] = useState('');

  useEffect(() => {
    if (visible) {
      setTempUrl(serverUrl.replace(/\/api\/kiosk\/process$/, ''));
    }
  }, [visible, serverUrl]);

  const handleSave = () => {
    onSave(tempUrl);
  };

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
        <TouchableOpacity activeOpacity={1} onPress={onClose} style={styles.modalBg}>
            <TouchableOpacity activeOpacity={1} onPress={(e) => e.stopPropagation()} style={styles.modalContent}>
                <Text style={styles.modalTitle}>Server Connection Settings</Text>
                <Text style={{ fontSize: 12, color: '#64748b', marginBottom: 8 }}>
                    Enter your LEMS server IP address or domain (e.g. http://192.168.100.14:8000)
                </Text>
                <View style={{ backgroundColor: '#f1f5f9', padding: 10, borderRadius: 8, marginBottom: 14 }}>
                    <Text style={{ fontSize: 11, color: '#475569', fontWeight: 'bold' }}>Current Active Target:</Text>
                    <Text style={{ fontSize: 11, color: '#0f172a', fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace', marginTop: 2 }}>{serverUrl}</Text>
                </View>
                <TextInput 
                    style={styles.input} 
                    placeholder="e.g. http://192.168.100.14:8000" 
                    placeholderTextColor="#999"
                    value={tempUrl} 
                    onChangeText={setTempUrl}
                    autoCapitalize="none"
                />
                <TouchableOpacity style={styles.buttonSubmit} onPress={handleSave}>
                    <Text style={styles.buttonText}>Save & Apply</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.buttonCancel} onPress={onClose}>
                    <Text style={styles.buttonTextCancel}>Cancel</Text>
                </TouchableOpacity>
            </TouchableOpacity>
        </TouchableOpacity>
        </KeyboardAvoidingView>
    </Modal>
  );
}
