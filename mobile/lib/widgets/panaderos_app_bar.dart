import 'package:flutter/material.dart';
import '../core/constants.dart';

class PanaderosAppBar extends StatelessWidget implements PreferredSizeWidget {
  final List<Widget>? actions;
  final bool showBackButton;
  final PreferredSizeWidget? bottom;

  const PanaderosAppBar({
    super.key,
    this.actions,
    this.showBackButton = false,
    this.bottom,
    String? subtitle, // Parámetro mantenido por compatibilidad pero no mostrado en el brand
  });

  @override
  Size get preferredSize => Size.fromHeight(66 + (bottom?.preferredSize.height ?? 0));

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: AppColors.primary,
      elevation: 2,
      automaticallyImplyLeading: showBackButton,
      iconTheme: const IconThemeData(color: Colors.white),
      titleSpacing: showBackButton ? 0 : 16,
      bottom: bottom,
      title: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Logo oficial sin fondo blanco (transparente idéntico a la web)
          Image.asset(
            'assets/images/logo.png',
            height: 44,
            width: 44,
            fit: BoxFit.contain,
            errorBuilder: (_, __, ___) => const Icon(
              Icons.bakery_dining,
              color: AppColors.accent,
              size: 32,
            ),
          ),
          const SizedBox(width: 12),
          // Marca institucional con texto oficial idéntico al navbar web
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'S.O.E.P.La',
                style: TextStyle(
                  fontFamily: 'Manrope',
                  fontWeight: FontWeight.w900,
                  fontSize: 16,
                  color: Colors.white,
                  letterSpacing: 0.6,
                  height: 1.1,
                ),
              ),
              SizedBox(height: 2),
              Text(
                'Sindicato de Obreros y Empleados\nPanaderos de Lanús',
                style: TextStyle(
                  fontFamily: 'Manrope',
                  fontSize: 9.5,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFFE2E8F0),
                  height: 1.15,
                  letterSpacing: 0.1,
                ),
              ),
            ],
          ),
        ],
      ),
      actions: actions,
    );
  }
}
