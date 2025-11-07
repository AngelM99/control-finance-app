-- Script para limpiar TODOS los datos del usuario: usuario@controlfinance.com
-- ADVERTENCIA: Este script eliminará PERMANENTEMENTE todos los datos del usuario
-- Ejecuta este script en tu base de datos

-- Paso 1: Obtener el ID del usuario (reemplaza con el ID real si lo conoces)
-- SELECT id FROM users WHERE email = 'usuario@controlfinance.com';

-- Paso 2: Reemplaza USER_ID_AQUI con el ID del usuario obtenido en el paso 1
-- Por ejemplo, si el ID es 3, reemplaza todas las ocurrencias de USER_ID_AQUI por 3

SET @user_id = (SELECT id FROM users WHERE email = 'usuario@controlfinance.com' LIMIT 1);

-- Verificar el ID del usuario
SELECT CONCAT('Usuario ID: ', @user_id, ' - Nombre: ', name) as info FROM users WHERE id = @user_id;

-- Mostrar conteo de registros ANTES de eliminar
SELECT
    (SELECT COUNT(*) FROM transactions WHERE user_id = @user_id) as transacciones,
    (SELECT COUNT(*) FROM installments WHERE user_id = @user_id) as planes_cuotas,
    (SELECT COUNT(*) FROM financial_products WHERE user_id = @user_id) as productos_financieros,
    (SELECT COUNT(*) FROM lenders WHERE user_id = @user_id) as prestamistas;

-- DESCOMENTAR LAS SIGUIENTES LÍNEAS PARA EJECUTAR LA LIMPIEZA
-- ⚠️ ADVERTENCIA: Esto eliminará PERMANENTEMENTE los datos

-- DELETE FROM transactions WHERE user_id = @user_id;
-- DELETE FROM installments WHERE user_id = @user_id;
-- DELETE FROM financial_products WHERE user_id = @user_id;
-- DELETE FROM lenders WHERE user_id = @user_id;

-- Verificar que todo se eliminó correctamente
-- SELECT
--     (SELECT COUNT(*) FROM transactions WHERE user_id = @user_id) as transacciones_restantes,
--     (SELECT COUNT(*) FROM installments WHERE user_id = @user_id) as cuotas_restantes,
--     (SELECT COUNT(*) FROM financial_products WHERE user_id = @user_id) as productos_restantes,
--     (SELECT COUNT(*) FROM lenders WHERE user_id = @user_id) as prestamistas_restantes;

-- ✅ Si todos los contadores muestran 0, la limpieza fue exitosa
