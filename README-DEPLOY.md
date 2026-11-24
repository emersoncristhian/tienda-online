# Despliegue Todo-en-Uno en Render: Tienda Online WooCommerce

Este proyecto está configurado para desplegarse de forma 100% automatizada en **Render** mediante su sistema de Blueprint ([render.yaml](file:///C:/laragon/www/tienda-online/render.yaml)).

---

## ¿Qué hace esta configuración automáticamente?

1. **Crea la Base de Datos MySQL 8**:
   - Servicio privado (`tienda-mysql`) con disco persistente SSD de 10 GB.
   - Genera contraseñas aleatorias seguras automáticamente.
   - **Auto-importación**: Al arrancar por primera vez, ejecuta automáticamente `init-db/init.sql` con todas las tablas y productos de tu tienda.
2. **Crea el Servicio Web WordPress**:
   - Conecta WordPress a la base de datos automáticamente (sin copiar contraseñas a mano).
   - Monta un disco persistente en `/var/www/html/wp-content/uploads` (10 GB) para resguardar fotos y comprobantes.
   - Configura límites de PHP a 64MB y memoria a 256MB.
   - Habilita HTTPS automático con terminación SSL.

---

## Pasos para el Despliegue

### 1. Entrar a Render
1. Inicia sesión en tu cuenta de [dashboard.render.com](https://dashboard.render.com).
2. Si es tu primera vez usando servicios pagos con disco en Render, asegúrate de tener registrado tu método de pago en **Account Settings > Billing**.

### 2. Conectar el Blueprint
1. En el Dashboard de Render, haz clic en **New +** y selecciona **Blueprint**.
2. Conecta tu cuenta de GitHub (si aún no lo has hecho) y selecciona el repositorio:
   `emersoncristhian/tienda-online`
3. Render detectará automáticamente el archivo `render.yaml` y te mostrará el plan con los dos servicios:
   - `tienda-mysql` (Base de datos MySQL privada)
   - `tienda-online` (Sitio web WordPress WooCommerce)
4. En las variables solicitadas, solo debes confirmar:
   - `WP_HOME`: La URL que Render le asignará a tu sitio (ejemplo: `https://tienda-online-xxxx.onrender.com`).
   - `WP_SITEURL`: La misma URL anterior.
5. Haz clic en el botón **Apply**.

---

## 3. Subir las imágenes de productos (`uploads`)

Tus imágenes locales (~400 MB en `wp-content/uploads`) no se suben al código de GitHub por seguridad y tamaño. Una vez que tu sitio esté activo en Render:

1. Entra a tu panel `/wp-admin` en Render.
2. Ve a **Plugins > Añadir nuevo** e instala **WPvivid Backup & Migration** o **All-in-One WP Migration**.
3. Haz un respaldo de solo archivos multimedia en tu Laragon local y restáuralo en la web de Render con 1 clic.
