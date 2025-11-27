<?php

/**
 * Helper para validación de estados de equipos
 * Aplicando principio KISS: funciones simples y claras
 */

if (!function_exists('validarTransicionEstado')) {
    /**
     * Valida si una transición de estado es permitida
     * Flujo: Planificación → Producción → Postproducción → Finalizado → Vencido (terminal)
     * 
     * @param string $estadoActual Estado actual del equipo
     * @param string $nuevoEstado Nuevo estado deseado
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    function validarTransicionEstado(string $estadoActual, string $nuevoEstado): array
    {
        // Normalizar estados
        $estadoActual = trim($estadoActual);
        $nuevoEstado = trim($nuevoEstado);

        // Si no hay cambio, es válido
        if ($estadoActual === $nuevoEstado) {
            return ['valido' => true, 'mensaje' => 'Sin cambios'];
        }

        // Regla 1: Finalizado y Vencido no pueden regresar a ningún estado
        if (in_array($estadoActual, ['Finalizado', 'Vencido'])) {
            return [
                'valido' => false,
                'mensaje' => 'Este servicio ya fue cerrado y no puede modificarse'
            ];
        }

        // Regla 2: No se puede saltar de Planificación directamente a Postproducción o Finalizado
        if ($estadoActual === 'Planificación' && in_array($nuevoEstado, ['Postproducción', 'Finalizado'])) {
            return [
                'valido' => false,
                'mensaje' => 'Debe avanzar primero a Producción'
            ];
        }

        // Regla 3: Producción no puede saltar directamente a Finalizado
        if ($estadoActual === 'Producción' && $nuevoEstado === 'Finalizado') {
            return [
                'valido' => false,
                'mensaje' => 'Debe pasar por Postproducción antes de finalizar'
            ];
        }

        // Regla 4: No se puede retroceder a estados anteriores en la cadena
        $retrocesos = [
            'Producción' => ['Planificación'],
            'Postproducción' => ['Planificación', 'Producción'],
        ];

        if (isset($retrocesos[$estadoActual]) && in_array($nuevoEstado, $retrocesos[$estadoActual])) {
            return [
                'valido' => false,
                'mensaje' => 'Este servicio no puede retroceder de fase'
            ];
        }

        // Regla 5: Solo Finalizado puede pasar a Vencido mediante lógica externa, no manual
        if ($nuevoEstado === 'Vencido' && $estadoActual !== 'Finalizado') {
            return [
                'valido' => false,
                'mensaje' => 'El estado Vencido solo puede ser asignado por el sistema'
            ];
        }

        return ['valido' => true, 'mensaje' => 'Transición válida'];
    }
}

if (!function_exists('getEstadosPermitidos')) {
    /**
     * Obtiene los estados permitidos desde un estado actual
     * 
     * @param string $estadoActual
     * @return array
     */
    function getEstadosPermitidos(string $estadoActual): array
    {
        $estados = ['Planificación', 'Producción', 'Postproducción', 'Finalizado'];
        $permitidos = [];

        foreach ($estados as $estado) {
            $validacion = validarTransicionEstado($estadoActual, $estado);
            if ($validacion['valido']) {
                $permitidos[] = $estado;
            }
        }

        return $permitidos;
    }
}

if (!function_exists('getColorEstado')) {
    /**
     * Obtiene el color CSS para un estado
     * 
     * @param string $estado
     * @return string
     */
    function getColorEstado(string $estado): string
    {
        return match ($estado) {
            'Planificación' => '#7c3aed',    // Morado
            'Producción' => '#3b82f6',      // Azul
            'Postproducción' => '#f59e0b',  // Naranja
            'Finalizado' => '#10b981',      // Verde
            'Vencido' => 'danger',           // Rojo - Vencido
            default => 'secondary'
        };
    }
}

if (!function_exists('getIconoEstado')) {
    /**
     * Obtiene el ícono FontAwesome para un estado
     * 
     * @param string $estado
     * @return string
     */
    function getIconoEstado(string $estado): string
    {
        return match ($estado) {
            'Planificación' => 'fas fa-calendar-alt',
            'Producción' => 'fas fa-video',
            'Postproducción' => 'fas fa-magic',
            'Finalizado' => 'fas fa-check-circle',
            'Vencido' => 'fas fa-exclamation-triangle',
            default => 'fas fa-question-circle'
        };
    }
}

if (!function_exists('getSweetAlertIcon')) {
    /**
     * Obtiene el ícono compatible con SweetAlert2 para un estado
     * 
     * @param string $estado
     * @return string
     */
    function getSweetAlertIcon(string $estado): string
    {
        return match ($estado) {
            'Planificación' => 'info',
            'Producción' => 'info',
            'Postproducción' => 'warning',
            'Finalizado' => 'success',
            'Vencido' => 'error',
            default => 'question'
        };
    }
}
