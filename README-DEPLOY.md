# Guía de Despliegue en Render: Tienda Online WooCommerce

Este documento contiene todos los pasos detallados para poner tu tienda online en producción utilizando **Render**.

---

## 1. Preparativos Realizados en el Proyecto

1. **`tienda_online_wp.sql` generado**: Respaldo completo de tu base de datos local exportado (5.3 MB), listo para importar a tu MySQL en la nube. Está protegido en `.gitignore` para no filtrarse a GitHub.
2. **`Dockerfile` optimizado**:
   - Ajusta memoria y subida de archivos PHP a 64MB / 256MB (necesario para imágenes de catálogo en WooCommerce).
   - Incluye script `docker-entrypoint-render.sh` para otorgar automáticamente permisos `www-data` al disco montado.
3. **`render.yaml` corregido**:
   - Monta el disco persistente **exclusivamente en `/var/www/html/wp-content/uploads`**. Esto permite que cada `git push` actualice temas, plugins y código sin bloquearse.
4. **`wp-config.php` preparado**:
   - Lee dinámicamente credenciales de base de datos (`WORDPRESS_DB_*`), variables de dominio (`WP_HOME`, `WP_SITEURL`), SMTP y flag SSL para MySQL.
5. **`.htaccess` adaptado**:
   - Rutas reescritas desde la raíz `/` en lugar de subcarpeta local `/tienda-online/`.

---

## 2. Paso 1: Configurar la Base de Datos MySQL

Render **no cuenta con MySQL administrado con 1 clic**. Tienes 2 opciones recomendadas:

### Opción A (Recomendada y Gratuita): Base de Datos MySQL en Aiven
1. Regístrate en [Aiven.io](https://aiven.io/).
2. Crea un nuevo servicio seleccionando **MySQL**.
3. Elige el plan gratuito o básico en la región más cercana a ti.
4. Copia los datos de conexión que te entrega Aiven:
   - **Host** (ej: `mysql-xxxx.aivencloud.com:port`)
   - **User** (ej: `avnadmin`)
   - **Password**
   - **Database** (ej: `defaultdb` o crea `tienda_online_wp`)
5. Importa el archivo `tienda_online_wp.sql` a esta base de datos usando DBeaver, MySQL Workbench o la consola de Aiven.
6. En Render defines `WORDPRESS_DB_SSL = true` para que WordPress conecte con cifrado TLS.

### Opción B: MySQL en Render como Servicio Privado (Plan Pago)
1. En el Dashboard de Render, pulsa **New +** > **Private Service**.
2. Conecta la imagen Docker oficial `mysql:8.0`.
3. Asigna un Persistent Disk montado en `/var/lib/mysql` (10 GB).
4. Configura las variables:
   - `MYSQL_DATABASE`: `tienda_online_wp`
   - `MYSQL_USER`: `wp_user`
   - `MYSQL_PASSWORD`: (tu contraseña segura)
   - `MYSQL_ROOT_PASSWORD`: (tu contraseña root)
5. La dirección interna de conexión será `nombre-del-servicio:3306`.

---

## 3. Paso 2: Subir el Proyecto a GitHub

Desde la terminal de tu proyecto:

```bash
git init
git add .
git commit -m "feat: configuracion lista para despliegue en Render"
git branch -M main
```

Crea un repositorio **PRIVADO** en tu cuenta de GitHub (ejemplo: `tienda-online`) y enlaza el remoto:

```bash
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git push -u origin main
```

*(Nota: La carpeta `wp-content/uploads` y el archivo `tienda_online_wp.sql` están ignorados por `.gitignore`, por lo que tus fotos de clientes y la BD no se subirán públicamente).*

---

## 4. Paso 3: Desplegar en Render

1. Entra a [dashboard.render.com](https://dashboard.render.com).
2. Haz clic en **New +** > **Blueprint**.
3. Selecciona tu repositorio de GitHub `tienda-online`.
4. Render detectará automáticamente el archivo [render.yaml](file:///C:/laragon/www/tienda-online/render.yaml).
5. Render te pedirá rellenar las variables de entorno (`envVars`):
   - `WORDPRESS_DB_HOST`: Host y puerto de tu MySQL.
   - `WORDPRESS_DB_NAME`: Nombre de la base de datos.
   - `WORDPRESS_DB_USER`: Usuario de MySQL.
   - `WORDPRESS_DB_PASSWORD`: Contraseña de MySQL.
   - `WORDPRESS_DB_SSL`: `true` si usas Aiven / cloud SSL, o `false` si es MySQL privado en Render.
   - `WP_HOME`: URL asignada por Render (ej: `https://tienda-online-xxxx.onrender.com`).
   - `WP_SITEURL`: Misma URL que `WP_HOME`.
   - `SMTP_GMAIL_USER` / `SMTP_GMAIL_PASS`: (Opcional) Correo y contraseña de aplicación de Gmail para notificaciones WooCommerce.
6. Pulsa **Apply**. Render creará el Web Service Docker y el disco persistente de 10GB en `/var/www/html/wp-content/uploads`.

---

## 5. Paso 4: Subir las Imágenes de Productos (`uploads`)

Tienes 400 MB en `wp-content/uploads` que debes pasar al disco de Render:

### Método A (Vía Plugin - El más fácil y rápido):
1. Una vez desplegado WordPress, entra al panel de administración `/wp-admin`.
2. Instala el plugin gratuito **WPvivid Backup & Migration** o **All-in-One WP Migration**.
3. Haz un respaldo de solo archivos multimedia en local y restaúralo en la web de Render.

### Método B (Vía SSH en Render):
1. En el Dashboard de Render, abre la pestaña **Shell** de tu servicio `tienda-online`.
2. Puedes descargar tu archivo comprimido de uploads o copiar los archivos directamente a `/var/www/html/wp-content/uploads/`.
3. Asigna permisos:
   ```bash
   chown -R www-data:www-data /var/www/html/wp-content/uploads
   ```

---

## 6. Paso 5: Verificación Final

1. Accede a `https://tu-servicio.onrender.com`.
2. Inicia sesión en `/wp-login.php`.
3. Verifica que los productos y sus precios se muestren correctamente.
4. Realiza una prueba de compra con:
   - Pasarela PayPal.
   - Pasarela Mercado Pago (verifica que las credenciales API coincidan).
   - Pago Yape (con comprobante).
5. Cuando compres tu dominio propio (ej: `tiendademotos.com`), solo debes agregarlo en la sección **Custom Domains** de Render y actualizar las variables `WP_HOME` y `WP_SITEURL`.
