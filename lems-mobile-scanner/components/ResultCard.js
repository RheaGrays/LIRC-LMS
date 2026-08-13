import React from 'react';
import { View, Text } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import styles from '../styles';

export default function ResultCard({ result }) {
  if (!result) return null;

  return (
    <View style={styles.resultCardWrapper}>
        <View style={styles.resultCard}>
            {/* Status Icon Circle */}
            <View style={[
                styles.resultIconCircle,
                result.status === 'success' ? styles.bgSuccess : result.status === 'offline' ? styles.bgOffline : styles.bgError
            ]}>
                <Ionicons 
                    name={
                        result.status === 'success' ? 'checkmark-circle' :
                        result.status === 'offline' ? 'cloud-offline' : 'alert-circle'
                    } 
                    size={52} 
                    color="#ffffff" 
                />
            </View>

            {/* Status Tag Pill */}
            <View style={[
                styles.statusTag,
                result.status === 'success' ? styles.tagSuccess : (result.status === 'cooldown' ? styles.tagWarning : (result.status === 'offline' ? styles.tagOffline : styles.tagError))
            ]}>
                <Text style={[
                    styles.statusTagText,
                    result.status === 'success' ? styles.textSuccess : (result.status === 'cooldown' ? styles.textWarning : (result.status === 'offline' ? styles.textOffline : styles.textError))
                ]}>
                    {result.status === 'success' ? 'SUCCESS' : (result.status === 'cooldown' ? 'ALREADY CHECKED IN' : (result.status === 'offline' ? 'SAVED OFFLINE' : 'SCAN ERROR'))}
                </Text>
            </View>

            {/* Title & Description */}
            <Text style={styles.resultTitle}>
                {result.status === 'success' ? 'Check-in Recorded' : (result.status === 'cooldown' ? 'Cooldown Active' : (result.status === 'offline' ? 'Queued Offline' : 'Process Error'))}
            </Text>
            
            <Text style={styles.resultMessage}>{result.message}</Text>

            {/* Auto Reset Footer Hint */}
            <View style={styles.resultFooterHint}>
                <Ionicons name="time-outline" size={14} color="#94a3b8" />
                <Text style={styles.resultFooterHintText}>Scanner resets in 3s...</Text>
            </View>
        </View>
    </View>
  );
}
