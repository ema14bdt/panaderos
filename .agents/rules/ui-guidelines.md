# UI & UX Guidelines

1. **Design System & Visual Coherence**:
   - Reuse existing design tokens in `mobile/lib/core/constants.dart` (`AppColors.primary`, `AppColors.accent`, `AppColors.background`, `AppColors.border`).
   - Use the institutional font `Manrope` for all text hierarchies.
   - Maintain uniform component styling: card elevation, rounded corners (`12-16px`), padding, and form inputs.

2. **Navigation Hierarchy**:
   - Both affiliate and administrator modes must use the bottom `NavigationBar` with consistent icons, labels, and indicator styling.
   - Never place primary section navigation tabs in the top header (`AppBar`).
   - The top `PanaderosAppBar` is strictly reserved for the institutional brand:
     - `S.O.E.P.La`
     - `Sindicato de Obreros y Empleados Panaderos de Lanús`
     - Action icons (e.g. switch view button).
   - Never duplicate header actions at the bottom of screens.

3. **Date Formats**:
   - ALL dates displayed, entered, or edited in the UI must strictly follow the format: `dd/mm/aaaa` (`DD/MM/YYYY`).
   - Date inputs must accept/display `dd/mm/aaaa` and integrate calendar pickers where appropriate.
