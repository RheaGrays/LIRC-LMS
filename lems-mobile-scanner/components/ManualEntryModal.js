import React, { useState } from 'react';
import { View, Text, TextInput, Modal, TouchableOpacity, KeyboardAvoidingView, Platform } from 'react-native';
import styles from '../styles';

export default function ManualEntryModal({ visible, onClose, onSubmit }) {
  const [manualId, setManualId] = useState('');

  const handleSubmit = () => {
    onSubmit(manualId);
    setManualId('');
    onClose();
  };

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
        <TouchableOpacity activeOpacity={1} onPress={onClose} style={styles.modalBg}>
            <TouchableOpacity activeOpacity={1} onPress={(e) => e.stopPropagation()} style={styles.modalContent}>
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
                <TouchableOpacity style={styles.buttonSubmit} onPress={handleSubmit}>
                    <Text style={styles.buttonText}>Submit ID</Text>
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
