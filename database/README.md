# 📚 Instalación de Base de Datos - Portal de Trabajo UTP

## 🎯 Descripción
Este directorio contiene todos los scripts SQL necesarios para configurar la base de datos del Portal de Trabajo UTP.

---

## 📋 Requisitos Previos
- ✅ XAMPP instalado y funcionando
- ✅ MySQL corriendo (puerto 3306)
- ✅ phpMyAdmin accesible en http://localhost/phpmyadmin

---

## 🚀 Paso 1: Crear la Base de Datos

1. Abre phpMyAdmin: http://localhost/phpmyadmin
2. Haz clic en **"Nueva"** en el panel izquierdo
3. Nombre de la base de datos: `portal_trabajo_utp`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Clic en **"Crear"**

---

## 📝 Paso 2: Ejecutar Scripts SQL (EN ORDEN)

### **2.1. Schema (Crear Tablas)**
1. Selecciona la base de datos `portal_trabajo_utp` en el panel izquierdo
2. Haz clic en la pestaña **"SQL"**
3. Abre el archivo `schema.sql` con un editor de texto
4. Copia TODO el contenido
5. Pégalo en el área de texto de phpMyAdmin
6. Clic en **"Continuar"** (botón inferior derecho)

**✅ Verificación:** Deberías ver el mensaje "9 tablas creadas" y en el panel izquierdo verás:
- administradores
- bitacora_auditoria
- empresas
- estudiantes
- habilidades
- notificaciones
- ofertas
- ofertas_habilidades
- postulaciones

---

### **2.2. Procedimientos Almacenados**
1. Pestaña **"SQL"** (asegúrate de estar en la BD portal_trabajo_utp)
2. Abre el archivo `stored_procedures.sql`
3. Copia TODO el contenido
4. Pega y ejecuta
5. Clic en **"Continuar"**

**✅ Verificación:** Ve a la pestaña "Rutinas" y deberías ver:
- `sp_buscar_ofertas_por_rol`

---

### **2.3. Datos Iniciales (Seed Data)**
1. Pestaña **"SQL"**
2. Abre el archivo `seed_data.sql`
3. Copia TODO el contenido
4. Pega y ejecuta
5. Clic en **"Continuar"**

**✅ Verificación:** Deberías ver una tabla con:
- 3 administradores
- 10 empresas
- 30 habilidades
- 10 ofertas
- Múltiples relaciones ofertas-habilidades

---

## 🔑 Paso 3: Usuarios Administradores Creados

Puedes iniciar sesión con cualquiera de estos usuarios:

| Correo | Contraseña | Nombre |
|--------|-----------|--------|
| denis.cedeno@utp.ac.pa | Admin123! | Denis Cedeño |
| geralis.garrido@utp.ac.pa | Admin123! | Geralis Garrido |
| admin.ti@utp.ac.pa | Admin123! | Admin TI |

---

## 🔍 Paso 4: Verificar Instalación

Ejecuta esta consulta en phpMyAdmin para verificar que todo esté correcto:

```sql
-- Verificar conteos
SELECT 
    (SELECT COUNT(*) FROM administradores) AS admins,
    (SELECT COUNT(*) FROM empresas) AS empresas,
    (SELECT COUNT(*) FROM habilidades) AS habilidades,
    (SELECT COUNT(*) FROM ofertas) AS ofertas,
    (SELECT COUNT(*) FROM estudiantes) AS estudiantes,
    (SELECT COUNT(*) FROM postulaciones) AS postulaciones;
```

**Resultado esperado:**
- admins: 3
- empresas: 10
- habilidades: 30
- ofertas: 10
- estudiantes: 0 (se crearán cuando los estudiantes se registren)
- postulaciones: 0

---

## 🧪 Paso 5: Probar el Procedimiento Almacenado

Ejecuta este ejemplo para buscar ofertas por rol:

```sql
CALL sp_buscar_ofertas_por_rol('PHP');
```

Deberías ver ofertas relacionadas con PHP.

Otros ejemplos:
```sql
CALL sp_buscar_ofertas_por_rol('Desarrollador');
CALL sp_buscar_ofertas_por_rol('remoto');
CALL sp_buscar_ofertas_por_rol('DevOps');
```

---

## 📊 Estructura de Tablas Creadas

### Tablas Principales:
1. **estudiantes** - Información de estudiantes UTP
2. **administradores** - Usuarios administradores del sistema
3. **empresas** - Empresas que publican ofertas
4. **ofertas** - Ofertas de trabajo
5. **habilidades** - Catálogo de habilidades técnicas/blandas
6. **ofertas_habilidades** - Relación N:M ofertas-habilidades
7. **postulaciones** - Postulaciones de estudiantes a ofertas
8. **notificaciones** - Sistema de notificaciones internas
9. **bitacora_auditoria** - Registro de auditoría

---

## ⚠️ Solución de Problemas

### Error: "Base de datos ya existe"
**Solución:** Elimina la base de datos existente:
1. Selecciona `portal_trabajo_utp`
2. Clic en "Operaciones"
3. Scroll hasta "Eliminar base de datos"
4. Confirmar
5. Volver al Paso 1

### Error: "Tabla ya existe"
**Solución:** Ejecuta este comando antes del schema.sql:
```sql
DROP DATABASE IF EXISTS portal_trabajo_utp;
CREATE DATABASE portal_trabajo_utp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_trabajo_utp;
```

### Error en caracteres especiales (tildes)
**Solución:** Asegúrate que la base de datos use `utf8mb4_unicode_ci`

---

## 🔐 Regenerar Hash de Contraseñas (Opcional)

Si necesitas cambiar las contraseñas de los administradores:

1. Ve a la carpeta raíz del proyecto
2. Crea un archivo temporal `generar_hash.php`:

```php
<?php
$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash generado: " . $hash;
?>
```

3. Ejecuta: `php generar_hash.php`
4. Copia el hash generado
5. En phpMyAdmin, ejecuta:

```sql
UPDATE administradores 
SET password_hash = 'TU_NUEVO_HASH' 
WHERE correo_utp = 'denis.cedeno@utp.ac.pa';
```

---

## ✅ Checklist de Instalación

- [ ] Base de datos `portal_trabajo_utp` creada
- [ ] Archivo `schema.sql` ejecutado (9 tablas creadas)
- [ ] Archivo `stored_procedures.sql` ejecutado (1 procedimiento)
- [ ] Archivo `seed_data.sql` ejecutado (datos iniciales cargados)
- [ ] Verificación de conteos correcta
- [ ] Prueba de procedimiento almacenado exitosa

---

## 📞 Soporte

Si encuentras algún problema durante la instalación:
1. Verifica que MySQL esté corriendo en XAMPP
2. Revisa los logs de error de MySQL
3. Asegúrate de ejecutar los scripts en el orden correcto
4. Verifica que la codificación sea utf8mb4_unicode_ci

---

## 🎉 ¡Listo!

La base de datos está configurada correctamente. Puedes proceder con la instalación de los archivos PHP del proyecto.

**Siguiente paso:** Configurar los archivos de conexión en `/config/`