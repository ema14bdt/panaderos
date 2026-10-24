import 'package:flutter/material.dart';
import '../core/constants.dart';
import 'admin_screen.dart';
import 'beneficios_screen.dart';
import 'carnet_screen.dart';
import 'eventos_screen.dart';
import 'home_screen.dart';
import 'perfil_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({super.key, this.initialIndex = 0});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  late int _currentIndex;
  bool _isAdminView = false;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }

  @override
  Widget build(BuildContext context) {
    if (_isAdminView) {
      return AdminScreen(
        onSwitchToAfiliado: () {
          setState(() {
            _isAdminView = false;
          });
        },
      );
    }

    final screens = [
      const HomeScreen(),
      const CarnetScreen(),
      const BeneficiosScreen(),
      const EventosScreen(),
      PerfilScreen(
        onSwitchToAdmin: () {
          setState(() {
            _isAdminView = true;
          });
        },
      ),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        backgroundColor: Colors.white,
        elevation: 8,
        indicatorColor: AppColors.accent.withValues(alpha: 0.25),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home, color: AppColors.primary),
            label: 'Inicio',
          ),
          NavigationDestination(
            icon: Icon(Icons.badge_outlined),
            selectedIcon: Icon(Icons.badge, color: AppColors.primary),
            label: 'Mi Carnet',
          ),
          NavigationDestination(
            icon: Icon(Icons.card_giftcard_outlined),
            selectedIcon: Icon(Icons.card_giftcard, color: AppColors.primary),
            label: 'Beneficios',
          ),
          NavigationDestination(
            icon: Icon(Icons.event_available_outlined),
            selectedIcon: Icon(Icons.event_available, color: AppColors.primary),
            label: 'Eventos',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: AppColors.primary),
            label: 'Mi Perfil',
          ),
        ],
      ),
    );
  }
}
