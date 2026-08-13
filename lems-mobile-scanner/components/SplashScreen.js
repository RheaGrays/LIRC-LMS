import React from 'react';
import { View, Text, Image, Animated, ImageBackground } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import styles from '../styles';

export default function SplashScreen({ visible, fadeAnim, progress, status }) {
  if (!visible) return null;

  return (
    <Animated.View style={[styles.splashContainer, { opacity: fadeAnim }]}>
      <ImageBackground source={require('../assets/bg.jpg')} style={styles.splashBg} imageStyle={{ opacity: 0.08 }}>
          
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
                  <Image source={require('../assets/CorJesu_Logo.png')} style={{width: 20, height: 20}} />
              </View>
          </View>

          {/* Center Content */}
          <View style={styles.splashCenterContent}>
              {/* Emblem */}
              <View style={styles.emblemContainer}>
                  <View style={styles.emblemGlow} />
                  <View style={styles.emblemOuterRing}>
                      <View style={styles.emblemInnerRing}>
                          <Image source={require('../assets/CorJesu_Logo.png')} style={{width: 80, height: 80, resizeMode: 'contain'}} />
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
                      <View style={[styles.loadingBarFill, { width: `${progress}%` }]} />
                  </View>
                  <Text style={styles.loadingBarPercent}>{progress}%</Text>
              </View>
              <Text style={styles.loadingStatusText}>{status}</Text>
          </View>

      </ImageBackground>
    </Animated.View>
  );
}
