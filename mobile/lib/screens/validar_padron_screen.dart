import 'package:flutter/material.dart';
import '../core/constants.dart';
import '../services/api_service.dart';
import 'register_screen.dart';

class ValidarPadronScreen extends StatefulWidget {
  const ValidarPadronScreen({super.key});

  @override
  State<ValidarPadronScreen> createState() => _ValidarPadronScreenState();
}

class _ValidarPadronScreenState extends State<ValidarPadronScreen> {
  final _formKey = GlobalKey<FormState>();
  final _dniController = TextEditingController();
  final _numeroAfiliadoController = TextEditingController();
  final _apiService = ApiService();

  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _verificarPadron() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final datosPadron = await _apiService.validarPadron(
        _dniController.text.trim(),
        _numeroAfiliadoController.text.trim(),
      );

      if (!mounted) return;

      // Avanzar al Paso 2 con los datos ya verificados del padrón
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (ctx) => RegisterScreen(datosPadron: datosPadron),
        ),
      );
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _dniController.dispose();
    _numeroAfiliadoController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Registro de Afiliado'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Indicador de pasos
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Text(
                      'PASO 1 DE 2',
                      style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'Verificación de Padrón',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              const Text(
                'Identificate como Afiliado',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Ingresá tu DNI y número de afiliado tal como figuran en el padrón del sindicato para activar tu carnet digital.',
                style: TextStyle(color: AppColors.textMuted, fontSize: 14, height: 1.4),
              ),
              const SizedBox(height: 28),

              if (_errorMessage != null)
                Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.error.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: AppColors.error, size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(color: AppColors.error, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),

              // Campo DNI
              TextFormField(
                controller: _dniController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'DNI (sin puntos)',
                  hintText: 'Ej: 28456123',
                  prefixIcon: Icon(Icons.badge_outlined, color: AppColors.primary),
                ),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) return 'Ingresá tu DNI';
                  if (val.trim().length < 7) return 'El DNI debe tener al menos 7 dígitos';
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Campo Número de Afiliado
              TextFormField(
                controller: _numeroAfiliadoController,
                keyboardType: TextInputType.text,
                decoration: const InputDecoration(
                  labelText: 'Número de Afiliado',
                  hintText: 'Ej: 10452',
                  prefixIcon: Icon(Icons.confirmation_number_outlined, color: AppColors.primary),
                ),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) return 'Ingresá tu número de afiliado';
                  return null;
                },
              ),
              const SizedBox(height: 32),

              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _verificarPadron,
                  child: _isLoading
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                        )
                      : const Text('Verificar en Padrón'),
                ),
              ),

              const SizedBox(height: 24),
              Center(
                child: TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('¿Ya tenés cuenta? Iniciar Sesión'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
